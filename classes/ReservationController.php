<?php

use \Interop\Container\ContainerInterface as ContainerInterface;

class ReservationController {
  protected $ci;
  //Constructor
  public function __construct(ContainerInterface $ci) {
    $this->ci = $ci;
  }

  public function get_reservations ($request, $response, $args) {
    $this->ci->logger->addInfo("Reservation list");
    $mapper = new ReservationMapper($this->ci->db);
    $reservations = $mapper->getReservations(array());
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);
  }

  public function get_reservations_for_subnet ($request, $response, $args){
    $this->ci->logger->addInfo("Reservation list for subnet #" . $args['subnet_id']);
    $mapper = new ReservationMapper($this->ci->db);
    // Filter data
    $filter = array('subnet_id' => $args['subnet_id']);
    // Optional group_id argument
    if(array_key_exists('group_id', $args)){
      $filter['group_id'] = $args['group_id'];
    }
    $reservations = $mapper->getReservations($filter);
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);  
  }

  public function get_reservations_for_group ($request, $response, $args){
    $this->ci->logger->addInfo("Reservation list for subnet #" . $args['subnet_id']);
    $mapper = new ReservationMapper($this->ci->db);
    // Filter data
    $filter = array('group_id' => $args['group_id']);

    $reservations = $mapper->getReservations($filter);
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);
  }

  public function get_reservation_by_ip ($request, $response, $args){
    $this->ci->logger->addInfo('Request for reservation with IP: ' . $args['ip']);
    $filter = array('ip' => $args['ip']);

    $mapper = new ReservationMapper($this->ci->db);
    $reservation = $mapper->getReservations($filter);
    if(sizeof($reservation) == 1){
      return $response->withStatus(200)->withJson($reservation[0]->serialize());
    }else{
      return $response->withStatus(404)->withJson();
    }
  }

  public function get_reservation_by_id ($request, $response, $args){
    $this->ci->logger->addInfo('Request for reservation #' . $args['id']);
    $filter = array('id' => $args['id']);

    $mapper = new ReservationMapper($this->ci->db);
    $reservation = $mapper->getReservations($filter);
    if(sizeof($reservation) == 1){
      return $response->withStatus(200)->withJson($reservation[0]->serialize());
    }else{
      return $response->withStatus(404)->withJson();
    }
  }

  public function get_reservation_by_mac ($request, $response, $args){
    $this->ci->logger->addInfo('Request for reservation with MAC: ' . $args['mac']);

    $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
    $filter = array('mac' => intval($clean_mac, 16));

    $mapper = new ReservationMapper($this->ci->db);
    $reservations = $mapper->getReservations($filter);
    $array = [];
    foreach($reservations as $reservation){
      $array[] = $reservation->serialize();
    }
    return $response->withStatus(200)->withJson($array);  
  }

  public function post_reservation ($request, $response, $args){
  }

  public function put_reservation ($request, $response, $args){
  }

  public function delete_reservation ($request, $response, $args){
  }
}
