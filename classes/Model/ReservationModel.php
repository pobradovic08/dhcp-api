<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/10/2017
 * Time: 2:02 PM
 */

namespace Dhcp\Model;


use \Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\ModelNotFoundException;


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
        return $this->hasOne('\Dhcp\Model\GroupModel', 'group_id', 'group_id');
    }

    /*
     * End host for this reservation
     */
    public function end_host () {
        return $this->hasOne('\Dhcp\Model\EndHostModel', 'end_host_id', 'end_host_id');
    }

    /*
     * Check constraints
     */
    public function validate () {
        /*
         * Check for required parameters
         */
        if(!$this->attributes['end_host_id'] or !$this->attributes['ip'] || !$this->attributes['group_id']){
            return false;
        }
        /*
         * Check if group and endhost exist
         */
        try{
            $group = GroupModel::findOrFail($this->attributes['group_id']);
            $endhost = EndHostModel::findOrFail($this->attributes['end_host_id']);
        }catch (ModelNotFoundException $e){
            return false;
        }
        return GroupModel::all();
    }

}