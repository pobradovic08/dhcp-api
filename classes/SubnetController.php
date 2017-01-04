<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 7:15 PM
 */

use \Interop\Container\ContainerInterface as ContainerInterface;


class SubnetController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function get_subnets ($request, $response, $args) {
        $r = new DhcpResponse();
        $this->ci->logger->addInfo("Full subnet list");
        $mapper = new SubnetMapper($this->ci->db);
        $results = $mapper->getSubnets(array ());
        // Build an array of end hosts
        $array = [];
        foreach ($results as $result) {
            $array[] = $result->serialize();
        }
        // Prepare API response
        $r->setData($array);
        $r->success();
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_by_id ($request, $response, $args) {
        $r = new DhcpResponse();
        $id = intval($args['subnet_id']);
        if (!Validator::validateId($id)) {
            $r->fail();
        } else {
            $this->ci->logger->addInfo("Get subnet with ID #{$id}");
            $mapper = new SubnetMapper($this->ci->db);
            $results = $mapper->getSubnets(array ('subnet_id' => $id));
            // Build an array of end hosts
            if (sizeof($results) == 1) {
                $r->success();
                $r->setData($results[0]->serialize());
            } else {
                $r->fail();
                $r->setCode(404);
                $r->addMessage("Subnet with ID #{$id} not found.");
            }
        }
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_by_address ($request, $response, $args) {
        $r = new DhcpResponse();
        $ip = $args['ip'];
        if (!Validator::validateIpAddress($ip)) {
            $r->fail();
        } else {
            $this->ci->logger->addInfo("Get subnet which contains {$ip}");
            $mapper = new SubnetMapper($this->ci->db);
            $results = $mapper->getSubnets(array ('ip' => $ip));
            // Build an array of end hosts
            if (sizeof($results) == 1 and $results[0]->isValidAddress($ip)) {
                $r->success();
                $r->setData($results[0]->serialize());
            } else {
                $r->fail();
                $r->setCode(404);
                $r->addMessage("Subnet for address {$ip} not found.");
            }
        }
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function post_subnet ($request, $response, $args) {

    }

    public function put_subnet ($request, $response, $args) {

    }

    public function delete_subnet ($request, $response, $args) {

    }
}