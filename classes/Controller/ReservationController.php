<?php

namespace Dhcp\Controller;

use Dhcp\Model\EndHostModel;
use Dhcp\Model\GroupModel;
use Dhcp\Model\ReservationModel;
use Dhcp\Model\SubnetModel;
use Dhcp\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ReservationController
 *
 * @author  Pavle Obradovic <pobradovic08@gmail.com>
 */
class ReservationController extends BaseController {

    /*
     * If request mode is set to TERSE, don't fetch
     * linked Models, just their IDs
     */
    const TERSE = 'terse';

    /**
     * Get all reservations
     * In terse mode linked EndHost, Group and Subnet objects are not fetched.
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Optional 'mode' argument
     * @return ResponseInterface
     */
    public function get_reservations (ServerRequestInterface $request, ResponseInterface $response, $args) {
        if ($args['mode'] == self::TERSE) {
            $reservations = ReservationModel::all();
        } else {
            $reservations = ReservationModel::with('end_host', 'group.subnet')->get();
        }
        $this->r->success();
        $this->r->setData($reservations);
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * Get multiple reservations that belong to specific subnet
     * In terse mode linked EndHost object is not fetched, just Subnet and Group
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'subnet_id'. Optional 'mode' argument
     * @return ResponseInterface
     */
    public function get_reservations_for_subnet (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'subnet_id' route argument
         */
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        try {
            //TODO: Check if terse is really terse and endhost has no type attribute
            if ($args['mode'] == self::TERSE) {
                $reservations = SubnetModel::with('groups.reservations')->findOrFail($args['subnet_id']);
            } else {
                $reservations = SubnetModel::with('groups.reservations.end_host')->findOrFail($args['subnet_id']);
            }
            $this->r->success();
            $this->r->setData($reservations);
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "Subnet #{$args['subnet_id']} not found");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * Get multiple reservations that belong to a specific Group
     * In terse mode linked EndHost object is not fetched, just Subnet and Group
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_reservations_for_group (ServerRequestInterface $request, ResponseInterface $response, $args) {
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        try {
            if ($args['mode'] == self::TERSE) {
                $reservations = GroupModel::with('reservations')->findOrFail($args['group_id']);
            } else {
                $reservations = GroupModel::with('subnet', 'reservations.end_host')->findOrFail($args['group_id']);
            }
            $this->r->success();
            $this->r->setData($reservations);
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "Group #{$args['group_id']} not found");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * Get reservation for IP address
     * In terse mode linked EndHost, Subnet and Group objects are not fetched
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_reservation_by_ip (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'ip' route argument
         */
        if (!Validator::validateArgument($args, 'ip', Validator::IP)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid IP");
            $this->r->fail(400, "Invalid IP Address");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $decip = ip2long($args['ip']);
        //TODO: Check if terse is really terse (has no endhost type data)
        if ($args['mode'] == self::TERSE) {
            $reservation = ReservationModel::where('ip', '=', $decip)->first();
        } else {
            $reservation = ReservationModel::with('end_host', 'group.subnet')->where('ip', '=', $decip)->first();
        }
        if ($reservation) {
            $this->r->success();
            $this->r->setData($reservation);
        } else {
            $this->r->fail(404, "Reservation for IP {$args['ip']} not found.");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * Get reservation with ID
     * In terse mode linked EndHost, Subnet and Group objects are not fetched
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_reservation_by_id (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'id' route argument
         */
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
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "Reservation #{$args['id']} not found.");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * Get multiple reservations for a MAC address
     * In terse mode linked EndHost, Subnet and Group objects are not fetched
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_reservation_by_mac (ServerRequestInterface $request, ResponseInterface $response, $args) {
        if (!Validator::validateArgument($args, 'mac', Validator::REGEXP_MAC)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid MAC");
            $this->r->fail(400, "Invalid MAC address");
            return $response->withJson($this->r, $this->r->getCode());
        }
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        if ($args['mode'] == self::TERSE) {
            $endhost = EndHostModel::with('reservations')
                                   ->where('mac', '=', intval($clean_mac, 16))
                                   ->first();
        } else {
            /*
             * We need 'reservations.end_host' even if we do this on EndHostModel
             * because we later just get the 'reservations' attribute
             */
            $endhost = EndHostModel::with('reservations.end_host', 'reservations.group.subnet')
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

    /**
     * Create new reservation with unique IP
     * Required parameters:
     *  - Endhost ID
     *  - Group ID
     *  - IP address
     * Optional parameters:
     *  - Active status
     *  - Comment
     *
     * Additional checks:
     *  - Group and host exist
     *  - IP belongs to the subnet
     *  - There are no other reservations for that host in the subnet
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface Returns new Reservation
     */
    public function post_reservation (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $required_arguments = [
            ['end_host_id', Validator::ID],
            ['group_id', Validator::ID],
            ['ip', Validator::IP],
        ];

        $optional_arguments = [
            ['active', Validator::REGEXP_BOOL],
            ['comment', Validator::DESCRIPTION],
        ];
        /*
         * Parameters from Request are filtered and copied to this array.
         */
        $data = [];
        /*
         * Generates error message if the value is missing or doesn't
         * match the regular expression defined for it
         */
        foreach ($required_arguments as $arg) {
            if (Validator::validateArgument($request->getParams(), $arg[0], $arg[1])) {
                $data[$arg[0]] = $request->getParam($arg[0]);
            } else {
                $this->r->fail(400, "Required parameter {$arg[0]} missing or invalid.");
            }
        }
        /*
         * No error message is generated if the parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ($optional_arguments as $arg) {
            if (Validator::validateArgument($request->getParams(), $arg[0], $arg[1])) {
                $data[$arg[0]] = $request->getParam($arg[0]);
            }
        }
        /*
         * We need to check that:
         * 0. There are no reservations for that IP
         * 1. Group and host exist
         * 2. IP belongs to the subnet
         * 3. There are no other reservations for that host in the subnet
         */
        try {
            /*
             * Check if the reservation is unique
             */
            $reservation = ReservationModel::with('end_host')->where('ip', '=', ip2long($data['ip']))->first();
            if ($reservation) {
                $this->r->fail(400, "IP {$data['ip']} already reserved (#{$reservation->reservation_id})" .
                                    " for " . $reservation->end_host->hostname);
                return $response->withJson($this->r, $this->r->getCode());
            }
            /*
             * Get group and subnet. This also checks if the group exists.
             * Get host. This checks if the host exists.
             */
            $group = GroupModel::findOrFail($data['group_id']);
            $subnet = SubnetModel::findOrFail($group->subnet_id);
            $endhost = EndHostModel::findOrFail($data['end_host_id']);
            /*
             * Check if IP belongs to the subnet
             */
            if (!$subnet->validIp($data['ip'])) {
                $this->r->fail(400,
                               "IP ${data['ip']} is not a valid host address in" .
                               "{$subnet->network}/{$subnet->network_mask}");
                return $response->withJson($this->r, $this->r->getCode());
            }
            /*
             * Check if there are no other reservations for that host in the subnet
             * Count reservation entries that have given end_host_id AND are bound to
             * one of the groups that belong to a given group's parent subnet
             */
            $count = $this->ci->capsule->table('reservations')->select('*')
                                       ->join('groups', 'reservations.group_id', 'groups.group_id')
                                       ->join('end_hosts', 'reservations.end_host_id', 'end_hosts.end_host_id')
                                       ->where('groups.subnet_id', '=', $subnet->subnet_id)
                                       ->where('reservations.end_host_id', '=', $endhost->end_host_id)
                                       ->count();
            if ($count) {
                $this->r->fail(400,
                               "Host {$endhost->hostname} already has reservation in {$subnet->network}/{$subnet->cidr()}");
                return $response->withJson($this->r, $this->r->getCode());
            }
            /*
             * We've come so far... create new reservation
             */
            $reservation = new ReservationModel($data);
            if ($reservation->save()) {
                $this->r->success("IP {$data['ip']} reserved for {$endhost->hostname}");
                $this->r->setData($reservation);
            } else {
                $this->r->fail(500, 'Creating reservation unsuccessful.');
            }
        } catch (ModelNotFoundException $e) {
            $this->r->fail(400, "Group #{$data['group_id']} or host #{$data['end_host_id']} doesn't exist.");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    //TODO: Update reservation
    public function put_reservation (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'id' route argument
         */
        if (!Validator::validateArgument($args, 'id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid reservation ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        /*
         * Editable fields
         */
        $optional_arguments = [
            ['end_host_id', Validator::ID],
            ['group_id', Validator::ID],
            ['ip', Validator::IP],
            ['active', Validator::REGEXP_BOOL],
            ['comment', Validator::DESCRIPTION],
        ];

        try {

            /*
             * Get existing reservation
             */
            $reservation = ReservationModel::findOrFail($args['id']);

            /*
             * Loop through optional parameters and check if
             * they exist and are matching the regexp defined above.
             * No error message is generated if the parameter is missing.
             * If the value is not matching the regexp, parameter is not
             * added to data array.
             */
            $data = [];
            foreach ($optional_arguments as $arg) {
                if (Validator::validateArgument($request->getParams(), $arg[0], $arg[1])) {
                    $data[$arg[0]] = $request->getParam($arg[0]);
                }
            }
            /*
             * Make changes to reservation
             */
            $reservation->fill($data);
            /*
             * Check if reservation is valid
             */
            //TODO: maybe this should be a ReservationModel method
            if ($reservation->valid()) {
                /*
                * Save reservation to database
                */
                if($reservation->save()) {
                    $this->r->success("Reservation updated.");
                    $this->r->setData($reservation);
                }
            }

        } catch (Illuminate\Database\Exception\ModelNotFoundException $e) {
            $this->r->fail(404, "Reservation #{$args['id']} not found.");
        }

        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * Delete reservations by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should have 'id' key
     * @return ResponseInterface
     */
    public function delete_reservation (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'id' route argument
         */
        if (!Validator::validateArgument($args, 'id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid reservation ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        /*
         * Fetch and delete reservation
         */
        try {
            $reservation = ReservationModel::findOrFail($args['id']);
            if ($reservation->delete()) {
                $this->r->success("Reservation #{$args['id']} deleted");
                $this->ci->logger->addInfo('Reservation #' . $args['id'] . ' deleted.');
            } else {
                $this->r->fail(500, "Couldn't delete reservation");
                $this->ci->logger->addError('Deleting reservation #' . $args['id'] . " failed");
            }
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "Reservation #{$args['id']} not found.");
        }
        return $response->withJson($this->r, $this->r->getCode());
    }
}
