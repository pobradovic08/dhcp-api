<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/2/2017
 * Time: 2:30 AM
 */
class Validator {

    /*
     * ID must be integer between 1 and infinity
     */
    static function validateId ($id) {
        return is_int ($id) and $id > 0;
    }

    /*
     * VLAN ID must be integer between 1 and 4094
     * 0 and 4095 are reserved
     */
    static function validateVlanId ($vlan_id) {
        return is_int ($vlan_id) and $vlan_id > 0 and $vlan_id < 4095;
    }

    static function validateIpAddress ($ip) {
        return boolval(filter_var($ip, FILTER_VALIDATE_IP));
    }

    static function validateIpMask ($mask) {
        if(!self::validateIpAddress($mask)){
            return false;
        }
        $dec = ip2long($mask);
        /*
         * If mask is 0.0.0.0 ip2long will return 0 which is evaluated to false
         * It's a false negative, so...
         */
        if($dec or $mask == '0.0.0.0'){
            $bin = decbin($dec);
            // For decbin(0) result is just "0" so /^0$/
            return boolval(preg_match('/(^0$|^1+0*$)/', $bin));
        }
        return false;
    }

    /*
     * String must be 64 or less characters
     */
    static function validateDescription ($description){
        return strlen($description) <= 64 and strlen($description) > 0;
    }
}