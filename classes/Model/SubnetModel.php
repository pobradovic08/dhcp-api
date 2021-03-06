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


namespace Dhcp\Model;

use Dhcp\Validator;
use \Illuminate\Database\Eloquent\Model;

class SubnetModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'subnets';
    protected $primaryKey = 'subnet_id';

    protected $fillable = [
        'vlan_id',
        'network',
        'network_mask',
        'from_address',
        'to_address',
        'description'
    ];

    /*
     * Set IP address
     * Accepts dotted-decimal format and long integer
     */
    public function setNetworkAttribute ($ip) {
        if (Validator::validateIpAddress($ip)) {
            $this->attributes['network'] = ip2long($ip);
        } elseif (Validator::validateIpAddress(long2ip($ip))) {
            $this->attributes['network'] = $ip;
        }
    }

    /*
     * Convert IP from long to dotted-decimal
     */
    public function getNetworkAttribute () {
        return long2ip($this->attributes['network']);
    }

    /*
     * Convert IP from long to dotted-decimal
     */
    public function getNetworkMaskAttribute () {
        return long2ip($this->attributes['network_mask']);
    }

    /*
     * Set IP mask
     * Accepts dotted-decimal format and long integer
     */
    public function setNetworkMaskAttribute ($ip) {
        if (Validator::validateIpMask($ip)) {
            $this->attributes['network_mask'] = ip2long($ip);
        } elseif (Validator::validateIpMask(long2ip($ip))) {
            $this->attributes['network_mask'] = $ip;
        }
    }

    /*
     * Convert IP from long to dotted-decimal
     */
    public function getFromAddressAttribute () {
        if(!$this->attributes['from_address']){
            $this->attributes['from_address'] = $this->attributes['network'] + 2;
        }
        return long2ip($this->attributes['from_address']);
    }

    /*
    * Set From IP address
    * Accepts dotted-decimal format and long integer
    */
    public function setFromAddressAttribute ($ip) {
        if (Validator::validateIpAddress($ip)) {
            $this->attributes['from_address'] = ip2long($ip);
        } elseif (Validator::validateIpAddress(long2ip($ip))) {
            $this->attributes['from_address'] = $ip;
        }
    }

    /*
     * Convert IP from long to dotted-decimal
     */
    public function getToAddressAttribute () {
        if(!$this->attributes['to_address']){
            $this->attributes['to_address'] = ($this->attributes['network'] | ~$this->attributes['network_mask']) - 1;
        }
        return long2ip($this->attributes['to_address']);
    }

    /*
    * Set To IP address
    * Accepts dotted-decimal format and long integer
    */
    public function setToAddressAttribute ($ip) {
        if (Validator::validateIpAddress($ip)) {
            $this->attributes['to_address'] = ip2long($ip);
        } elseif (Validator::validateIpAddress(long2ip($ip))) {
            $this->attributes['to_address'] = $ip;
        }
    }

    /*
     * Return group objects without subnet relationships
     */
    public function groups () {
        return $this->hasMany('\Dhcp\Model\GroupModel', 'subnet_id')->without('subnet');
    }

    public function reservations () {
        return $this->hasMany('\Dhcp\Model\GroupModel', 'subnet_id')->with('reservations');
    }

    /*
     * Checks if IP address belongs to the subnet
     */
    public function validIp ($ip) {
        $dec = ip2long($ip);
        return $this->attributes['from_address'] < $dec and $dec < $this->attributes['to_address'];
    }

    public function cidr () {
        return 32 - log(($this->attributes['network_mask'] ^ ip2long('255.255.255.255')) + 1, 2);
    }

}