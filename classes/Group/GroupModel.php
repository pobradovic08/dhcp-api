<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/10/2017
 * Time: 2:07 PM
 */

namespace Dhcp\Group;


use \Illuminate\Database\Eloquent\Model;

class GroupModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'groups';
    protected $primaryKey = 'group_id';

    protected $with = [ 'subnet' ];

    public function subnet () {
        return $this->hasOne('\Dhcp\Subnet\SubnetModel', 'subnet_id', 'subnet_id');
    }

}