<?php

namespace Dhcp\EndHost;

use Dhcp\EndHost\EndHostModel;
use Dhcp\Response;
use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->ci->capsule;
    }

    /*
     * Get all hosts
     * HTTP GET
     */
    public function get_host ($request, $response, $args) {
        // API response
        $r = new Response();
        // Log request info
        $this->ci->logger->addInfo("Full end host list");
        $hosts = EndHostModel::all();
        // Prepare API response
        $r->setData($hosts);
        $r->success();
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    /*
     * Get host by ID
     * HTTP GET
     */
    public function get_host_by_id ($request, $response, $args) {
        // API response
        $r = new Response();
        if (!Validator::validateArgument($args, 'end_host_id', Validator::ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $r->fail(400, "Invalid host ID");
            return $response->withStatus($r->getCode())->withJson($r);
        }
        // Log request info
        $this->ci->logger->addInfo("Rrequested end host #" . $args['end_host_id']);
        try {
            $endhost = EndHostModel::findOrFail($args['end_host_id']);
            $r->setData($endhost);
            $r->success();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $r->fail(404, "End host with ID#{$args['end_host_id']} not found.");
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_host_by_mac ($request, $response, $args) {
        // API response
        $r = new Response();
        if (!Validator::validateArgument($args, 'mac', Validator::MAC)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid MAC");
            $r->fail(400, "Invalid MAC address");
            return $response->withStatus($r->getCode())->withJson($r);
        }
        // Log request info
        $this->ci->logger->addInfo("Rrequested end with MAC: " . $args['mac']);
        // Instance mapper, replace all funny characters in mac address
        // and request end host with specific MAC
        $mapper = new EndHostMapper($this->ci->db);
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        $filter = ['mac' => intval($clean_mac, 16)];
        $endhost = $mapper->getEndHosts($filter);
        // Prepare API response
        // If there's one EndHost everything is good
        if (sizeof($endhost) == 1) {
            $r->setData($endhost[0]->serialize());
            $r->success();
        } else {
            $r->fail(404, "End host with MAC {$args['mac']} not found.");
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_search_host ($request, $response, $args) {
        // API response
        $r = new Response();
        // Log request info
        $this->ci->logger->addInfo("Searching for host with pattern: " . $args['pattern']);
        // Instance mapper and search for end host matching specific pattern
        $mapper = new EndHostMapper($this->ci->db);
        $filter = ['search' => '%' . $args['pattern'] . '%'];
        $endhosts = $mapper->getEndHosts($filter);
        // If there's more than one, it's good
        if (sizeof($endhosts) >= 1) {
            // Build array of end hosts
            $array = [];
            foreach ($endhosts as $endhost) {
                $array[] = $endhost->serialize();
            }
            $r->success();
            $r->setData($array);
        } else {
            $r->fail(404, "No matches found");
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function post_host ($request, $response, $args) {
        // API response
        $r = new Response();
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
                $r->fail(400, "Required parameter {$param[0]} missing or invalid.");
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
        if ($mapper->insertEndHost($endhost, $r)) {
            $this->ci->logger->addInfo("Created new end host with ID#" . $r->getData()['end_host_id']);
        } else {
            $this->ci->logger->addError("Failed adding new. Reason: " . join(', ', $r->getMessages()));
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    //TODO: \Dhcp\Response object
    public function delete_host ($request, $response, $args) {
        $r = new Response();
        $this->ci->logger->addInfo("Delete end host #" . $args['end_host_id']);
        $mapper = new EndHostMapper($this->ci->db, $r);
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
