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
    protected $fillable = [
        'end_host_id',
        'group_id',
        'ip',
        'active',
        'comment'
    ];
    protected $casts = [
        'active' => 'boolean',
    ];

    /*
     * Format
     */
    public function getIpAttribute () {
        return long2ip($this->attributes['ip']);
    }

    public function setIpAttribute ($ip) {
        $this->attributes['ip'] = ip2long($ip);
    }

    /*
     * Group object for this reservation
     */
    public function group () {
        return $this->hasOne('\Dhcp\Group\GroupModel', 'group_id', 'group_id');
    }

    /*
     * End host for this reservation
     */
    public function end_host () {
        return $this->hasOne('\Dhcp\EndHost\EndHostModel', 'end_host_id', 'end_host_id');
    }

}