<?php

namespace Dhcp\Reservation;

use Dhcp\Response;
use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class ReservationController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->r = new Response();
    }

    public function get_reservations ($request, $response, $args) {
        $this->ci->logger->addInfo("Reservation list");
        return $this->get_filtered_reservations($response, [], true, $args['mode'] == 'terse');
    }

    public function get_reservations_for_subnet ($request, $response, $args) {
        $this->ci->logger->addInfo("Reservation list for subnet #" . $args['subnet_id']);
        // Filter data
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $filter = ['subnet_id' => $args['subnet_id']];
        return $this->get_filtered_reservations($response, $filter, true, $args['mode'] == 'terse');
    }

    public function get_reservations_for_group ($request, $response, $args) {
        $this->ci->logger->addInfo("Reservation list for group #" . $args['group_id']);
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $filter = ['group_id' => $args['group_id']];
        return $this->get_filtered_reservations($response, $filter, true, $args['mode'] == 'terse');
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

    public function get_reservation_by_id ($request, $response, $args) {
        $this->ci->logger->addInfo('Request for reservation #' . $args['id']);
        if (!Validator::validateArgument($args, 'id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid reservation ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $filter = ['id' => $args['id']];
        return $this->get_filtered_reservations($response, $filter, false, $args['mode'] == 'terse');
    }

    public function get_reservation_by_mac ($request, $response, $args) {
        $this->ci->logger->addInfo('Request for reservation with MAC: ' . $args['mac']);
        if (!Validator::validateArgument($args, 'mac', Validator::REGEXP_MAC)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid MAC");
            $this->r->fail(400, "Invalid MAC address");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        $filter = ['mac' => intval($clean_mac, 16)];
        return $this->get_filtered_reservations($response, $filter, true, $args['mode'] == 'terse');
    }

    public function post_reservation ($request, $response, $args) {
    }

    public function put_reservation ($request, $response, $args) {
    }

    public function delete_reservation ($request, $response, $args) {
    }

    private function get_filtered_reservations ($response, $filter, $multiple_results = false, $terse = false) {
        $mapper = new ReservationMapper($this->ci->db);
        $reservations = $mapper->getReservations($filter, $terse);
        /*
         * For multiple results success is imminent :)
         * If there's no reservations we just return empty array
         * For single results, fail if not found with 404 http code
         */
        if ($multiple_results) {
            $array = [];
            foreach ( $reservations as $reservation ) {
                $array[] = $reservation->serialize();
            }
            $this->r->success();
            $this->r->setData($array);
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        } else {
            if (sizeof($reservations) == 1) {
                $this->r->success();
                $this->r->setData($reservations[0]->serialize());
            } else {
                $this->r->fail(404);
            }
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
    }
}
