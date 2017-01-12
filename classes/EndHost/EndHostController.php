<?php

namespace Dhcp\EndHost;

use Dhcp\EndHost\EndHostModel;
use Dhcp\Response;
use Dhcp\Validator;
use \Illuminate\Database\Query\Expression;
use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->ci->capsule;
        $this->r = new Response();
    }

    /*
     * Get all hosts
     * HTTP GET
     */
    public function get_host ($request, $response, $args) {
        // Log request info
        $this->ci->logger->addInfo("Full end host list");
        $hosts = EndHostModel::all();
        // Prepare API response
        $this->r->setData($hosts);
        $this->r->success();
        // Return response as JSON body
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Get host by ID
     * HTTP GET
     */
    public function get_host_by_id ($request, $response, $args) {
        if (!Validator::validateArgument($args, 'end_host_id', Validator::ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        // Log request info
        $this->ci->logger->addInfo("Rrequested end host #" . $args['end_host_id']);
        try {
            $endhost = EndHostModel::findOrFail($args['end_host_id']);
            $this->r->setData($endhost);
            $this->r->success();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->r->fail(404, "End host with ID#{$args['end_host_id']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Get host by MAC address
     * HTTP GET
     */
    public function get_host_by_mac ($request, $response, $args) {
        if (!Validator::validateArgument($args, 'mac', Validator::MAC)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid MAC");
            $this->r->fail(400, "Invalid MAC address");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        // Log request info
        $this->ci->logger->addInfo("Rrequested end with MAC: " . $args['mac']);
        // Replace all funny characters in mac address
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        $endhost = EndHostModel::where('mac', '=', intval($clean_mac, 16))->first();
        // Prepare API response
        if ($endhost) {
            $this->r->setData($endhost);
            $this->r->success();
        } else {
            $this->r->fail(404, "End host with MAC {$args['mac']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Search end host table for end host and match:
     * hostname, mac, description
     * to a given search pattern
     * HTTP GET
     */
    public function get_search_host ($request, $response, $args) {
        // Log request info
        $this->ci->logger->addInfo("Searching for host with pattern: " . $args['pattern']);
        $mac = preg_replace('/[^%0-9A-Fa-f]/i', '', $args['pattern']);
        $endhosts = EndHostModel::where('description', 'like', "%{$args['pattern']}%")
                                ->where('hostname', 'like', "%{$args['pattern']}%", 'or')
                                ->where(new Expression('HEX(mac)'), 'like', "%{$mac}%", 'or')->get();
        // If there's more than one, it's good
        if (sizeof($endhosts) >= 1) {
            $this->r->success();
            $this->r->setData($endhosts);
        } else {
            $this->r->fail(404, "No matches found");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    public function post_host ($request, $response, $args) {
        $this->ci->logger->addInfo("Adding new host with parameters: " . join(', ', $request->getParams()));

        $required_params = [
            ['hostname', Validator::REGEXP_HOSTNAME],
            ['mac', Validator::REGEXP_MAC],
            ['end_host_type_id', Validator::REGEXP_ID],
        ];
        $optional_params = [
            ['end_host_id', Validator::REGEXP_ID],
            ['description', null],
            ['production', Validator::REGEXP_ID],
        ];
        /*
         * Data array used for building the EndHostEntry object.
         * Parameters from Request are filtered and copied to this array.
         */
        $data = [];
        /*
         * Loop trough required parameters and check if
         * they exist and are matching the regexp defined above.
         * Generates error message if the value is missing or doesn't
         * match the regular expression defined for it
         */
        foreach ($required_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            } else {
                $this->r->fail(400, "Required parameter {$param[0]} missing or invalid.");
            }
        }

        /*
         * Loop through optional parameters and check if
         * they exist and are matching the regexp defined above.
         * No error message is generated if the parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ($optional_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            }
        }
        /*
         * Build EndHostEntry from data array and create new host
         */
        $endhost = new EndHostEntry($data);
        $mapper = new EndHostMapper($this->ci->db);
        if ($mapper->insertEndHost($endhost, $this->r)) {
            $this->ci->logger->addInfo("Created new end host with ID#" . $this->r->getData()['end_host_id']);
        } else {
            $this->ci->logger->addError("Failed adding new. Reason: " . join(', ', $this->r->getMessages()));
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    public function delete_host ($request, $response, $args) {
        $this->ci->logger->addInfo("Delete end host #" . $args['end_host_id']);
        $mapper = new EndHostMapper($this->ci->db, $this->r);
        $result = $mapper->deleteHost($args['end_host_type_id']);
        if ($result['success']) {
            if ($result['deleted_count']) {
                $http_code = 200;
            } else {
                $http_code = 404;
            }
        }
        return $response->withStatus($http_code)->withJson($result);
    }
}
