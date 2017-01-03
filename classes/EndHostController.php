<?php

use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function get_host ($request, $response, $args) {
        // API response
        $r = new DhcpResponse();
        // Log request info
        $this->ci->logger->addInfo("Full end host list");
        // Instance mapper and request all end hosts (empty filter)
        $mapper = new EndHostMapper($this->ci->db);
        $endhosts = $mapper->getEndHosts(array ());
        // Build an array of end hosts
        $array = [];
        foreach ( $endhosts as $endhost ) {
            $array[] = $endhost->serialize();
        }
        // Prepare API response
        $r->setData($array);
        $r->success();
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_host_by_id ($request, $response, $args) {
        // API response
        $r = new DhcpResponse();
        // Log request info
        $this->ci->logger->addInfo("Rrequested end host #" . $args['end_host_id']);
        // Instance mapper and request end host with specific ID
        $mapper = new EndHostMapper($this->ci->db);
        $filter = array ('end_host_id' => $args['end_host_id']);
        $endhost = $mapper->getEndHosts($filter);
        // Prepare API response
        // If there's one endhost everything is good
        if (sizeof($endhost) == 1) {
            $r->setData($endhost[0]->serialize());
            $r->success();
        } else {
            $r->fail();
            $r->setCode(404);
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_host_by_mac ($request, $response, $args) {
        // API response
        $r = new DhcpResponse();
        // Log request info
        $this->ci->logger->addInfo("Rrequested end with MAC: " . $args['mac']);
        // Instance mapper, replace all funny characters in mac address
        // and request end host with specific MAC
        $mapper = new EndHostMapper($this->ci->db);
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        $filter = array ('mac' => intval($clean_mac, 16));
        $endhost = $mapper->getEndHosts($filter);
        // Prepare API response
        // If there's one endhost everything is good
        if (sizeof($endhost) == 1) {
            $r->setData($endhost[0]->serialize());
            $r->success();
        } else {
            $r->fail();
            $r->setCode(404);
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_search_host ($request, $response, $args) {
        // API response
        $r = new DhcpResponse();
        // Log request info
        $this->ci->logger->addInfo("Searching for host with pattern: " . $args['pattern']);
        // Instance mapper and search for end host matching specific pattern
        $mapper = new EndHostMapper($this->ci->db);
        $filter = array ('search' => '%' . $args['pattern'] . '%');
        $endhosts = $mapper->getEndHosts($filter);
        // If there's more than one, it's good
        if (sizeof($endhosts) >= 1) {
            // Build array of end hosts
            $array = [];
            foreach ( $endhosts as $endhost ) {
                $array[] = $endhost->serialize();
            }
            $r->success();
            $r->setData($array);
        } else {
            $r->fail();
            $r->setCode(404);
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function post_host ($request, $response, $args) {
        $required_params = array (
            array (
                'name' => 'hostname',
                'filter' => FILTER_SANITIZE_STRING,
                'regexp' => '/[a-zA-Z0-9-]+/'
            ),
            array (
                'name' => 'mac',
                'filter' => FILTER_SANITIZE_STRING,
                'regexp' => '/^(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})$/'
            ),
            array (
                'name' => 'end_host_type_id',
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'regexp' => '/[0-9]+/'
            )
        );
        $optional_params = array (
            array (
                'name' => 'end_host_id',
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'regexp' => '/[0-9]+/'
            ),
            array (
                'name' => 'description',
                'filter' => FILTER_SANITIZE_STRING,
                'regexp' => '/.*/'
            ),
            array (
                'name' => 'production',
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'regexp' => '/[01]/'
            )
        );
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
        foreach ( $required_params as $param ) {
            // No parameter defined, generate error message
            if (!$request->getParam($param['name'])) {
                return $response->withStatus(400)->withJson(array ('message' => "Required parameter '" . $param['name'] . "' missing"));
            } else {
                // Filter parameter and add it to data array if it matches the regexp
                $filtered_value = filter_var($request->getParam($param['name']), $param['filter']);
                if (preg_match($param['regexp'], $filtered_value)) {
                    $data[$param['name']] = $filtered_value;
                    // Filtered parameter doesn't match regexp, generate error message
                } else {
                    return $response->withStatus(400)->withJson(array ('message' => "Required parameter '" . $param['name'] . "' invalid"));
                }
            }
        }

        /*
         * Loop through optional parameters and check if
         * they exist and are matching the regexp defined above.
         * No error message is generated if the parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ( $optional_params as $param ) {
            // Filter parameter and add it to data array if it matches the regexp
            $filtered_value = filter_var($request->getParam($param['name']), $param['filter']);
            if (preg_match($param['regexp'], $filtered_value)) {
                $data[$param['name']] = $filtered_value;
            }
        }
        /*
         * Build EndHostEntry from data array and create new host
         */
        $endhost = new EndHostEntry($data);
        $mapper = new EndHostMapper($this->ci->db);
        $result = $mapper->insertEndHost($endhost);
        if ($result['success']) {
            $this->ci->logger->addInfo($result['message']);
            return $response->withStatus(200)->withJson($result);
        } else {
            return $response->withStatus(400)->withJson($result);
        }
    }

    public function delete_host ($request, $response, $args) {
        $this->ci->logger->addInfo("Delete end host type #" . $args['end_host_type_id']);
        $mapper = new EndHostTypeMapper($this->ci->db);
        $result = $mapper->deleteType($args['end_host_type_id']);
        $http_code = 400;
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
