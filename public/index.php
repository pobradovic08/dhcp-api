<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

require '../vendor/autoload.php';

spl_autoload_register (function ($classname) {
    require ("../classes/" . $classname . ".php");
});

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

$container = $app->getContainer ();

// Setup logging
$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger($c['settings']['logger']['name']);
    $file_handler = new \Monolog\Handler\StreamHandler($c['settings']['logger']['path']);
    $logger->pushHandler ($file_handler);
    return $logger;
};

// Setup MySQL
$container['db'] = function ($c) {
    $dbs = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $dbs['host'] . ";dbname=" . $dbs['dbname'],
                   $dbs['user'], $dbs['password']);
    $pdo->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute (PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
};


/*
 * End Hosts
 */

$app->group ('/endhosts', function () use ($app) {
    /* Get all End Hosts */
    $app->get ('/[all]', '\EndHostController:get_host');
    /* Get end host by ID */
    $app->get ('/id/{end_host_id:[0-9]+}[/]', '\EndHostController:get_host_by_id');
    /* Get end host by MAC address */
    $app->get ('/mac/{mac:(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})}[/]', '\EndHostController:get_host_by_mac');
    /* Search host */
    $app->get ('/search/{pattern}[/]', '\EndHostController:get_search_host');
    /* Create or update new end host */
    $app->post ('[/add]', '\EndHostController:post_host');
    /* TODO: Updates host with specific ID */
    $app->put ('/id/{end_host_id:[0-9]+}[/]', '\EndHostController:put_host_by_id');

    /*
     * End Host Types
     */

    /* Delete end host type by ID */
    $app->delete ('/type/id/{end_host_type_id:[0-9]+}[/]', '\EndHostController:delete_host');
    /* Get all types */
    $app->get ('/type/[all]', '\EndHostTypeController:get_type');
    /* Create new end host type */
    $app->post ('/type[/add]', '\EndHostTypeController:post_type');
    /* Get type by ID */
    $app->get ('/type/id/{end_host_type_id:[0-9]+}[/]', '\EndHostTypeController:get_type_by_id');
    /* Update host type with specific ID */
    $app->put ('/type/id/{end_host_type_id:[0-9]+}[/]', '\EndHostTypeController:put_type_by_id');

});

/*
 * Reservations
 */
$app->group ('/reservations', function () use ($app) {
    /* Get all reservations */
    $app->get ('/[all]', '\ReservationController:get_reservations');
    /* Get all reservations from specific subnet */
    $app->get ('/subnet/{subnet_id:[0-9]+}[/{group_id:[0-9]+}]', '\ReservationController:get_reservations_for_subnet');
    /* Get all reservations from specific group */
    $app->get ('/group/{group_id:[0-9]+}', '\ReservationController:get_reservations_for_group');
    /* Get specific reservation by ID */
    $app->get ('/id/{id:[0-9]+}', '\ReservationController:get_reservation_by_id');
    /* Get specific reservation by IP address */
    $app->get ('/ip/{ip:[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}}', '\ReservationController:get_reservation_by_ip');
    /* Get all reservations for a MAC address */
    $app->get ('/mac/{mac:(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})}', '\ReservationController:get_reservation_by_mac');

});


// Run application
$app->run ();
