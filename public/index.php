<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

spl_autoload_register(function ($classname) {
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

$container = $app->getContainer();

// Setup logging
$container['logger'] = function($c){
  $logger = new \Monolog\Logger($c['settings']['logger']['name']);
  $file_handler = new \Monolog\Handler\StreamHandler($c['settings']['logger']['path']);
  $logger->pushHandler($file_handler);
  return $logger;
};

// Setup MySQL
$container['db'] = function($c){
  $dbs = $c['settings']['db'];
  $pdo = new PDO("mysql:host=" . $dbs['host'] . ";dbname=" . $dbs['dbname'],
        $dbs['user'], $dbs['password']);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  return $pdo;
};


/*
 * End Hosts
 */

$app->group('/endhosts', function () use ($app) {
  /*
   *  Get all types
   */
   $app->get('/type/[all]', function (Request $request, Response $response){
     $this->logger->addInfo("End host type list");
     $mapper = new EndHostTypeMapper($this->db);
     $endhost_types = $mapper->getTypes(array());
     $array = [];
     foreach ($endhost_types as $type) {
       $array[] = $type->serialize();
     }
     return $response->withStatus(200)->withJson($array);
   });

   /*
    * Get type by ID
    */
   $app->get('/type/id/{end_host_type_id:[0-9]+}[/]', function (Request $request, Response $response, $args){
     $this->logger->addInfo("End host type #" . $args['end_host_type_id']);
     $mapper = new EndHostTypeMapper($this->db);
     $filter = array('end_host_type_id' => $args['end_host_type_id']);
     $types = $mapper->getTypes($filter);
     if(sizeof($types) == 1){
       return $response->withStatus(200)->withJson($types[0]->serialize());
     }else{
       return $response->withStatus(404)->withJson([]);
     }
   });
});

/*
 * Reservations
 */
$app->group('/reservations', function () use ($app) {
  /*
   * Get all reservations
   */
  $app->get('/[all]', function(Request $request, Response $response){
    $this->logger->addInfo("Reservation list");
    $mapper = new ReservationMapper($this->db);
    $reservations = $mapper->getReservations(array());
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);
  });

  /*
   * Get all reservations from specific subnet
   */

  $app->get('/subnet/{subnet_id:[0-9]+}[/{group_id:[0-9]+}]', function (Request $request, Response $response, $args){
    $this->logger->addInfo("Reservation list for subnet #" . $args['subnet_id']);
    $mapper = new ReservationMapper($this->db);
    // Filter data
    $filter = array('subnet_id' => $args['subnet_id']);
    // Optional group_id argument
    if(array_key_exists('group_id', $args)){
      $filter['group_id'] = $args['group_id'];
    }
    $reservations = $mapper->getReservations($filter);
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);
  });

  /*
   * Get all reservations from specific group
   */
  $app->get('/group/{group_id:[0-9]+}', function (Request $request, Response $response, $args){
    $this->logger->addInfo("Reservation list for subnet #" . $args['subnet_id']);
    $mapper = new ReservationMapper($this->db);
    // Filter data
    $filter = array('group_id' => $args['group_id']);

    $reservations = $mapper->getReservations($filter);
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);
  });

  /*
   * Get specific reservation by ID
   */
  $app->get('/id/{id:[0-9]+}', function($request, $response, $args) {
    $this->logger->addInfo('Request for reservation #' . $args['id']);
    $filter = array('id' => $args['id']);

    $mapper = new ReservationMapper($this->db);
    $reservation = $mapper->getReservations($filter);
    if(sizeof($reservation) == 1){
      return $response->withStatus(200)->withJson($reservation[0]->serialize());
    }else{
      return $response->withStatus(404)->withJson([]);
    }
  })->setName('reservation-details');

  /*
   * Get specific reservation by IP address
   */
  $app->get('/ip/{ip:[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}}', function($request, $response, $args) {
    $this->logger->addInfo('Request for reservation with IP: ' . $args['ip']);
    $filter = array('ip' => $args['ip']);

    $mapper = new ReservationMapper($this->db);
    $reservation = $mapper->getReservations($filter);
    if(sizeof($reservation) == 1){
      return $response->withStatus(200)->withJson($reservation[0]->serialize());
    }else{
      return $response->withStatus(404)->withJson([]);
    }
  });

  /*
   * Get specific reservation by MAC address
   */
  $app->get('/mac/{mac:(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})}', function($request, $response, $args) {
    $this->logger->addInfo('Request for reservation with MAC: ' . $args['mac']);
    $mapper = new ReservationMapper($this->db);

    $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
    $filter = array('mac' => intval($clean_mac, 16));

    $mapper = new ReservationMapper($this->db);
    $reservations = $mapper->getReservations($filter);
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);
  });
});


// Run application
$app->run();
