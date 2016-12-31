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

    // Filter by specific `end_host_type_id`
    if(array_key_exists('end_host_type_id', $data)){
      $where_arr[] = "`end_host_type_id` = :end_host_type_id";
    }

    // Join all cases, build and prepare SQL query
    if(!empty($where_arr)){
      $where_sql = 'WHERE ' . join(' AND ', $where_arr);
    }
    $sql = "SELECT `end_host_type_id`, `description` as end_host_type_description
	    FROM end_host_types eht
	    $where_sql";

    $stmt = $this->db->prepare($sql);

    // Execute statement with provided arguments
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
      $response['object'] = $this->getTypes(array('end_host_type_id' => $this->db->lastInsertId()))[0]->serialize();
    }
    return $response;
  }

  public function editType($data) {
    $result = array('success' => false);
    $sql = "UPDATE `end_host_types` SET `description` = :description WHERE `end_host_type_id` = :end_host_type_id";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute($data)){
      $result['success'] = true;
      $result['object'] = $this->getTypes(array('end_host_type_id' => $data['end_host_type_id']))[0]->serialize();
    }
    return $result;
  }

  public function deleteType($id) {
    $response = array("success" => false);
    $sql = "DELETE FROM `end_host_types` WHERE `end_host_type_id` = :id";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute(array('id' => $id))){
      $response['success'] = true;
      $response['deleted_count'] = $stmt->rowCount();
    }
    return $response;
  }
}
