<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 11:20 PM
 */
class GroupController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function get_groups ($request, $response, $args) {
        $r = new DhcpResponse();
        $this->ci->logger->addInfo("Full group list");
        $r->success();
        $r->setData("haha");
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_group_by_id ($request, $response, $args) {
        $r = new DhcpResponse();
        $this->ci->logger->addInfo("Group with ID: {$args['id']}");
        $r->success();
        $r->setData("haha");
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function post_group ($request, $response, $args) {

    }

    public function put_group ($request, $response, $args) {

    }

    public function delete_group ($request, $response, $args) {

    }
}