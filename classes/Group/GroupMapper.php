<?php

namespace Dhcp\Group;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/5/2017
 * Time: 3:13 PM
 */
class GroupMapper {

    protected $db;

    public function __construct ($db) {
        $this->db = $db;
    }

    public function getGroups(array $data){
        // Array of where statements to be concatenated
        $where_arr = [];
        $where_sql = "";
        if (array_key_exists('group_id', $data)) {
            $where_arr[] = "`group_id` = :group_id";
        }
        if (array_key_exists('subnet_id', $data)){
            $where_arr[] = "`subnet_id` = :subnet_id";
        }
        // Join all cases
        if (!empty($where_arr)) {
            $where_sql = 'WHERE ' . join(' AND ', $where_arr);
        }
        $sql = "SELECT
            `group_id`, `subnet_id` as group_subnet_id, `name` as group_name,
            `description` as group_description
            FROM `groups` $where_sql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $tmp = $stmt->fetchAll();
        $results = [];
        foreach ( $tmp as $row ) {
            $results[] = new GroupEntry($row);
        }
        return $results;
    }
}