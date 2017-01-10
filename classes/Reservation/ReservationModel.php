<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/10/2017
 * Time: 2:02 PM
 */

namespace Dhcp\Reservation;


use \Illuminate\Database\Eloquent\Model;

class ReservationModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $with = [ 'group' ];

    public function getIpAttribute () {
        return long2ip($this->attributes['ip']);
    }

    public function getActiveAttribute () {
        return boolval($this->attributes['active']);
    }

    /*
     * Return Group object for this Reservation
     */
    public function group () {
        return $this->hasOne('\Dhcp\Group\GroupModel', 'group_id', 'group_id');
    }

    public function end_host () {
        return $this->hasOne('\Dhcp\EndHost\EndHostModel', 'end_host_id', 'end_host_id');
    }

}