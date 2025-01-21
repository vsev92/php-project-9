<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\AnalyzerDAO;
use App\Site;
use App\CheckDAO;
use App\Check;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;

require __DIR__ . '/../vendor/autoload.php';






$container = new Container();

session_start();



$container->set('getSiteDAO', function ($aDatabaseUrl) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $aDatabaseUrl = (string)$_ENV['DATABASE_URL'];
    $aDatabaseUrl = (string)$_ENV['DATABASE_URL'];
   
    return new AnalyzerDAO($aDatabaseUrl);
});

$container->set('getCheckDAO', function ($aDatabaseUrl) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $aDatabaseUrl = (string)$_ENV['DATABASE_URL'];
    $aDatabaseUrl = (string)$_ENV['DATABASE_URL'];
   
    return new CheckDAO($aDatabaseUrl);
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
	
	$params = ['inputValid' => true];
    return $this->get('renderer')->render($response, '/../templates/index.phtml', $params);
})->setName('home');

$app->get('/urls', function ($request, $response, $args) {

    $messages = $this->get('flash')->getMessages();
    $messageType = array_keys($messages)[0];
    $message = $messages[$messageType][0];

    $siteDAO = $this->get('getSiteDAO');
    $sites = $siteDAO->getAll();


    $params = ['sites' => $sites, 'flash' => $message, 'flashType' => $messageType];

    return $this->get('renderer')->render($response, '/../templates/urls.phtml', $params);
})->setName('urls');


$app->get('/urls/{id}', function ($request, $response, $args) {
  
    $messages = $this->get('flash')->getMessages();
    $messageType = array_keys($messages)[0];
    $message = $messages[$messageType][0];

    $siteDAO = $this->get('getSiteDAO');
    $id = $args['id'];
    $site = $siteDAO->findById($id);

    $checkDAO = $this->get('getCheckDAO');
    
    $checks = $checkDAO->findChecksBySiteId($id);
    $params = ['site' => $site, 'checks'=> $checks, 'flash' => $message, 'flashType' => $messageType];

    

    return $this->get('renderer')->render($response, '/../templates/url.phtml', $params);
})->setName('url');

$app->post('/urls', function ($request, $response) use ($router) {


   

	$aUrl= $request->getParsedBody()['url'];
    $urlRaw = $aUrl['name'];
    $id = 0;

   
    if(Site::isUrlValid($urlRaw)) {
        $site = new Site($urlRaw);
        $siteDAO = $this->get('getSiteDAO');
        $siteFromDB = $siteDAO->findByName($site->getUrl());

        if(is_null($siteFromDB)){
            if($siteDAO->save($site)) {
                $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
                $id = $site->getId();
            } else {
                $this->get('flash')->addMessage('danger', 'Ошибка добавления страницы');
            }
        } else {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
            $id = $siteFromDB->getId();
        }

   
        $url = $router->urlFor('url', ['id' => $id]);
        $newResponce = $response->withRedirect($url);
        return $newResponce;

    } else {

        $params = ['inputValidation' => false, 'url' => $url];
        return $this->get('renderer')->render($response, '/../templates/index.phtml', $params);

    }
   
    
});

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router) {
    $id = $args['url_id'];
    $check =  new Check($id);
    $siteDAO = $this->get('getSiteDAO');
    $site = $siteDAO->findById((string)$id);
    try{
        $check->check($site->getUrl());
        $checkDAO = $this->get('getCheckDAO');
        $checkDAO->save($check);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ConnectException $e) {
        $this->get('flash')->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
    } catch (ClientException $e) {
        return $this->get('renderer')->render($response, '/../templates/ServerError.phtml');
    } 
    $url = $router->urlFor('url', ['id' => $id]);
    $newResponce = $response->withRedirect($url);
    return $newResponce;

});


$app->run();



