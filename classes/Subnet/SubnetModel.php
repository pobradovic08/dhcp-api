<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/10/2017
 * Time: 2:15 PM
 */

namespace Dhcp\Subnet;

use \Illuminate\Database\Eloquent\Model;

class SubnetModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'subnets';
    protected $primaryKey = 'subnet_id';

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
     * Convert IP from long to dotted-decimal
     */
    public function getFromAddressAttribute () {
        return long2ip($this->attributes['from_address']);
    }

    /*
     * Convert IP from long to dotted-decimal
     */
    public function getToAddressAttribute () {
        return long2ip($this->attributes['to_address']);
    }

    /*
     * Return group objects without subnet relationships
     */
    public function groups () {
        return $this->hasMany('\Dhcp\Group\GroupModel', 'subnet_id')->without('subnet');
    }

    public function reservations() {
        return $this->hasMany('\Dhcp\Group\GroupModel', 'subnet_id')->with('reservations');
    }

}