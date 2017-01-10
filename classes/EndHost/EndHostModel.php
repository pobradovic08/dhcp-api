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

    const CREATED_AT = 'insert_time';
    const UPDATED_AT = 'update_time';

    protected $table = 'end_hosts';
    protected $primaryKey = 'end_host_id';

    public function getMacAttribute () {
        return wordwrap(dechex($this->attributes['mac']), 4, '.', true);
    }

    public function getProductionAttribute () {
        return boolval($this->attributes['production']);
    }

    public function type () {
        return $this->hasOne('\Dhcp\EndHostType\EndHostTypeModel', 'end_host_type_id', 'end_host_type_id');
    }

    public function reservation () {
        //return $this->belongsToOne('\Dhcp\Reservation\ReservationModel', 'end_host_id');
    }

}