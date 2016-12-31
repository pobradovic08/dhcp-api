<?php

require('EndHostTypeEntry.php');

class EndHostMapper {

  protected $db;

  public function __construct($db){
    $this->db = $db;
  }

  private function idExists(int $id) {
    $sql = "SELECT COUNT(`end_host_id`) as count FROM end_hosts WHERE `end_host_id` = :id";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute(['id' => $id])){
      $result = $stmt->fetchAll()[0];
      return ((int) $result['count']) > 0;
    }
  }

  private function isUnique(EndHostEntry $eh) {
    $sql = "SELECT COUNT(`end_host_id`) as count FROM end_hosts
            WHERE `end_host_id` = :end_host_id OR `hostname` = :hostname OR `mac` = :mac";
    $stmt = $this->db->prepare($sql);
    if($stmt->execute($eh->db_unique_data())){
      $result = $stmt->fetchAll()[0];
      return ((int) $result['count']) == 0;
    }
  }

  private function getEndHostId (EndHostEntry $eh) {
    $sql = "SELECT `end_host_id` FROM end_hosts WHERE
              `mac` = :mac OR `end_host_id` = :end_host_id
            LIMIT 0,1";
    $stmt = $this->db->prepare($sql);
    $data = [ 'end_host_id' => $eh->getId(),
              'mac' => $eh->db_data()['mac']];
    if($stmt->execute($data)){
      $result = $stmt->fetchAll()[0];
      return (int) $result['end_host_id'];
    }
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
    if(array_key_exists('mac', $data)){
      $where_arr[] = "eh.`mac` = :mac";
    }
    if(array_key_exists('search', $data)){
      // If search keyword looks like a hex number (MAC address)
      // Create new search key for comparing MAC addresses. SQL returns hex number so strip everything else
      if(preg_match('/[A-Fa-f0-9\.\:\-\%]/i', $data['search'])){
        $data['mac_search'] = preg_replace('/[^%0-9A-Fa-f]/i', '', $data['search']);
      }else{
        $data['mac_search'] = $data['search'];
      }
      $where_arr[] = "eh.`description` LIKE :search OR HEX(eh.`mac`) LIKE :mac_search OR eh.`hostname` LIKE :search";
    }

    // Join all cases
    if(!empty($where_arr)){
      $where_sql = 'WHERE ' . join(' AND ', $where_arr);
    }
    $sql = "SELECT eh.`end_host_id`, eh.`hostname`, HEX(eh.`mac`) as mac, eh.`end_host_type_id`,
            eh.`description` as end_host_description, eh.`production`,
            eh.`insert_time` as end_host_insert_time, `update_time` as end_host_update_time,
            eht.`description` as end_host_type_description
	    FROM end_hosts eh
	    LEFT JOIN end_host_types eht on eh.`end_host_type_id` = eht.`end_host_type_id`
	    $where_sql";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($data);
    $tmp = $stmt->fetchAll();
    $results = [];
    foreach($tmp as $row){
      $row['end_host_type'] = new EndHostTypeEntry($row);
      $results[] = new EndHostEntry($row);
    }
    return $results;
  }

  public function insertEndHost (EndHostEntry $eh) {
    $id_exists = $this->getEndHostId($eh);
    return $id_exists ? $this->updateEndHost($eh) : $this->createEndHost($eh);
  }

  private function createEndHost (EndHostEntry $eh) {
    $result = array('success' => false);
    if($this->isUnique($eh)){
      $sql = "INSERT INTO end_hosts (
                `hostname`, `description`, `mac`,
                `end_host_type_id`, `production`, `insert_time`
              ) VALUES (
                :hostname, :description, :mac,
                :end_host_type_id, :production, UNIX_TIMESTAMP()
              )";
      try {
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($eh->db_insert_data())){
          $last_insert_id = $this->db->lastInsertId();
          if($last_insert_id == "0"){
            var_dump($stmt->errorInfo());
          }
          $result['success'] = true;
          $result['rows'] = $stmt->rowCount(); 
          $result['data'] = $this->getEndHosts(array('end_host_id' => $last_insert_id))[0]->serialize();
        }
      } catch (PDOException $e) {
        $result['error'] = $e->getMessage();
      }
    }else{
        $result['message'] = "EndHost ID, MAC or hostname already exists";
    }
    return $result;
  }

  private function updateEndHost (EndHostEntry $eh) {
    $result = array('success' => false);
    $sql = "UPDATE `end_hosts` SET
              `hostname` = :hostname, `description` = :description,
              `end_host_type_id` = :end_host_type_id, `production` = :production,
              `update_time` = UNIX_TIMESTAMP()
            WHERE `end_host_id` = :end_host_id AND `mac` = :mac";
    try {
      $stmt = $this->db->prepare($sql);
      if($stmt->execute($eh->db_data())){
        $end_host = $this->getEndHosts(array('end_host_id' => $eh->getId()));
        if(sizeof($end_host) == 1 && $stmt->rowCount()){
          $result['data'] = $end_host[0]->serialize();
          $result['success'] = true;
          $result['message'] = "Updated end host #" . $eh->getId();
        }else{
          $result['message'] = "Entry #" . $eh->getId() . " not updated. MAC and EndHost ID mismatch.";
        }
      }
    } catch (PDOException $e) {
      $result['error'] = $e->getMessage();
    }
    return $result;    
  }
}
