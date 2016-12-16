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
   * Get all End Hosts
   */
   $app->get('/[all]', function(Request $request, Response $response){
     $this->logger->addInfo("Full end host list");
     $mapper = new EndHostMapper($this->db);
     $endhosts = $mapper->getEndHosts(array());
     $array = [];
     foreach ($endhosts as $endhost) {
       $array[] = $endhost->serialize();
     }
     return $response->withStatus(200)->withJson($array); 
   });
  /*
   * Get end host by ID
   */
   $app->get('/id/{end_host_id:[0-9]+}[/]', function (Request $request, Response $response, $args){
     $this->logger->addInfo("Rrequested end host #" . $args['end_host_id']);
     $mapper = new EndHostMapper($this->db);
     $filter = array('end_host_id' => $args['end_host_id']);
     $endhost = $mapper->getEndHosts($filter);
     if(sizeof($endhost) == 1) {
       return $response->withStatus(200)->withJson($endhost[0]->serialize());
     } else {
       return $response->withStatus(404)->withJson([]);
     }
   });
  /*
   * Get end host by MAC address
   */
   $app->get('/mac/{mac:(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})}[/]', function (Request $request, Response $response, $args){
     $this->logger->addInfo("Rrequested end with MAC: " . $args['mac']);
     $mapper = new EndHostMapper($this->db);
     $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
     $filter = array('mac' => intval($clean_mac, 16));
     $endhost = $mapper->getEndHosts($filter);
     if(sizeof($endhost) == 1) {
       return $response->withStatus(200)->withJson($endhost[0]->serialize());
     } else {
       return $response->withStatus(404)->withJson([]);
     }
   });
  /*
   * Search host
   */
   $app->get('/search/{pattern}[/]', function (Request $request, Response $response, $args){
     $this->logger->addInfo("Searching for host with pattern: " . $args['pattern']);
     $mapper = new EndHostMapper($this->db);
     $filter = array('search' => '%' . $args['pattern'] . '%');
     $endhosts = $mapper->getEndHosts($filter);
     if(sizeof($endhosts) >= 1) {
       $array = [];
       foreach ($endhosts as $endhost) {
         $array[] = $endhost->serialize();
       }
       return $response->withStatus(200)->withJson($array);
     } else {
       return $response->withStatus(404)->withJson([]);
     }
   });
  /*
   *  Create new end host
   */
   $app->post('/add[/]', function (Request $request, Response $response, $args) {
     $required_params = array(
                          array ( 'name' => 'hostname', 'filter' => FILTER_SANITIZE_STRING, 'regexp' => '/[a-zA-Z0-9-]+/'),
                          array ( 'name' => 'mac', 'filter' => FILTER_SANITIZE_STRING, 'regexp' => '/[a-fA-F0-9.:-]+/'),
                          array ( 'name' => 'end_host_type_id', 'filter' => FILTER_SANITIZE_NUMBER_INT, 'regexp' => '/[0-9]+/')
                        );
     $optional_params = array(
                          array ( 'name' => 'description', 'filter' => FILTER_SANITIZE_STRING, 'regexp' => '/.*/')
                        );
     $data = [];
     foreach ($required_params as $param) {
       if(! $request->getParam($param['name'])) {
         return $response->withStatus(400)->withJson(array('error' => "Required parameter '" . $param['name'] . "' missing"));
       } else {
         $filtered_value = filter_var($request->getParam($param['name']), $param['filter']);
         if (preg_match($param['regexp'], $filtered_value)) {
           $data[$param['name']] = $filtered_value;
         }
       }
     }
     foreach ($optional_params as $param) {
       $filtered_value = filter_var($request->getParam($param['name']), $param['filter']);
       if (preg_match($param['regexp'], $filtered_value)) {
         $data[$param['name']] = $filtered_value;
       }
     }
     $endhost = new EndHostEntry($data);
     $mapper = new EndHostMapper($this->db);
     if($mapper->createEndHost($endhost)){
       $this->logger->addInfo("Added new end host. Hostname: " . $data['hostname'] ."; MAC: " . $data['mac']);
     }
   });
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
    * Create new end host type
    */
   $app->post('/type[/]', function (Request $request, Response $response, $args) {
     $this->logger->addInfo("Creating new end host type with description: \"" . $request->getParam('description') . '"');
     $mapper = new EndHostTypeMapper($this->db);
     if(! $request->getParam('description')){
       return $response->withStatus(400)->withJson(array('error' => "Required parameter 'description' missing"));
     }else{
       $result = $mapper->addType(filter_var($request->getParam('description', FILTER_SANITIZE_STRING)));
       if($result['success']){
         return $response->withStatus(200)->withJson($result);
       }else{
         return $response->withStatus(403)->withJson($result);
       }
     }
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

   $app->put('/type/id/{end_host_type_id:[0-9]+}[/]', function (Request $request, Response $response, $args) {
     $this->logger->addInfo("Updating End host type entry #" . $args['end_host_type_id']);
     if(! $request->getParam('description') || ! $request->getParam('end_host_type_id')){
       return $response->withStatus(400)->withJson(array('error' => "Required parameters missing"));
     }else{
       $mapper = new EndHostTypeMapper($this->db);
       $data = array(
                 'end_host_type_id' => $request->getParam('end_host_type_id'),
                 'description' => $request->getParam('description')
               );
       $result = $mapper->editType($data);
       if($result['success']){
         return $response->withStatus(200)->withJson($result);
       }else{
         return $response->withStatus(400)->withJson($result);
       }
     }
   });

   /*
    * Delete end host type by ID
    */
   $app->delete('/type/id/{end_host_type_id:[0-9]+}[/]', function (Request $request, Response $response, $args) {
     $this->logger->addInfo("Delete end host type #" . $args['end_host_type_id']);
     $mapper = new EndHostTypeMapper($this->db);
     $result = $mapper->deleteType($args['end_host_type_id']);
     $http_code = 400;
     if($result['success']){
       if($result['deleted_count']){
         $http_code = 200;
       }else{
         $http_code = 404;
       }
     }
     return $response->withStatus($http_code)->withJson($result);
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
