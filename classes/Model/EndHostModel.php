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

class EndHostModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'end_hosts';
    protected $primaryKey = 'end_host_id';
    protected $fillable = [
        'hostname',
        'mac',
        'end_host_type_id',
        'description',
        'production',
    ];
    protected $casts = [
        'production' => 'boolean',
    ];

    // By default fetch type data
    //TODO: remove default type fetch
    protected $with = [
        'type'
    ];

    /*
     * Format MAC address as HHHH.HHHH.HHHH
     */
    public function getMacAttribute () {
        return wordwrap(dechex($this->attributes['mac']), 4, '.', true);
    }

    /*
     * Convert MAC address to decimal
     */
    public function setMacAttribute ($mac) {
        $clean_mac = preg_replace('/[^%0-9A-Fa-f]/i', '', $mac);
        $this->attributes['mac'] = hexdec($clean_mac);
    }

    public function getMacDecimalAttribute () {
        return $this->attributes['mac'];
    }

    /*
     * Get type of this host
     */
    public function type () {
        return $this->hasOne('\Dhcp\Model\EndHostTypeModel', 'end_host_type_id', 'end_host_type_id');
    }

    /*
     * Get all reservations for this host
     */
    public function reservations () {
        return $this->hasMany('\Dhcp\Model\ReservationModel', 'end_host_id');
    }
}