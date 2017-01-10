<?php

namespace Dhcp\Group;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 11:20 PM
 */

use Dhcp\Group\GroupModel;
use Dhcp\Response;
use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class GroupController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->ci->capsule;
        $this->r = new Response();
    }

    /*
     * Get list of groups for specific subnet ID
     * HTTP GET
     */
    public function get_groups ($request, $response, $args) {
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        $subnet_id = intval($args['subnet_id']);
        $this->ci->logger->addInfo("Full group list for subnet ID #{$subnet_id}");
        $groups = GroupModel::where('subnet_id', '=', $subnet_id)->without('subnets')->get();
        if (!$groups->isEmpty()) {
            $this->r->success();
            $this->r->setData($groups);
        } else {
            $this->r->fail(404, "Subnet #$subnet_id not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Get group with specific ID
     * HTTP GET
     */
    public function get_group_by_id ($request, $response, $args) {
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        $this->ci->logger->addInfo("Get group with ID #{$args['group_id']}");
        try {
            $group = GroupModel::findOrFail($args['group_id']);
            if ($group->subnet_id == $args['subnet_id']) {
                $this->r->success();
                $this->r->setData($group);
            } else {
                $this->r->fail(404, "Group with ID#{$args['group_id']} doesn't belong to subnet #{$args['subnet_id']}");
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->r->fail(404, 'No group with ID #' . $args['group_id']);
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    //TODO: POST group
    public function post_group ($request, $response, $args) {

    }

    //TODO: PUT group
    public function put_group ($request, $response, $args) {

    }

    //TODO: DELETE group
    public function delete_group ($request, $response, $args) {

    }
}