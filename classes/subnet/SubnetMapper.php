<?php

namespace Dhcp\Subnet;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/4/2017
 * Time: 3:39 PM
 */
class SubnetMapper {

    protected $db;

    public function __construct ($db) {
        $this->db = $db;
    }

    public function getSubnets (array $data) {
        // Array of where statements to be concatenated
        $where_arr = [];
        $where_sql = "";
        if (array_key_exists('subnet_id', $data)) {
            $where_arr[] = "`subnet_id` = :subnet_id";
        }
        if (array_key_exists('vlan_id', $data)){
            $where_arr[] = "`vlan` = :vlan_id";
        }
        if (array_key_exists('ip', $data)) {
            // Fix for PDO not allowing the same named parameter to be used multiple times
            $data['ip2'] = $data['ip'];
            $where_arr[] = "`from_address` <= INET_ATON(:ip) AND `to_address` >= INET_ATON(:ip2) LIMIT 0,1";
        }
        // Join all cases
        if (!empty($where_arr)) {
            $where_sql = 'WHERE ' . join(' AND ', $where_arr);
        }
        $sql = "SELECT
            `subnet_id`, `vlan`,
            INET_NTOA(`network`) as network, INET_NTOA(`network_mask`) as network_mask,
            `description` as subnet_description
            FROM `subnets` $where_sql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $tmp = $stmt->fetchAll();
        $results = [];
        foreach ( $tmp as $row ) {
            $results[] = new SubnetEntry($row);
        }
        return $results;
    }

    public function getFreeAddresses ($id) {
        $addresses = [];
        $sql = "SELECT `from_address`, `to_address` FROM `subnets` WHERE `subnet_id` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        if ($result) {
            $reservationMapper = new ReservationMapper($this->db);
            $reserved_addresses = $reservationMapper->getReservationListForSubnet($id);
            for ( $i = $result['from_address']; $i <= $result['to_address']; $i++ ) {
                if (!in_array($i, $reserved_addresses)) {
                    $addresses[] = long2ip($i);
                }
            }
        } else {
            throw new \InvalidArgumentException("No subnet with ID #$id");
        }
        return $addresses;
    }

    private function getSubnetId (SubnetEntry $se) {

    }

    public function insertSubnet (SubnetEntry $se) {
        $subnet_id = $this->getSubnetId($se);
        if ($subnet_id) {
            $se->setId($subnet_id);
            return $this->updateSubnet($se);
        } else {
            return $this->insertSubnet($se);
        }
    }

    public function createSubnet (SubnetEntry $se) {

    }

    public function updateSubnet (SubnetEntry $se) {

    }

    public function deleteSubnet (SubnetEntry $se) {

    }
}