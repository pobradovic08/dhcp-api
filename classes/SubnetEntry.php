<?php

class SubnetEntry {

    /*
     * Required parameters
     */
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
        if (isset($data['vlan'], $data['network'],
            $data['network_mask'], $data['subnet_description'])) {
            if (isset($data['subnet_id'])) {
                $this->setId($data['subnet_id']);
            }
            $this->setVlan($data['vlan']);
            $this->setNetwork($data['network']);
            $this->setNetworkMask($data['network_mask']);
            $this->setDescription($data['subnet_description']);
        } else {
            throw new \InvalidArgumentException("Missing required parameter(s)");
        }

        /*
         * Not too pretty so order is important!
         * Calculate CIDR first
         * Calculate network before fist hop, calculate broadcast before last hop
         */
        $this->cidr = 32 - log((ip2long($data['network_mask']) ^ ip2long('255.255.255.255')) + 1, 2);
        $this->network_address = $this->calculateNetwork();
        $this->broadcast_address = $this->calculateBroadcast();
        $this->first_host_address = $this->calculateFirstHop();
        $this->last_host_address = $this->calculateLastHop();

        if ($this->network != $this->network_address) {
            throw new InvalidArgumentException("Invalid subnet address. Try with: " . long2ip($this->network_address));
        }

    }

    private function calculateNetwork () {
        return $this->network & $this->network_mask;
    }

    private function calculateBroadcast () {
        return $this->network_address | ~$this->network_mask;
    }

    private function calculateFirstHop () {
        if ($this->cidr >= 31) {
            return $this->network_address;
        } else {
            return $this->network_address + 1;
        }
    }

    private function calculateLastHop () {
        if ($this->cidr >= 31) {
            return $this->broadcast_address;
        } else {
            return $this->broadcast_address - 1;
        }
    }

    public function isValidAddress ($ip) {
        if (Validator::validateIpAddress($ip)) {
            return (ip2long($ip) & $this->network_mask) == $this->network_address;
        }
        return false;
    }

    public function isValidHostAddress ($ip) {
        if (Validator::validateIpAddress($ip)) {
            $dec_ip = ip2long($ip);
            $in_subnet = (ip2long($ip) & $this->network_mask) == $this->network_address;
            return $in_subnet and
                   (long2ip($dec_ip) != $this->getBroadcastAddress()) and
                   (long2ip($dec_ip) != $this->getNetworkAddress());
        }
        return false;
    }

    public function getId () {
        return $this->subnet_id;
    }

    public function setId ($id) {
        if (Validator::validateId($id)) {
            $this->subnet_id = (int)$id;
        } else {
            throw new \InvalidArgumentException("Subnet ID is invalid");
        }
    }

    public function getVlan () {
        return $this->vlan;
    }

    public function setVlan ($vlan_id) {
        if (Validator::validateVlanId($vlan_id)) {
            $this->vlan = (int)$vlan_id;
        } else {
            throw new \InvalidArgumentException("Subnet ID is invalid");
        }
    }

    public function getNetwork () {
        return long2ip($this->network);
    }

    public function setNetwork ($network) {
        if (Validator::validateIpAddress($network)) {
            $this->network = ip2long($network);
        } else {
            throw new \InvalidArgumentException("Network address not valid");
        }
    }

    public function getNetworkMask () {
        return long2ip($this->network_mask);
    }

    public function setNetworkMask ($mask) {
        if (Validator::validateIpMask($mask)) {
            $this->network_mask = ip2long($mask);
        } else {
            throw new \InvalidArgumentException("Network mask is not valid");
        }
    }

    public function getCidr () {
        return $this->cidr;
    }

    public function getNetworkAddress () {
        return long2ip($this->network_address);
    }

    public function getBroadcastAddress () {
        return long2ip($this->broadcast_address);
    }

    public function getFirstHostAddress () {
        return long2ip($this->first_host_address);
    }

    public function getLastHostAddress () {
        return long2ip($this->last_host_address);
    }

    public function getDescription () {
        return $this->subnet_description;
    }

    private function setDescription ($subnet_description) {
        if (Validator::validateDescription($subnet_description)) {
            $this->subnet_description = $subnet_description;
        } else {
            throw new \InvalidArgumentException("Subnet description too long");
        }
    }

    public function serialize () {
        return [
            'subnet_id' => $this->getId(),
            'subnet_vlan' => $this->getVlan(),
            'subnet_network' => $this->getNetwork(),
            'subnet_mask' => $this->getNetworkMask(),
            'subnet_description' => $this->getDescription(),
        ];
    }
}
