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


require __DIR__ . '/vendor/autoload.php';

var_dump(\Dhcp\EndHost\EndHostEntryTest::class);


class Group extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'groups';
    protected $primaryKey = 'group_id';

    public function subnet() {
        return $this->belongsTo('Subnet');
    }
}

class Subnet extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'subnets';
    protected $primaryKey = 'subnet_id';

    public function groups() {
        return $this->hasMany('Group');
    }
}



$db = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'dhcp_t',
    'username' => 'dhcp',
    'password' => 'dhcp',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
];

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();


$subnet = Subnet::with('groups')->find(3);


echo $subnet->toJson();
