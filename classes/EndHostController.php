<?php

use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostController {
   protected $ci;
   //Constructor
   public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
   }
   
   public function get_host($request, $response, $args) {
     $this->ci->logger->addInfo("Full end host list");
     $mapper = new EndHostMapper($this->ci->db);
     $endhosts = $mapper->getEndHosts(array());
     $array = [];
     foreach ($endhosts as $endhost) {
       $array[] = $endhost->serialize();
     }
     return $response->withStatus(200)->withJson($array);
   }
   
   public function get_host_by_id($request, $response, $args) {
     $this->ci->logger->addInfo("Rrequested end host #" . $args['end_host_id']);
     $mapper = new EndHostMapper($this->ci->db);
     $filter = array('end_host_id' => $args['end_host_id']);
     $endhost = $mapper->getEndHosts($filter);
     if(sizeof($endhost) == 1) {
       return $response->withStatus(200)->withJson($endhost[0]->serialize());
     } else {
       return $response->withStatus(404)->withJson([]);
     }
   }
      
   public function get_host_by_mac($request, $response, $args) {
     $this->ci->logger->addInfo("Rrequested end with MAC: " . $args['mac']);
     $mapper = new EndHostMapper($this->ci->db);
     $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
     $filter = array('mac' => intval($clean_mac, 16));
     $endhost = $mapper->getEndHosts($filter);
     if(sizeof($endhost) == 1) {
       return $response->withStatus(200)->withJson($endhost[0]->serialize());
     } else {
       return $response->withStatus(404)->withJson([]);
     }
   }

   public function get_search_host($request, $response, $args) {
     $this->ci->logger->addInfo("Searching for host with pattern: " . $args['pattern']);
     $mapper = new EndHostMapper($this->ci->db);
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
   }

   public function post_host($request, $response, $args) {
     $required_params = array(
                          array (
                            'name' => 'hostname',
                            'filter' => FILTER_SANITIZE_STRING,
                            'regexp' => '/[a-zA-Z0-9-]+/'
                          ),
                          array (
                            'name' => 'mac',
                            'filter' => FILTER_SANITIZE_STRING,
                            'regexp' => '/^(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})$/'
                          ),
                          array (
                            'name' => 'end_host_type_id',
                            'filter' => FILTER_SANITIZE_NUMBER_INT,
                            'regexp' => '/[0-9]+/'
                          )
                        );
     $optional_params = array(
                          array (
                            'name' => 'description',
                            'filter' => FILTER_SANITIZE_STRING,
                            'regexp' => '/.*/'
                          ),
                          array (
                            'name' => 'production',
                            'filter' => FILTER_SANITIZE_NUMBER_INT,
                            'regexp' => '/[01]/'
                          )
                        );
     $data = [];
     foreach ($required_params as $param) {
       if(! $request->getParam($param['name'])) {
         return $response->withStatus(400)->withJson(array('message' => "Required parameter '" . $param['name'] . "' missing"));
       } else {
         $filtered_value = filter_var($request->getParam($param['name']), $param['filter']);
         if (preg_match($param['regexp'], $filtered_value)) {
           $data[$param['name']] = $filtered_value;
         }else{
           return $response->withStatus(400)->withJson(array('message' => "Required parameter '" . $param['name'] . "' invalid"));
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
     $mapper = new EndHostMapper($this->ci->db);
     $result = $mapper->createEndHost($endhost);
     if($result['success']){
       if($result['rows'] == 1) {
         $result['message'] = "Created new end host #" . $result['data']['end_host_id'];
         $this->ci->logger->addInfo("Added new end host. Hostname: " . $data['hostname'] ."; MAC: " . $data['mac']);
       }else if($result['rows'] == 2){
         $result['message'] = "Updated existing end host #" . $result['data']['end_host_id'];
         $this->ci->logger->addInfo("Updated existing end host. Hostname: " . $result['data']['hostname'] ."; MAC: " . $result['data']['mac']);
       }
       return $response->withStatus(200)->withJson($result);
     }else{
       return $response->withStatus(400)->withJson($result);
     }
   }

   public function delete_host($request, $response, $args){
     $this->ci->logger->addInfo("Delete end host type #" . $args['end_host_type_id']);
     $mapper = new EndHostTypeMapper($this->ci->db);
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
   }
}
