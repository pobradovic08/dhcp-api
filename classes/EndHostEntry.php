<?php

class EndHostEntry {

  private $id;
  private $description;
  private $hostname;
  private $mac;
  private $type_id;
  private $type_description;
  private $insert_time;
  private $update_time;

  public function __construct(array $data) {
    if(isset($data['end_host_id'])){
      $this->id = $data['end_host_id'];
    }
    $this->hostname = $data['hostname'];
    $this->description  = $data['description'];
    $this->mac = $data['mac'];
    $this->type_id = $data['end_host_type_id'];
    $this->type_description = $data['end_host_type_description'];
    $this->insert_time = $data['insert_time'];
    $this->update_time = $data['update_time'];
  }

  public function getId() {
    return (int) $this->id;
  }

  public function getHostname() {
    return $this->hostname;
  }

  public function getDescription() {
    return $this->description;
  }

  public function getMac() {
    return $this->mac;
  }

  public function getTypeId() {
    return (int) $this->type_id;
  }

  public function getTypeDescription() {
    return $this->type_description;
  }

  public function getInsertTime() {
    return (int) $this->insert_time;
  }

  public function getUpdateTime() {
    return (int) $this->update_time;
  }

  public function serialize () {
    return [
      'end_host_id' => $this->getId(),
      'hostname' => $this->getHostname(),
      'description' => $this->getDescription(),
      'mac' => $this->getMac(),
      'type_id' => $this->getTypeId(),
      'type_description' => $this->getTypeDescription(),
      'insert_time' => $this->getInsertTime(),
      'update_time' => $this->getUpdateTime()
    ];
  }
}
