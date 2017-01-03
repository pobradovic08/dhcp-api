<?php

class ReservationEntry {

    // Reservation data
    protected $reservation_id;
    protected $ip;
    protected $comment;
    protected $active;
    protected $insert_time;
    protected $update_time;
    // End host data
    protected $end_host;
    protected $end_host_id;
    // GroupEntry object
    protected $group;
    protected $group_id;
    // Subnet data
    protected $subnet;
    protected $subnet_id;

    public function __construct (array $data) {
        if (isset($data['reservation_id'])) {
            $this->reservation_id = (int)$data['reservation_id'];
        }

        if (isset($data['group']) and $data['group'] instanceof GroupEntry) {
            $this->group = $data['group'];
            $this->group_id = $this->group->getId ();
        } elseif (isset($data['group_id']) and Validator::validateId ($data['group_id'])) {
            $this->group_id = $data['group_id'];
        }

        if (isset($data['end_host']) and $data['end_host'] instanceof EndHostEntry) {
            $this->end_host = $data['end_host'];
            $this->end_host_id = $this->end_host->getId ();
        } elseif (isset($data['end_host_id']) and Validator::validateId ($data['end_host_id'])) {
            $this->end_host_id = $data['end_host_id'];
        }

        if (isset($data['subnet']) and $data['subnet'] instanceof SubnetEntry) {
            $this->subnet = $data['subnet'];
            $this->subnet_id = $this->subnet->getId ();
        } elseif (isset($data['subnet_id']) and Validator::validateId ($data['subnet_id'])) {
            $this->subnet_id = $data['subnet_id'];
        }

        if (isset($data['ip']) and Validator::validateIpAddress ($data['ip'])) {
            $this->ip = ip2long ($data['ip']);
        }
        $this->active = (bool)$this->parse_var ($data, 'active', false);
        $this->comment = (string)$this->parse_var ($data, 'comment');
        $this->insert_time = (int)$this->parse_var ($data, 'insert_time', 0);
        $this->update_time = (int)$this->parse_var ($data, 'update_time', 0);
    }

    private static function parse_var ($array, $key, $default_value = null) {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default_value;
        }
    }

    public function getId () {
        return $this->reservation_id;
    }

    public function getIp () {
        return long2ip ($this->ip);
    }

    public function isActive () {
        return $this->active;
    }

    public function getEndHost () {
        return $this->end_host;
    }

    public function getEndHostId () {
        return $this->end_host_id;
    }

    public function getGroup () {
        return $this->group;
    }

    public function getGroupId () {
        return $this->group_id;
    }

    public function getSubnet () {
        return $this->subnet;
    }

    public function getSubnetId () {
        return $this->subnet_id;
    }

    public function getComment () {
        return $this->comment;
    }

    public function getInsertTime () {
        return $this->insert_time;
    }

    public function getUpdateTime () {
        return $this->update_time;
    }

    public function serialize () {
        return [
            'reservation_id' => $this->getId (),
            'ip' => $this->getIp (),
            'active' => $this->isActive (),
            'reservation_comment' => $this->getComment (),
            'reservation_insert_time' => $this->getInsertTime (),
            'reservation_update_time' => $this->getUpdateTime (),
            'end_host' => $this->end_host->serialize (),
            'group' => $this->group->serialize (),
            'subnet' => $this->subnet->serialize (),
        ];
    }
}
