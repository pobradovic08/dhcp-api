<?php

class EndHostMapper {

  protected $db;

  public function __construct($db){
    $this->db = $db;
  }

  public function getEndHosts (array $data) {
    // Array of where statements to be concatenated
    $where_arr = array();
    $where_sql = "";
    if(array_key_exists('end_host_id', $data)){
      $where_arr[] = "`end_host_id` = :end_host_id";
    }
    if(array_key_exists('end_host_type_id', $data)){
      $where_arr[] = "`end_host_type_id` = :end_host_type_id";
    }
    if(array_key_exists('search', $data)){
      $where_arr[] = "eh.`description` LIKE :search OR HEX(eh.`mac`) LIKE :search OR eh.`hostname` LIKE :search";
    }

    // Join all cases
    if(!empty($where_arr)){
      $where_sql = 'WHERE ' . join(' AND ', $where_arr);
    }
    $sql = "SELECT eh.`end_host_id`, eh.`hostname`, HEX(eh.`mac`) as mac, eh.`end_host_type_id`, eh.`description`,
            eh.`insert_time`, `update_time`, eht.`description` as end_host_type_description
	    FROM end_hosts eh
	    LEFT JOIN end_host_types eht on eh.`end_host_type_id` = eht.`end_host_type_id`
	    $where_sql";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($data);
    $results = [];
    while($row = $stmt->fetch()){
      $results[] = new EndHostEntry($row);
    }
    return $results;
  }

  public function createEndHost (EndHostEntry $eh) {
    $sql = "INSERT INTO end_hosts (`hostname`, `description`, `mac`, `end_host_type_id`, `insert_time`)
            VALUES( :hostname, :description, :mac, :end_host_type_id, UNIX_TIMESTAMP() )";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute($eh->db_data())){
      return true;
    } else {
      return false;
    }
  }
}
