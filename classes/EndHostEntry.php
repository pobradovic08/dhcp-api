<?php

class EndHostEntry {

  private $id;
  private $description;
  private $hostname;
  private $mac;
  // EndHostType object
  private $end_host_type;
  private $type_id;
  private $type_description;
  private $production;
  private $insert_time;
  private $update_time;

  public function __construct(array $data) {
    if(isset($data['end_host_id'])){
      $this->id = (int) $data['end_host_id'];
    }
    if($data['end_host_type'] instanceof EndHostTypeEntry){
      $this->end_host_type = $data['end_host_type'];
    }
    $this->hostname = $data['hostname'];
    $this->description  = $data['end_host_description'];
    $this->mac = $data['mac'];
    $this->type_id = (int) $data['end_host_type_id'];
    $this->type_description = $data['end_host_type_description'];
    $this->production = (bool) $data['production'] || 0;
    $this->insert_time = (int) $data['end_host_insert_time'];
    $this->update_time = (int) $data['end_host_update_time'];
  }

  public function getId() {
    return $this->id;
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

  public function getType() {
    return $this->end_host_type;
  }

  public function isProduction() {
    return $this->production;
  }

  public function getInsertTime() {
    return $this->insert_time;
  }

  public function getUpdateTime() {
    return $this->update_time;
  }

  public function serialize () {
    return [
      'end_host_id' => $this->getId(),
      'hostname' => $this->getHostname(),
      'end_host_description' => $this->getDescription(),
      'mac' => $this->getMac(),
      'end_host_type' => $this->end_host_type->serialize(),
      'production' => $this->isProduction(),
      'end_host_insert_time' => $this->getInsertTime(),
      'end_host_update_time' => $this->getUpdateTime()
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
