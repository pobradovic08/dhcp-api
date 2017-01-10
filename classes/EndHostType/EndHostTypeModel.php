<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/9/2017
 * Time: 4:59 PM
 */

namespace Dhcp\EndHostType;

use \Illuminate\Database\Eloquent\Model;

class EndHostTypeModel extends Model {

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'end_host_types';
    protected $primaryKey = 'end_host_type_id';

    public function endhosts() {
        return $this->hasMany('\Dhcp\EndHost\EndHostModel', 'end_host_type_id');
    }

}