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

class GroupModel extends Model {

//    const CREATED_AT = 'insert_time';
//    const UPDATED_AT = 'update_time';

    public $timestamps = false;
    protected $table = 'groups';
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'subnet_id',
        'name',
        'description'
    ];

    /*
     * Subnet that this group belongs to
     */
    public function subnet () {
        return $this->hasOne('\Dhcp\Model\SubnetModel', 'subnet_id', 'subnet_id');
    }

    /*
     * All reservations that are in this group
     * Group information is excluded
     */
    public function reservations () {
        return $this->hasMany('\Dhcp\Model\ReservationModel', 'group_id')->without('group');
    }
}