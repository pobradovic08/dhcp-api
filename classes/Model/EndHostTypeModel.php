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

class EndHostTypeModel extends Model {

    protected $table = 'end_host_types';
    protected $primaryKey = 'end_host_type_id';
    protected $fillable = ['description'];
    public $timestamps = false;

    /*
     * All hosts that are  of this type
     */
    public function endhosts () {
        return $this->hasMany('\Dhcp\Model\EndHostModel', 'end_host_type_id');
    }
}