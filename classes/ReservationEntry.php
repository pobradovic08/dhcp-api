<?php

class ReservationEntry {

  // Reservation data
  protected $id;
  protected $mac;
  protected $ip;
  protected $hostname;
  protected $comment;
  protected $insert_time;
  protected $update_time;
  // Group data
  protected $group_name;
  protected $group_description;
  // Subnet data
  protected $subnet_id;
  protected $vlan;
  protected $network;
  protected $network_mask;
  protected $subnet_description;

  public function __construct(array $data){
    if(isset($data['reservation_id'])){
      $this->id = $data['reservation_id'];
    }
    $this->mac = $data['mac'];
    $this->ip = $data['ip'];
    $this->hostname = $data['hostname'];
    $this->comment = $data['comment'];
    $this->insert_time = $data['insert_time'];
    $this->update_time = $data['update_time'];

    $this->group_name = $data['group_name'];
    $this->group_description = $data['group_description'];

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
    return $this->mac;
  }

  public function getIp() {
    return $this->ip;
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

  public function getGroupName() {
    return $this->group_name;
  }

  public function getGroupDescription() {
    return $this->group_description;
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
      'id' => $this->getId(),
      'mac' => $this->getMac(),
      'ip' => $this->getIp(),
      'hostname' => $this->getHostname(),
      'comment' => $this->getComment(),
      'insert_time' => $this->getInsertTime(),
      'update_time' => $this->getUpdateTime(),
      'group_name' => $this->getGroupName(),
      'group_description' => $this->getGroupDescription(),
      'vlan' => $this->getVlan(),
      'network' => $this->getNetwork(),
      'subnet_description' => $this->getSubnetDescription(),
    ];
  }
}