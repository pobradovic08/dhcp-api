<?php

class SubnetEntry {

  protected $subnet_id;
  protected $vlan;
  protected $network;
  protected $network_mask;
  protected $subnet_description;

  public function __construct(array $data){
    if(isset($data['subnet_id'])){
      $this->subnet_id = (int) $data['subnet_id'];
    }
    $this->vlan = (int) $data['vlan'];
    $this->network = $data['network'];
    $this->network_mask = $data['network_mask'];
    $this->subnet_description = $data['subnet_description'];
  }

  private function validate() {
    return false;
  }

  public function getId() {
    return $this->subnet_id;
  }

  public function getVlan(){
    return $this->vlan;
  }

  public function getNetwork(){
    return $this->network;
  }

  public function getNetworkMask() {
    return $this->network_mask;
  }

  public function getNetworkAddress() {

  }

  public function getDescription() {
    return $this->subnet_description;
  }

  public function serialize() {
    return [
      'subnet_id' => $this->getId(),
      'subnet_vlan' => $this->getVlan(),
      'subnet_network' => $this->getNetwork(),
      'subnet_mask' => $this->getNetworkMask(),
      'subnet_description' => $this->getDescription(),
    ];
  }
}
