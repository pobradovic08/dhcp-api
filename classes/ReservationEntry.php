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
  protected $end_host;
  // GroupEntry object
  protected $group;
  // Subnet data
  protected $subnet;

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
    if($data['subnet'] instanceof SubnetEntry){
      $this->subnet = $data['subnet'];
    }
    $this->ip = $data['ip'];
    $this->active = (bool) $data['active'];
    $this->comment = $data['comment'];
    $this->insert_time = (int) $data['insert_time'];
    $this->update_time = (int) $data['update_time'];
  }
  
  public function getId() {
    return $this->id;
  }

  public function getIp() {
    return $this->ip;
  }
  
  public function isActive() {
    return $this->active;
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
      'subnet' => $this->subnet->serialize(),
    ];
  }
}
