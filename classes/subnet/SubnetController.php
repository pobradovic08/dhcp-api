<?php

namespace Dhcp\Subnet;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 7:15 PM
 */

use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;


class SubnetController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function get_subnets ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $this->ci->logger->addInfo("Full subnet list");
        try {
            $mapper = new SubnetMapper($this->ci->db);
            $results = $mapper->getSubnets([]);
            // Build an array of end hosts
            $array = [];
            foreach ( $results as $result ) {
                $array[] = $result->serialize();
            }
            // Prepare API response
            $r->setData($array);
            $r->success();
            // Return response as JSON body
        }catch (\InvalidArgumentException $e){
            $r->fail();
            $r->addMessage($e->getMessage());
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_by_id ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $id = intval($args['subnet_id']);
        if (!Validator::validateId($id)) {
            $r->fail();
        } else {
            $this->ci->logger->addInfo("Get subnet with ID #{$id}");
            try {
                $mapper = new SubnetMapper($this->ci->db);
                $results = $mapper->getSubnets(['subnet_id' => $id]);
                // Build an array of end hosts
                if (sizeof($results) == 1) {
                    $r->success();
                    $r->setData($results[0]->serialize());
                } else {
                    $r->fail();
                    $r->setCode(404);
                    $r->addMessage("Subnet with ID #{$id} not found.");
                }
            }catch (\InvalidArgumentException $e){
                $r->fail();
                $r->addMessage($e->getMessage());
            }
        }
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_by_vlan ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $id = intval($args['vlan_id']);
        if (!Validator::validateVlanId($id)) {
            $r->fail();
        } else {
            $this->ci->logger->addInfo("Get subnet with VLAN ID #{$id}");
            try {
                $mapper = new SubnetMapper($this->ci->db);
                $results = $mapper->getSubnets(['vlan_id' => $id]);
                // Build an array of end hosts
                if (sizeof($results) == 1) {
                    $r->success();
                    $r->setData($results[0]->serialize());
                } else {
                    $r->fail();
                    $r->setCode(404);
                    $r->addMessage("Subnet with VLAN ID #{$id} not found.");
                }
            } catch (\InvalidArgumentException $e) {
                $r->fail();
                $r->addMessage($e->getMessage());
            }
        }
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_by_address ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $ip = $args['ip'];
        if (!Validator::validateIpAddress($ip)) {
            $r->fail();
        } else {
            $this->ci->logger->addInfo("Get subnet which contains {$ip}");
            try {
                $mapper = new SubnetMapper($this->ci->db);
                $results = $mapper->getSubnets(['ip' => $ip]);
                // Build an array of end hosts
                if (sizeof($results) == 1 and $results[0]->isValidAddress($ip)) {
                    $r->success();
                    $r->setData($results[0]->serialize());
                } else {
                    $r->fail();
                    $r->setCode(404);
                    $r->addMessage("Subnet for address {$ip} not found.");
                }
            }catch (\InvalidArgumentException $e){
                $r->fail();
                $r->addMessage($e->getMessage());
            }
        }
        // Return response as JSON body
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_free_addresses ($request, $response, $args){
        $r = new \Dhcp\Response();
        $id = intval($args['subnet_id']);
        if (!Validator::validateId($id)) {
            $r->fail();
        } else {
            $this->ci->logger->addInfo("Get free addresses in subnet with ID #{$id}");
            try{
                $mapper = new SubnetMapper($this->ci->db);
                try {
                    $results = $mapper->getFreeAddresses($id);
                    $r->success();
                    $r->setData($results);
                }catch (\InvalidArgumentException $e){
                    $r->fail();
                    $r->addMessage($e->getMessage());
                    $r->setCode(404);
                }
            }catch (\InvalidArgumentException $e){
                $r->fail();
                $r->addMessage($e->getMessage());
            }
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function post_subnet ($request, $response, $args) {

    }

    public function put_subnet ($request, $response, $args) {

    }

    public function delete_subnet ($request, $response, $args) {

    }
}