<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\Container;
use App\SiteDAO;
use App\Site;
use App\CheckDAO;
use App\Check;
use App\DbProvider;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

session_start();

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$container->set(\PDO::class, function () {
    $dbUrl = (string)$_ENV['DATABASE_URL'];
    $conn =  DbProvider::fromDbUrl($dbUrl);
    DbProvider::migrate($conn);
    return $conn;
});

$container->set(SiteDAO::class, fn(\PDO $conn) => new SiteDAO($conn));

$container->set(CheckDAO::class, fn(\PDO $conn) => new CheckDAO($conn));

$container->set('renderer', fn() => new \Slim\Views\PhpRenderer(__DIR__ . '/../templates'));

$container->set('flash', fn() => new \Slim\Flash\Messages());

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response, $args) {
    $params = ['inputValid' => true];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('home');

$app->get('/urls', function ($request, $response, $args) {
    $messages = $this->get('flash')->getMessages();
    $message = '';
    $messageType = '';
    if (count($messages) > 0) {
        $messageType = array_keys($messages)[0];
        $message = $messages[$messageType][0];
    }
    $siteDAO = $this->get(SiteDAO::class);
    $sites = $siteDAO->getAll();
    $params = ['sites' => $sites, 'flash' => $message, 'flashType' => $messageType];
    return $this->get('renderer')->render($response, 'urls.phtml', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $siteDAO = $this->get(SiteDAO::class);
    $site = $siteDAO->findById($id);
    if (!isset($site)) {
        $newResponce = $response->withStatus(404);
        return $this->get('renderer')->render($newResponce, 'pageNotFound.phtml');
    }
    $checkDAO = $this->get(CheckDAO::class);
    $checks = $checkDAO->findChecksBySiteId($id);
    $messages = $this->get('flash')->getMessages();
    $message = '';
    $messageType = '';
    if (count($messages) > 0) {
        $messageType = array_keys($messages)[0];
        $message = $messages[$messageType][0];
    }
    $params = ['site' => $site, 'checks' => $checks, 'flash' => $message, 'flashType' => $messageType];
    return $this->get('renderer')->render($response, 'url.phtml', $params);
})->setName('url');

$app->post('/urls', function ($request, $response) use ($router) {
    $aUrl = $request->getParsedBody()['url'];
    $urlRaw = $aUrl['name'];
    if (Site::isUrlValid($urlRaw)) {
        $site = new Site($urlRaw);
        $siteDAO = $this->get(SiteDAO::class);
        $siteFromDB = $siteDAO->findByName($site->getUrl());
        if (is_null($siteFromDB)) {
            if ($siteDAO->save($site)) {
                $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
                $id = $site->getId();
            } else {
                return $this->get('renderer')->render($response, 'serverError.phtml');
            }
        } else {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
            $id = $siteFromDB->getId();
        }
        $url = $router->urlFor('url', ['id' => $id]);
        $newResponce = $response->withRedirect($url);
        return $newResponce;
    } else {
        $params = ['inputValid' => false, 'url' => $urlRaw];
        $newResponce = $response->withStatus(422);
        return $this->get('renderer')->render($newResponce, 'index.phtml', $params);
    }
});

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router) {
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
    } catch (ClientException $e) {
        return $this->get('renderer')->render($response, 'serverError.phtml');
    }
    $url = $router->urlFor('url', ['id' => $id]);
    $newResponce = $response->withRedirect($url);
    return $newResponce;
});

$app->run();
