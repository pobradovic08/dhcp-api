<?php

/**
 * ISC-DHCP Web API
 * Copyright (C) 2016  Pavle Obradovic (pajaja)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Dhcp;


class Validator {

    const REGEXP_ID = '/^[1-9][0-9]*$/';
    const REGEXP_MAC = '/^(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}-){5}[0-9A-Fa-f]{2}|(?:[0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2})$/';
    const REGEXP_HOSTNAME = '/^[a-zA-Z0-9-]+$/';
    const REGEXP_BOOL = '/^[01]$/';
    const REGEXP_FILENAME = '/^[\w\-. ]+$/';

    const IP = 1;
    const ID = 2;
    const MAC = 3;
    const HOSTNAME = 4;
    const DESCRIPTION = 5;
    const VLAN = 6;
    const FILENAME = 7;
    const MASK = 8;

    /*
     * ID must be integer between 1 and infinity
     */
    static function validateId( $id ) {
        return is_int( $id ) and $id > 0;
    }

    /*
     * VLAN ID must be integer between 1 and 4094
     * 0 and 4095 are reserved
     */
    static function validateVlanId( $vlan_id ) {
        return is_int( $vlan_id ) and $vlan_id > 0 and $vlan_id < 4095;
    }

    static function validateHostname( $hostname ) {
        return boolval( preg_match( self::REGEXP_HOSTNAME, $hostname ) );
    }

    static function validateFilename( $file ) {
        return boolval( preg_match( self::REGEXP_FILENAME, $file ) );
    }

    static function validateMacAddress( $mac ) {
        return boolval( preg_match( self::REGEXP_MAC, $mac ) );
    }

    static function validateIpAddress( $ip ) {
        return boolval( filter_var( $ip, FILTER_VALIDATE_IP ) );
    }

    static function validateIpMask( $mask ) {
        if ( !self::validateIpAddress( $mask ) ) {
            return false;
        }
        $dec = ip2long( $mask );
        /*
         * If mask is 0.0.0.0 ip2long will return 0 which is evaluated to false
         * It's a false negative, so...
         */
        if ( $dec or $mask == '0.0.0.0' ) {
            $bin = decbin( $dec );
            // For decbin(0) result is just "0" so /^0$/
            return boolval( preg_match( '/(^0$|^1+0*$)/', $bin ) );
        }
        return false;
    }

    /*
     * String must be 64 or less characters
     */
    static function validateDescription( $description ) {
        return strlen( $description ) <= 64 and strlen( $description ) > 0;
    }

    static function validateHttpCode( $code ) {
        return is_int( $code ) and ( $code >= 100 ) and ( $code < 600 );
    }

    static function validateArgument( $arguments, $argument_name, $regexp = null ) {
        // Check if argument exists
        if ( isset( $arguments[$argument_name] ) ) {
            // Match the regexp if defined or return true if filter succeeded
            if ( $regexp ) {
                switch ( $regexp ) {
                    case self::IP:
                        return self::validateIpAddress( $arguments[$argument_name] );
                    case self::MASK:
                        return self::validateIpMask( $arguments[$argument_name] );
                    case self::MAC:
                        return self::validateMacAddress( $arguments[$argument_name] );
                    case self::ID:
                        return self::validateId( intval( $arguments[$argument_name] ) );
                    case self::DESCRIPTION:
                        return self::validateDescription( $arguments[$argument_name] );
                    case self::VLAN:
                        return self::validateVlanId( intval( $arguments[$argument_name] ) );
                    case self::HOSTNAME:
                        return self::validateHostname( $arguments[$argument_name] );
                    case self::FILENAME:
                        return self::validateFilename( $arguments[$argument_name] );
                    default:
                        return boolval( preg_match( $regexp, $arguments[$argument_name] ) );
                }
            } else {
                return true;
            }
        }
        return false;
    }
}