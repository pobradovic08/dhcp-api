<?php

class EndHostEntry {

  private $id;
  private $description;
  private $hostname;
  private $mac;
  private $type_id;
  private $type_description;
  private $production;
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
    $this->production = $data['production'] || 0;
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
    return strtolower(join('.', str_split($this->mac, 4)));
  }

  public function getTypeId() {
    return (int) $this->type_id;
  }

  public function getTypeDescription() {
    return $this->type_description;
  }

  public function isProduction() {
    return $this->production;
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
      'production' => $this->isProduction(),
      'insert_time' => $this->getInsertTime(),
      'update_time' => $this->getUpdateTime()
    ];
  }
  
  public function db_data () {
    return [
      'hostname' => $this->hostname,
      'description' => $this->description,
      'mac' => hexdec($this->mac),
      'end_host_type_id' => $this->type_id,
      'production' => $this->production
    ];
  }
}
