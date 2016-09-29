<?php

class ReservationMapper {

  protected $db;

  public function __construct($db){
    $this->db = $db;
  }

  public function getReservations (array $data) {
    // Array of where statements to be concatenated
    $where_arr = array();
    $where_sql = "";
    if(array_key_exists('subnet_id', $data)){
      $where_arr[] = 's.`subnet_id` = :subnet_id';
    }
    if(array_key_exists('group_id', $data)){
      $where_arr[] = 'g.`group_id` = :group_id';
    }
    if(array_key_exists('id', $data)){
      $where_arr[] = 'r.`reservation_id` = :id';
    }
    if(array_key_exists('ip', $data)){
      $where_arr[] = "`ip` = INET_ATON(:ip)";
    }
    if(array_key_exists('mac', $data)){
      $where_arr[] = "`mac` = :mac";
    }

    // Join all cases
    if(!empty($where_arr)){
      $where_sql = 'WHERE ' . join(' AND ', $where_arr);
    }
    
    $sql = "SELECT `reservation_id`, r.end_host_id, hex(eh.`mac`) as mac, INET_NTOA(`ip`) as ip, r.`group_id`,
            eh.`hostname`, `comment`, `active`, r.`insert_time`, r.`update_time`, `vlan`,
            INET_NTOA(`network`) as network, INET_NTOA(`network_mask`) as network_mask,
            eh.`description` as end_host_description,
            g.`description` as group_description, s.`description` as subnet_description,
	    g.`name` as group_name
	    FROM reservations r
            LEFT JOIN end_hosts eh ON r.end_host_id = eh.end_host_id
	    LEFT JOIN groups g ON r.group_id = g.group_id
	    LEFT JOIN subnets s ON g.subnet_id = s.subnet_id
	    $where_sql";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($data);
    #var_dump($stmt);
    $results = [];
    while($row = $stmt->fetch()){
      $results[] = new ReservationEntry($row);
    }
    return $results;
  }
}
