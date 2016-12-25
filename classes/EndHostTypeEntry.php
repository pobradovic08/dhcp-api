<?php

class EndHostTypeEntry {

  private $id;
  private $description;

  public function __construct(array $data) {
    if(isset($data['end_host_type_id'])){
      $this->id = $data['end_host_type_id'];
      $this->description  = $data['end_host_type_description'];
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getDescription() {
    return $this->description;
  }

  public function serialize () {
    return [
      'end_host_type_id' => $this->getId(),
      'end_host_type_description' => $this->getDescription(),    
    ];
  }
}
