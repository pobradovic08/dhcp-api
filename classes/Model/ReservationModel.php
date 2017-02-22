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
    public function valida () {
        /*
         * Check for required parameters
         */
        if (!$this->attributes['end_host_id'] or !$this->attributes['ip'] || !$this->attributes['group_id']) {
            return false;
        }
        /*
         * Check if group and endhost entries exist
         */
        try {
            $group = GroupModel::findOrFail($this->attributes['group_id']);
            $subnet = SubnetModel::findOrFail($group->subnet_id);
            $endhost = EndHostModel::findOrFail($this->attributes['end_host_id']);
            /*
             * Check if IP belongs to the subnet
             */
            if (!$subnet->validIp(long2ip($this->attributes['ip']))) {
                return false;
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }
        return true;
    }

    /*
    * Check if reservation with that IP exists
    */
    public function ipExists() {
        $reservation = ReservationModel::where('ip', '=', $this->attributes['ip'])->first();
        return $reservation ? true : false;
    }

    /*
     * Check if there are no other reservations for that host in the subnet
     * Count reservation entries that have given end_host_id AND are bound to
     * one of the groups that belong to a given group's parent subnet
     */
    public function endHostExists() {
        /*
         * Check if group and endhost entries exist
         */
        try {
            $group = GroupModel::findOrFail($this->attributes['group_id']);
            $count = $this->ci->capsule->table('reservations')->select('*')
                                       ->join('groups', 'reservations.group_id', 'groups.group_id')
                                       ->join('end_hosts', 'reservations.end_host_id', 'end_hosts.end_host_id')
                                       ->where('groups.subnet_id', '=', $group->subnet_id)
                                       ->where('reservations.end_host_id', '=', $this->attributes['end_host_id'])
                                       ->count();
            return $count ? true : false;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /*
     * Check if the reservation is valid and is safe to be inserted in the database
     */
    public function safeToInsert() {
        return $this->valid() && !$this->ipExists() && !$this->endHostExists();
    }
}