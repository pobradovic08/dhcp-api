<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/9/2017
 * Time: 4:57 PM
 */

namespace Dhcp\EndHost;

use \Illuminate\Database\Eloquent\Model;

class EndHostModel extends Model {

    protected $table = 'end_hosts';
    protected $primaryKey = 'end_host_id';

    public function type() {
        return $this->hasOne('\Dhcp\EndHostType\EndHostTypeModel', 'end_host_type_id', 'end_host_type_id');
    }

}