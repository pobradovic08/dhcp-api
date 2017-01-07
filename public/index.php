<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

require __DIR__ . '/../vendor/autoload.php';

//spl_autoload_register(function ($classname) {
//    require("../classes/" . $classname . ".php");
//});

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'host' => 'localhost',
            'user' => 'dhcp',
            'password' => 'dhcp',
            'dbname' => 'dhcp',
        ],
        'logger' => [
            'name' => 'slim-app',
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
    $pdo = new PDO("mysql:host=" . $dbs['host'] . ";dbname=" . $dbs['dbname'],
                   $dbs['user'], $dbs['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
};

$container['SubnetController'] = function ($c) {
    return new \Dhcp\Subnet\SubnetController($c);
};

$container['GroupController'] = function ($c) {
    return new \Dhcp\Group\GroupController($c);
};

$container['EndHostController'] = function ($c) {
    return new \Dhcp\EndHost\EndHostController($c);
};

$container['EndHostTypeController'] = function ($c) {
    return new \Dhcp\EndHostType\EndHostTypeController($c);
};

$container['ReservationController'] = function ($c) {
    return new \Dhcp\Reservation\ReservationController($c);
};


require __DIR__ . '/../routes.php';

// Run application
$app->run();
