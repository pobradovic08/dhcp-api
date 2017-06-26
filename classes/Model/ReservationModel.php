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
    public function valid () {
        /*
         * Check for required parameters
         */
        if (!$this->attributes['end_host_id'] or !$this->attributes['ip'] || !$this->attributes['group_id']) {
            throw new \InvalidArgumentException("Missing end host, IP or group");
        }
        /*
         * Check if group and endhost entries exist
         */
        try {
            $group = GroupModel::findOrFail($this->attributes['group_id']);
            $subnet = SubnetModel::findOrFail($group->subnet_id);
            /*
             * Just for validation
             * Endhost is not used but it will raise an exception if it doesn't exist
             */
            $endhost = EndHostModel::findOrFail($this->attributes['end_host_id']);
            /*
             * Check if IP belongs to the subnet
             */
            if (!$subnet->validIp(long2ip($this->attributes['ip']))) {
                throw new \InvalidArgumentException("IP doesn't belong to the subnet");
            }
        } catch (ModelNotFoundException $e) {
            throw new \InvalidArgumentException("Group or endhost don't exist");
        }
        return true;
    }

    /*
    * Check if reservation with that IP exists
    */
    public function ipConflict () {
        $reservation = ReservationModel::with('end_host')
                                       ->where('ip', '=', $this->attributes['ip'])
                                       ->where('reservation_id', '!=', $this->attributes['reservation_id'])
                                       ->first();
        if ($reservation) {
            throw new \InvalidArgumentException("IP already reserved for '{$reservation['end_host']['hostname']}'.");
        }
        return false;
    }

    /*
     * Check if there are no other reservations for that host in the subnet
     * Count reservation entries that have given end_host_id AND are bound to
     * one of the groups that belong to a given group's parent subnet
     */
    public function endHostConflict () {
        /*
         * Check if group and endhost entries exist
         */
        try {
            $group = GroupModel::findOrFail($this->attributes['group_id']);
            $res = ReservationModel::select('reservations.reservation_id', 'end_hosts.hostname')
                                   ->join('groups', 'reservations.group_id', 'groups.group_id')
                                   ->join('end_hosts', 'reservations.end_host_id', 'end_hosts.end_host_id')
                                   ->where('groups.subnet_id', '=', $group->subnet_id)
                                   ->where('reservations.end_host_id', '=', $this->attributes['end_host_id'])
                                   ->where('reservations.reservation_id', '!=', $this->attributes['reservation_id'])
                                   ->first();
            if ($res) {
                throw new \InvalidArgumentException("Reservation for end host '{$res['hostname']}' already exists.");
            }
            return false;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /*
     * Check if the reservation is valid and is safe to be inserted in the database
     */
    public
    function safeToInsert () {
        return $this->valid() && !$this->ipConflict() && !$this->endHostConflict();
    }
}