<?php

use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostTypeController {
   protected $ci;
   //Constructor
   public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
   }

   public function get_type ($request, $response, $args) {
     $this->ci->logger->addInfo("End host type list");
     $mapper = new EndHostTypeMapper($this->ci->db);
     $endhost_types = $mapper->getTypes(array());
     $array = [];
     foreach ($endhost_types as $type) {
       $array[] = $type->serialize();
     }
     return $response->withStatus(200)->withJson($array);
   }

   public function get_type_by_id ($request, $response, $args) {
     $this->ci->logger->addInfo("End host type #" . $args['end_host_type_id']);
     $mapper = new EndHostTypeMapper($this->ci->db);
     $filter = array('end_host_type_id' => $args['end_host_type_id']);
     $types = $mapper->getTypes($filter);
     if(sizeof($types) == 1){
       return $response->withStatus(200)->withJson($types[0]->serialize());
     }else{
       return $response->withStatus(404)->withJson([]);
     }
   }

   public function put_type_by_id ($request, $response, $args) {
     $this->ci->logger->addInfo("Updating End host type entry #" . $args['end_host_type_id']);
     if(! $request->getParam('description') || ! $args['end_host_type_id']){
       return $response->withStatus(400)->withJson(array('error' => "Required parameters missing"));
     }else{
       $mapper = new EndHostTypeMapper($this->ci->db);
       $data = array(
                 'end_host_type_id' => $args['end_host_type_id'],
                 'description' => $request->getParam('description')
               );
       $result = $mapper->editType($data);
       if($result['success']){
         return $response->withStatus(200)->withJson($result['object']);
       }else{
         return $response->withStatus(400)->withJson($result['object']);
       }
     }
   }

   public function post_type ($request, $response, $args){
     $this->ci->logger->addInfo("Creating new end host type with description: \"" . $request->getParam('description') . '"');
     $mapper = new EndHostTypeMapper($this->ci->db);
     if(! $request->getParam('description')){
       return $response->withStatus(400)->withJson(array('error' => "Required parameter 'description' missing"));
     }else{
       $result = $mapper->addType(filter_var($request->getParam('description', FILTER_SANITIZE_STRING)));
       if($result['success']){
         return $response->withStatus(200)->withJson($result['object']);
       }else{
         return $response->withStatus(400)->withJson($result['object']);
       }
     }
   }
}
