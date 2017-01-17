<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

require '../vendor/autoload.php';

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'dhcp',
            'username' => 'dhcp',
            'password' => 'dhcp',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ],
        'logger' => [
            'name' => 'dhcp-api',
            'level' => Monolog\Logger::DEBUG,
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];

// instantiate the App object
$app = new \Slim\App($config);

$container = $app->getContainer();

// Setup logging
$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger($c['settings']['logger']['name']);
    $file_handler = new \Monolog\Handler\StreamHandler($c['settings']['logger']['path']);
    $logger->pushHandler($file_handler);
    return $logger;
};

// Setup MySQL
$container['db'] = function ($c) {
    $dbs = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $dbs['host'] . ";dbname=" . $dbs['database'],
                   $dbs['username'], $dbs['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
};

$container['capsule'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

$container['SubnetController'] = function ($c) {
    return new \Dhcp\Subnet\SubnetController($c);
};

$container['GroupController'] = function ($c) {
    return new \Dhcp\Controller\GroupController($c);
};

$container['EndHostController'] = function ($c) {
    return new \Dhcp\Controller\EndHostController($c);
};

$container['EndHostTypeController'] = function ($c) {
    return new \Dhcp\Controller\EndHostTypeController($c);
};

$container['ReservationController'] = function ($c) {
    return new \Dhcp\Reservation\ReservationController($c);
};


$app->add(new \Dhcp\Middleware\LogMiddleware($container));
// Add IP middleware last. Execution goes backwards
$app->add(new RKA\Middleware\IpAddress());


require __DIR__ . '/../routes.php';

// Run application
//$container->capsule;
$app->run();
