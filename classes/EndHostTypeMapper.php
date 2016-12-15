<?php

class EndHostTypeMapper {

  protected $db;

  public function __construct($db){
    $this->db = $db;
  }

  public function getTypes (array $data) {
    // Array of where statements to be concatenated
    $where_arr = array();
    $where_sql = "";
    if(array_key_exists('end_host_type_id', $data)){
      $where_arr[] = "`end_host_type_id` = :end_host_type_id";
    }

    // Join all cases
    if(!empty($where_arr)){
      $where_sql = 'WHERE ' . join(' AND ', $where_arr);
    }
    $sql = "SELECT `end_host_type_id`, `description`
	    FROM end_host_types eht
	    $where_sql";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($data);
    $results = [];
    while($row = $stmt->fetch()){
      $results[] = new EndHostTypeEntry($row);
    }
    return $results;
  }

  public function addType ($description) {
    $response = array("success" => false);
    $sql = "INSERT INTO `end_host_types` (`description`) VALUES (:description)";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute(array('description' => $description))){
      $response['success'] = true;
      $response['id'] = $this->db->lastInsertId();
    }
    return $response;
  }

  public function deleteType($id) {
    $response = array("success" => false);
    $sql = "DELETE FROM `end_host_types` WHERE `end_host_type_id` = :id";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute(array('id' => $id))){
      $response['success'] = true;
    }
    return $response;
  }
}
