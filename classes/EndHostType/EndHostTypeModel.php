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

    protected $table = 'end_host_types';
    protected $primaryKey = 'end_host_type_id';
    protected $fillable = ['description'];
    public $timestamps = false;

    /*
     * All hosts that are  of this type
     */
    public function endhosts () {
        return $this->hasMany('\Dhcp\EndHost\EndHostModel', 'end_host_type_id');
    }

}