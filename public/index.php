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

///database connection
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$databaseUrl = parse_url($_ENV['DATABASE_URL']);

//var_dump($databaseUrl);
$username = $databaseUrl['user']; // janedoe
$password = $databaseUrl['pass']; // mypassword
$host = $databaseUrl['host']; // localhost
$port = (string)$databaseUrl['port'] ?? '5432'; // 5432
$dbName = ltrim($databaseUrl['path'], '/');
$dsn = "pgsql:host={$host};port={$port};dbname={$dbName};";
   // var_dump($dsn);
    // make a database connection
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    if (!$pdo) {
        throw new Exception('Failed to connect to database');
    }
    
    $dbo = new AnalyzerDAO($pdo);



////




$container = new Container();

session_start();
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

    return $this->get('renderer')->render($response, '/../templates/urls.phtml');
})->setName('urls');


$app->post('/urls', function ($request, $response) use ($dbo, $router) {


   

	$aUrl= $request->getParsedBody()['url'];
    $url = $aUrl['name'];

   
    if(Site::isUrlValid($url)) {
        var_dump($url);
        $site = new Site($url);
        $dbo->save($site);
        $this->get('flash')->addMessage('success', 'Сайт добавлен');

        $url = $router->urlFor('home');
        var_dump($url);
        $newResponce = $response->withRedirect($url);
        return $newResponce;

    } else {
        var_dump(3);
        return $this->get('renderer')->render($response, '/../templates/index.phtml', $params);

    }
   
    
});



$app->run();



