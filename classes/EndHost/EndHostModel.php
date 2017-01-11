<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/9/2017
 * Time: 4:57 PM
 */

namespace Dhcp\EndHost;

use \Illuminate\Database\Eloquent\Model;

class EndHostModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'end_hosts';
    protected $primaryKey = 'end_host_id';

    protected $casts = [
        'production' => 'boolean',
    ];

    // By default fetch type data
    protected $with = [
        'type'
    ];

    /*
     * Format MAC address as HHHH.HHHH.HHHH
     */
    public function getMacAttribute () {
        return wordwrap(dechex($this->attributes['mac']), 4, '.', true);
    }

    //TODO: setMacAttribute()

    /*
     * Get type of this host
     */
    public function type () {
        return $this->hasOne('\Dhcp\EndHostType\EndHostTypeModel', 'end_host_type_id', 'end_host_type_id');
    }

    /*
     * Get all reservations for this host
     */
    public function reservations () {
        return $this->hasMany('\Dhcp\Reservation\ReservationModel', 'end_host_id');
    }
}