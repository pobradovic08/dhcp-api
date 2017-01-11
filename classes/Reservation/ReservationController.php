<?php

namespace Dhcp\Reservation;

use Dhcp\Reservation\ReservationModel;
use Dhcp\Response;
use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class ReservationController {

    const TERSE = 'terse';
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->ci->capsule;
        $this->r = new Response();
    }

    /*
     * Get all reservations
     * HTTP GET
     */
    public function get_reservations ($request, $response, $args) {
        $this->ci->logger->addInfo("Reservation list");
        if ($args['mode'] == self::TERSE) {
            $reservations = ReservationModel::all();
        } else {
            $reservations = ReservationModel::with('end_host', 'group.subnet')->get();
        }
        $this->r->success();
        $this->r->setData($reservations);
        return $response->withJson($this->r, $this->r->getCode());
    }

    /*
     * Get reservations for a subnet
     * HTTP GET
     */
    public function get_reservations_for_subnet ($request, $response, $args) {
        $this->ci->logger->addInfo("Reservation list for subnet #" . $args['subnet_id']);
        // Filter data
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        try {
            if ($args['mode'] == self::TERSE) {
                $reservations = \Dhcp\Subnet\SubnetModel::with('groups.reservations')->findOrFail($args['subnet_id']);
            } else {
                $reservations = \Dhcp\Subnet\SubnetModel::with('groups.reservations.end_host')->findOrFail($args['subnet_id']);
            }
            $this->r->success();
            $this->r->setData($reservations);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->r->fail(404, "Subnet #{$args['subnet_id']} not found");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /*
     * Get reservations for a specific group and subnet
     * HTTP GET
     */
    public function get_reservations_for_group ($request, $response, $args) {
        $this->ci->logger->addInfo("Reservation list for group #" . $args['group_id']);
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        try {
            if ($args['mode'] == self::TERSE) {
                $reservations = \Dhcp\Group\GroupModel::with('reservations')->findOrFail($args['group_id']);
            } else {
                $reservations = \Dhcp\Group\GroupModel::with('subnet', 'reservations.end_host')->findOrFail($args['group_id']);
            }
            $this->r->success();
            $this->r->setData($reservations);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->r->fail(404, "Group #{$args['group_id']} not found");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    public function get_reservation_by_ip ($request, $response, $args) {
        $this->ci->logger->addInfo('Request for reservation with IP: ' . $args['ip']);
        if (!Validator::validateArgument($args, 'ip', Validator::IP)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid IP");
            $this->r->fail(400, "Invalid IP Address");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $filter = ['ip' => $args['ip']];
        return $this->get_filtered_reservations($response, $filter, false, $args['mode'] == 'terse');
    }

    /*
     * Get a reservation by ID
     * HTTP GET
     */
    public function get_reservation_by_id ($request, $response, $args) {
        $this->ci->logger->addInfo('Request for reservation #' . $args['id']);
        if (!Validator::validateArgument($args, 'id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid reservation ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        try {
            if ($args['mode'] == self::TERSE) {
                $reservation = ReservationModel::findOrFail($args['id']);
            } else {
                $reservation = ReservationModel::with('end_host', 'group.subnet')->findOrFail($args['id']);
            }
            $this->r->success();
            $this->r->setData($reservation);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->r->fail(404, "Reservation #{$args['id']} not found.");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /*
     * Get a reservations for a MAC address
     * HTTP GET
     */
    public function get_reservation_by_mac ($request, $response, $args) {
        $this->ci->logger->addInfo('Request for reservation with MAC: ' . $args['mac']);
        if (!Validator::validateArgument($args, 'mac', Validator::REGEXP_MAC)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid MAC");
            $this->r->fail(400, "Invalid MAC address");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        $filter = ['mac' => intval($clean_mac, 16)];
        if ($args['mode'] == self::TERSE) {
            $endhost = \Dhcp\EndHost\EndHostModel::with('reservations')
                                                 ->where('mac', '=', intval($clean_mac, 16))
                                                 ->first();
        } else {
            $endhost = \Dhcp\EndHost\EndHostModel::with('reservations.end_host', 'reservations.group.subnet')
                                                 ->where('mac', '=', intval($clean_mac, 16))
                                                 ->first();
        }
        if ($endhost) {
            $reservation = $endhost->reservations;
            $this->r->success();
            $this->r->setData($reservation);
        } else {
            $this->r->fail(404, "Reservation for MAC {$args['mac']} not found.");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    public function post_reservation ($request, $response, $args) {
    }

    public function put_reservation ($request, $response, $args) {
    }

    public function delete_reservation ($request, $response, $args) {
    }
}
