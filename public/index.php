<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Flash\Messages;
use DI\Container;
use App\SiteDAO;
use App\Site;
use App\CheckDAO;
use App\Check;
use App\DbConnection;
use App\DbMigrator;
use GuzzleHttp\Exception\ConnectException;
use Dotenv\Dotenv;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

$app = AppFactory::createFromContainer($container);

//Errors handlers

$httpNotFoundExceptionHandler = function (
    ServerRequestInterface $request,
    HttpNotFoundException $exception,
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $this->get('renderer')->render($response, 'pageNotFound.phtml');
    return $response->withStatus(404);
};


$defaultExceptionHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $this->get('renderer')->render($response, 'serverError.phtml');
    return $response->withStatus(500);
};

//Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, $httpNotFoundExceptionHandler);
$errorMiddleware->setDefaultErrorHandler($defaultExceptionHandler);

session_start();
$dotenv = Dotenv::createImmutable((__DIR__ . '/..'));
$dotenv->safeLoad();

$dbUrl = (string)$_ENV['DATABASE_URL'];
$conn = DbConnection::fromDbUrl($dbUrl);
DbMigrator::migrate($conn);
$container->set(PDO::class, fn() => $conn);

$container->set(CheckDAO::class, fn(PDO $conn) => new CheckDAO($conn));

$container->set('renderer', fn() => new PhpRenderer(__DIR__ . '/../templates'));

$container->set('flash', fn() => new Messages());



$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response, $args) {
    $params = ['isInputValid' => true];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('index');

$app->get('/urls', function ($request, $response, $args) {
    $messages = $this->get('flash')->getMessages();
    $siteDAO = $this->get(SiteDAO::class);
    $sites = $siteDAO->getAll();
    $params = ['sites' => $sites, 'flashMessages' => $messages];
    return $this->get('renderer')->render($response, 'urls.phtml', $params);
})->setName('urls');

$app->get('/urls/{id:[0-9]{1,20}}', function ($request, $response, $args) {
    $id = $args['id'];
    $siteDAO = $this->get(SiteDAO::class);
    $site = $siteDAO->findById($id);
    if (!isset($site)) {
        throw (new HttpNotFoundException($request));
    }
    $checkDAO = $this->get(CheckDAO::class);
    $checks = $checkDAO->findChecksBySiteId($id);
    $messages = $this->get('flash')->getMessages();
    $params = ['site' => $site, 'checks' => $checks, 'flashMessages' => $messages];
    return $this->get('renderer')->render($response, 'url.phtml', $params);
})->setName('url');

$app->post('/urls', function ($request, $response) use ($router) {
    $aUrl = $request->getParsedBody()['url'];
    $urlRaw = $aUrl['name'];
    if (!Site::isUrlValid($urlRaw)) {
        $params = ['isInputValid' => false, 'url' => $urlRaw];
        $newResponse = $response->withStatus(422);
        return $this->get('renderer')->render($newResponse, 'index.phtml', $params);
    }
    $site = new Site($urlRaw);
    $siteDAO = $this->get(SiteDAO::class);
    $siteFromDB = $siteDAO->findByName($site->getUrl());
    if (is_null($siteFromDB)) {
        $siteDAO->save($site);
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        $id = $site->getId();
    } else {
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        $id = $siteFromDB->getId();
    }
    $url = $router->urlFor('url', ['id' => $id]);
    $newResponse = $response->withRedirect($url);
    return $newResponse;
});

$app->post('/urls/{url_id:[0-9]{1,20}}/checks', function ($request, $response, $args) use ($router) {
    $id = $args['url_id'];
    $check =  new Check($id);
    $siteDAO = $this->get(SiteDAO::class);
    $site = $siteDAO->findById((string)$id);
    try {
        $check->check($site->getUrl());
        $checkDAO = $this->get(CheckDAO::class);
        $checkDAO->save($check);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ConnectException $e) {
        $this->get('flash')->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
    }
    $url = $router->urlFor('url', ['id' => $id]);
    $newResponse = $response->withRedirect($url);
    return $newResponse;
});

$app->run();
