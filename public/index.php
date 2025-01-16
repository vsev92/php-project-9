<?php

declare(strict_types=1);

//use App\Application\Handlers\HttpErrorHandler;
//use App\Application\Handlers\ShutdownHandler;
//use App\Application\ResponseEmitter\ResponseEmitter;
//use App\Application\Settings\SettingsInterface;
//use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use DI\Container;
///use Slim\Factory\ServerRequestCreatorFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
//use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\AnalyzerDAO;
use App\Site;


require __DIR__ . '/../vendor/autoload.php';






$container = new Container();

session_start();

$container->set('getSiteDAO', function () {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $aDatabaseUrl = (string)$_ENV['DATABASE_URL'];
   
    return new AnalyzerDAO($aDatabaseUrl);
});


$container->set('renderer', function () {
   
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);



//////////
$router = $app->getRouteCollector()->getRouteParser();



$app->get('/', function ($request, $response, $args) {
	
	//$databaseUrl = parse_url($_ENV['DATABASE_URL']);
	$params = ['db' => $databaseUrl];
    return $this->get('renderer')->render($response, '/../templates/index.phtml', $params);
})->setName('home');;

$app->get('/urls', function ($request, $response, $args) {

    $messages = $this->get('flash')->getMessages();
    $siteDAO = $this->get('getSiteDAO');
    $sites = $siteDAO->getAll();
    $params = ['sites' => $sites, 'flash' => $messages['success'][0]];

    return $this->get('renderer')->render($response, '/../templates/urls.phtml', $params);
})->setName('urls');


$app->post('/urls', function ($request, $response) use ($dbo, $router) {


   

	$aUrl= $request->getParsedBody()['url'];
    $url = $aUrl['name'];

   
    if(Site::isUrlValid($url)) {
        var_dump($url);
        $site = new Site($url);
        $siteDAO = $this->get('getSiteDAO');
        $siteDAO->save($site);
        $this->get('flash')->addMessage('success', 'Сайт добавлен');

        $url = $router->urlFor('urls');
        $newResponce = $response->withRedirect($url);
        return $newResponce;

    } else {

        return $this->get('renderer')->render($response, '/../templates/index.phtml', $params);

    }
   
    
});



$app->run();



