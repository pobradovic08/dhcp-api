<?php

class SubnetEntry {

    protected $subnet_id;
    protected $vlan;
    protected $network;
    protected $network_mask;
    protected $subnet_description;
    /*
     * These will be calculated automatically
     */
    protected $first_host_address;
    protected $last_host_address;
    protected $network_address;
    protected $broadcast_address;
    protected $cidr;

    public function __construct (array $data) {
        if (isset($data['subnet_id'])) {
            if (Validator::validateId ($data['subnet_id'])) {
                $this->subnet_id = (int)$data['subnet_id'];
            } else {
                throw new \InvalidArgumentException("Subnet ID is invalid");
            }
        }
        if (Validator::validateVlanId ($data['vlan'])) {
            $this->vlan = (int)$data['vlan'];
        } else {
            throw new \InvalidArgumentException("Subnet ID is invalid");
        }
        if(Validator::validateIpAddress($data['network'])) {
            $this->network = $data['network'];
        }else{
            throw new \InvalidArgumentException("Network address not valid");
        }
        $this->network_mask = $data['network_mask'];
        $this->subnet_description = $data['subnet_description'];
    }

    private function validate () {
        return false;
    }

    public function getId () {
        return $this->subnet_id;
    }

    public function getVlan () {
        return $this->vlan;
    }

    public function getNetwork () {
        return $this->network;
    }

    public function getNetworkMask () {
        return $this->network_mask;
    }

    public function getCidr () {
        return $this->cidr;
    }

    public function getNetworkAddress () {

    }

    public function getBroadcastAddress () {

    }

    public function getFirstHostAddress () {

    }

    public function getLastHostAddress () {

    }

    public function getDescription () {
        return $this->subnet_description;
    }

    public function serialize () {
        return [
            'subnet_id' => $this->getId (),
            'subnet_vlan' => $this->getVlan (),
            'subnet_network' => $this->getNetwork (),
            'subnet_mask' => $this->getNetworkMask (),
            'subnet_description' => $this->getDescription (),
        ];
    }
}
