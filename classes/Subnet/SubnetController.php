<?php

namespace Dhcp\Subnet;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 7:15 PM
 */

use Dhcp\Subnet\SubnetModel;
use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;


class SubnetController {

    /*
     * Container interface
     */
    protected $ci;

    /*
     * Dhcp\Request object;
     */
    protected $r;

    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->r = new \Dhcp\Response();
        $this->ci->capsule;
    }

    /*
     * Get all subnets
     * HTTP GET
     */
    public function get_subnets ($request, $response, $args) {
        $subnets = SubnetModel::all();
        $this->r->setData($subnets);
        $this->r->success();
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Get subnet with specified ID
     * HTTP GET
     */
    public function get_subnet_by_id ($request, $response, $args) {
        if (!Validator::validateArgument($args, 'subnet_id', Validator::ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        try {
            $result = SubnetModel::findOrFail($args['subnet_id']);
            $this->r->success();
            $this->r->setData($result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->r->fail(404, "Subnet with ID #{$args['subnet_id']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Get subnet with VLAN ID
     * HTTP GET
     */
    public function get_subnet_by_vlan ($request, $response, $args) {
        if (!Validator::validateArgument($args, 'vlan_id', Validator::VLAN)) {
            $this->r->fail(400, 'Invalid VLAN ID.');
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        $result = SubnetModel::where('vlan', '=', $args['vlan_id'])->get();
        if ($result) {
            $this->r->success();
            $this->r->setData($result);
        } else {
            $this->r->fail(404, "Subnet with VLAN ID #{$args['vlan_id']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    public function get_subnet_by_address ($request, $response, $args) {
        $ip = $args['ip'];
        if (!Validator::validateIpAddress($ip)) {
            $this->r->fail();
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        try {
            $mapper = new SubnetMapper($this->ci->db);
            $results = $mapper->getSubnets(['ip' => $ip]);
            // Build an array of end hosts
            if (sizeof($results) == 1 and $results[0]->isValidAddress($ip)) {
                $this->r->success();
                $this->r->setData($results[0]->serialize());
            } else {
                $this->r->fail();
                $this->r->setCode(404);
                $this->r->addMessage("Subnet for address {$ip} not found.");
            }
        } catch (\InvalidArgumentException $e) {
            $this->r->fail();
            $this->r->addMessage($e->getMessage());
        }
        // Return response as JSON body
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    public function get_subnet_free_addresses ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $id = intval($args['subnet_id']);
        if (!Validator::validateId($id)) {
            $this->r->fail();
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        try {
            $mapper = new SubnetMapper($this->ci->db);
            try {
                $results = $mapper->getFreeAddresses($id);
                $this->r->success();
                $this->r->setData($results);
            } catch (\InvalidArgumentException $e) {
                $this->r->fail();
                $this->r->addMessage($e->getMessage());
                $this->r->setCode(404);
            }
        } catch (\InvalidArgumentException $e) {
            $this->r->fail();
            $this->r->addMessage($e->getMessage());
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    //TODO: new subnet
    public function post_subnet ($request, $response, $args) {

    }

    //TODO: update subnet
    public function put_subnet ($request, $response, $args) {

    }

    //TODO: delete subnet
    public function delete_subnet ($request, $response, $args) {

    }
}