<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/9/2017
 * Time: 1:06 PM
 */

//require __DIR__ . '/../vendor/autoload.php';


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
