<?php

class ReservationEntry {

  // Reservation data
  protected $id;
  protected $ip;
  protected $comment;
  protected $active;
  protected $insert_time;
  protected $update_time;
  // End host data
  // TODO: Pass EndHostEntry object
  protected $end_host;
  protected $mac;
  protected $hostname;
  protected $end_host_description;
  // GroupEntry object
  protected $group;
  // Subnet data
  // TODO: Pass SubnetEntry object
  protected $subnet_id;
  protected $vlan;
  protected $network;
  protected $network_mask;
  protected $subnet_description;

  public function __construct(array $data){
    if(isset($data['reservation_id'])){
      $this->id = (int) $data['reservation_id'];
    }
    if($data['group'] instanceof GroupEntry){
      $this->group = $data['group'];
    }
    if($data['end_host'] instanceof EndHostEntry){
      $this->end_host = $data['end_host'];
    }
    $this->ip = $data['ip'];
    $this->active = (bool) $data['active'];
    $this->comment = $data['comment'];
    $this->insert_time = (int) $data['insert_time'];
    $this->update_time = (int) $data['update_time'];


    $this->subnet_id = $data['subnet_id'];
    $this->vlan = $data['vlan'];
    $this->network = $data['network'];
    $this->network_mask = $data['network_mask'];
    $this->subnet_description = $data['subnet_description'];
  }
  
  public function getId() {
    return $this->id;
  }

  public function getMac() {
    return strtolower(rtrim(chunk_split($this->mac, 4, '.'), '.'));
  }
  
  public function getEndHostDescription() {
    return $this->end_host_description;
  }

  public function getIp() {
    return $this->ip;
  }
  
  public function isActive() {
    return $this->active;
  }

  public function getHostname() {
    return $this->hostname;
  }

  public function getComment() {
    return $this->comment;
  }

  public function getInsertTime() {
    return $this->insert_time;
  }

  public function getUpdateTime() {
    return $this->update_time;
  }

  public function getVlan() {
    return $this->vlan;
  }

  public function getNetwork() {
    return $this->network . '/' . $this->network_mask;
  }

  public function getSubnetDescription() {
    return $this->subnet_description;
  }

  public function serialize() {
    return [
      'reservation_id' => $this->getId(),
      'ip' => $this->getIp(),
      'active' => $this->isActive(),
      'reservation_comment' => $this->getComment(),
      'reservation_insert_time' => $this->getInsertTime(),
      'reservation_update_time' => $this->getUpdateTime(),
      'end_host' => $this->end_host->serialize(),
      'group' => $this->group->serialize(),
      'vlan_id' => (int) $this->getVlan(),
      'subnet' => $this->getNetwork(),
      'subnet_description' => $this->getSubnetDescription(),
    ];
  }
}
