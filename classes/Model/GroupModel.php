<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/10/2017
 * Time: 2:07 PM
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