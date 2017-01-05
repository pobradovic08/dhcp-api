<?php

require('EndHostTypeEntry.php');
require('GroupEntry.php');
require('EndHostEntry.php');
require('SubnetEntry.php');

class ReservationMapper {

    protected $db;

    public function __construct ($db) {
        $this->db = $db;
    }

    public function getReservations (array $data, $terse = false) {
        // Array of where statements to be concatenated
        $where_arr = [];
        $where_sql = "";
        if (array_key_exists('subnet_id', $data)) {
            $where_arr[] = 's.`subnet_id` = :subnet_id';
        }
        if (array_key_exists('group_id', $data)) {
            $where_arr[] = 'g.`group_id` = :group_id';
        }
        if (array_key_exists('id', $data)) {
            $where_arr[] = 'r.`reservation_id` = :id';
        }
        if (array_key_exists('ip', $data)) {
            $where_arr[] = "`ip` = INET_ATON(:ip)";
        }
        if (array_key_exists('mac', $data)) {
            $where_arr[] = "`mac` = :mac";
        }

        // Join all cases
        if (!empty($where_arr)) {
            $where_sql = 'WHERE ' . join(' AND ', $where_arr);
        }

        $sql = "SELECT `reservation_id`, r.end_host_id, hex(eh.`mac`) as mac, INET_NTOA(`ip`) as ip, r.`group_id`,
            eh.`hostname`, `comment`, `active`, r.`insert_time`, r.`update_time`, `vlan`,
            INET_NTOA(`network`) as network, INET_NTOA(`network_mask`) as network_mask,
            eh.`description` as end_host_description, eh.`production`,
            eh.`insert_time` as end_host_insert_time, eh.`update_time` as end_host_update_time,
            eht.`end_host_type_id`, eht.`description` as end_host_type_description,
            g.`description` as group_description,
            s.`subnet_id`, s.`description` as subnet_description,
	    g.`name` as group_name, g.`subnet_id` as group_subnet_id
	    FROM reservations r
            LEFT JOIN end_hosts eh ON r.end_host_id = eh.end_host_id
            LEFT JOIN end_host_types eht ON eh.end_host_type_id = eht.end_host_type_id
	    LEFT JOIN groups g ON r.group_id = g.group_id
	    LEFT JOIN subnets s ON g.subnet_id = s.subnet_id
	    $where_sql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $results = [];
        while ($row = $stmt->fetch()) {
            if (!$terse) {
                $row['end_host_type'] = new EndHostTypeEntry($row);
                $row['end_host'] = new EndHostEntry($row);
                $row['group'] = new GroupEntry($row);
                $row['subnet'] = new SubnetEntry($row);
            }
            $results[] = new ReservationEntry($row);
        }
        return $results;
    }

    public function getReservationListForSubnet ($id) {
        $sql = "SELECT ip FROM dhcp.reservations r
                LEFT JOIN groups g ON r.group_id = g.group_id
                LEFT JOIN subnets s ON g.subnet_id = s.subnet_id
                WHERE s.subnet_id = :subnet_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('subnet_id' => $id));
        $results = [];
        while($tmp  = $stmt->fetch()){
            $results[] = $tmp['ip'];
        }
        return $results;
    }
}
