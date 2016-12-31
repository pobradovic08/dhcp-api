<?php

class EndHostEntry {

    private $id;
    private $description;
    private $hostname;
    private $mac;
    private $end_host_type;    // EndHostType object
    private $end_host_type_id;
    private $production;
    private $insert_time;
    private $update_time;

    public function __construct (array $data) {
        if (isset($data['end_host_id'])) {
            $this->id = (int)$data['end_host_id'];
        }
        /*
         * Required arguments
         */
        //TODO: regexp check
        $this->hostname = $data['hostname'];
        $regexp_mac = "/^(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}|[0-9a-fA-f]{12})$/";
        if (preg_match ($regexp_mac, $data['mac'])) {
            $data['mac'] = preg_replace ('/[-:.]/', '', $data['mac']);
            $this->mac = $data['mac'];
        } else {
            throw new InvalidArgumentException("MAC format invalid: " . $data['mac']);
        }
        /*
         * If reading from database we have EndHostTypeEntry object
         * User POST/PUT request has just end host type ID
         */
        if (isset($data['end_host_type']) && $data['end_host_type'] instanceof EndHostTypeEntry) {
            $this->end_host_type = $data['end_host_type'];
            $this->end_host_type_id = $this->end_host_type->getId ();
        } elseif (is_int ($data['end_host_type_id']) and $data['end_host_type_id'] > 0) {
            $this->end_host_type_id = (int)$data['end_host_type_id'];
        } else {
            throw new InvalidArgumentException("No EndHostType object or valid End Host Type ID passed");
        }
        /*
         * Optional arguments
         */
        $this->description = $this->parse_var ($data, 'end_host_description', null);
        $this->production = (bool)$this->parse_var ($data, 'production', false);
        $this->insert_time = (int)$this->parse_var ($data, 'end_host_insert_time', 0);
        $this->update_time = (int)$this->parse_var ($data, 'end_host_update_time', 0);
    }

    private static function parse_var ($array, $key, $default_value) {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default_value;
        }
    }

    public function getId () {
        return $this->id;
    }

    public function getHostname () {
        return $this->hostname;
    }

    public function getDescription () {
        return $this->description;
    }

    public function getMacHex () {
        return strtolower (join ('', str_split ($this->mac, 4)));
    }

    public function getMac () {
        return strtolower (join ('.', str_split ($this->mac, 4)));
    }

    public function getType () {
        return $this->end_host_type;
    }

    public function getTypeId () {
        if ($this->end_host_type) {
            return $this->end_host_type->getId ();
        } else {
            return $this->end_host_type_id;
        }
    }

    public function isProduction () {
        return $this->production;
    }

    public function getInsertTime () {
        return $this->insert_time;
    }

    public function getUpdateTime () {
        return $this->update_time;
    }

    public function serialize () {
        return [
            'end_host_id' => $this->getId (),
            'hostname' => $this->getHostname (),
            'end_host_description' => $this->getDescription (),
            'mac' => $this->getMac (),
            'end_host_type' => $this->end_host_type->serialize (),
            'production' => $this->isProduction (),
            'end_host_insert_time' => $this->getInsertTime (),
            'end_host_update_time' => $this->getUpdateTime ()
        ];
    }

    public function db_data () {
        return [
            'end_host_id' => $this->id,
            'hostname' => $this->hostname,
            'description' => $this->description,
            'mac' => hexdec ($this->mac),
            'end_host_type_id' => $this->end_host_type_id,
            'production' => $this->production
        ];
    }

    public function db_unique_data () {
        return [
            'end_host_id' => $this->id,
            'hostname' => $this->hostname,
            'mac' => hexdec ($this->mac),
        ];
    }


    public function db_insert_data () {
        return [
            'hostname' => $this->hostname,
            'description' => $this->description,
            'mac' => hexdec ($this->mac),
            'end_host_type_id' => $this->end_host_type_id,
            'production' => $this->production
        ];
    }

}
