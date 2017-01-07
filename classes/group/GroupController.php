<?php

namespace Dhcp\Group;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 11:20 PM
 */

use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class GroupController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
    }

    /*
     * Get list of groups for specific subnet ID
     */
    public function get_groups ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $subnet_id = isset($args['subnet_id']) ? intval($args['subnet_id']) : null;
        if(!Validator::validateId($subnet_id)){
            $r->fail();
            $r->addMessage("Invalid subnet ID");
        }else {
            $this->ci->logger->addInfo("Full group list for subnet ID #{$subnet_id}");
            try {
                $mapper = new GroupMapper($this->ci->db);
                $groups = $mapper->getGroups(['subnet_id' => $subnet_id]);
                $array = [];
                foreach ($groups as $group) {
                    $array[] = $group->serialize();
                }
                $r->success();
                $r->setData($array);
            } catch (\InvalidArgumentException $e) {
                $r->fail();
                $r->addMessage($e->getMessage());
            }
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    /*
     * Get group with specific ID
     */
    public function get_group_by_id ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $group_id = isset($args['group_id']) ? intval($args['group_id']) : null;
        $subnet_id = isset($args['subnet_id']) ? intval($args['subnet_id']) : null;
        if(!Validator::validateId($group_id) or !Validator::validateId($subnet_id)){
            $r->fail();
            $r->addMessage("Invalid subnet or group ID");
        }else {
            $this->ci->logger->addInfo("Get group with ID #{$group_id}");
            try {
                $mapper = new GroupMapper($this->ci->db);
                $groups = $mapper->getGroups(['group_id' => $group_id, 'subnet_id' => $subnet_id]);
                if(sizeof($groups) == 1) {
                    $r->success();
                    $r->setData($groups[0]->serialize());
                }else{
                    $r->fail();
                    $r->setCode(404);
                }
            } catch (\InvalidArgumentException $e) {
                $r->fail();
                $r->addMessage($e->getMessage());
            }
        }
        return $response->withStatus($r->getCode())->withJson($r);
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