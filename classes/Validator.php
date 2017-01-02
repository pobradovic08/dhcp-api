<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/2/2017
 * Time: 2:30 AM
 */
class Validator {

    static function validateId ($id) {
        return is_int ($id) and $id > 0;
    }

    static function validateVlanId ($vlan_id) {
        return is_int ($vlan_id) and $vlan_id > 0 and $vlan_id < 4095;
    }

    static function validateIpAddress ($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    static function validateIpMask ($mask) {
        return false;
    }
}