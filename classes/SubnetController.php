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
        $r->success();
        $r->setData("haha");
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function get_subnet_by_id ($request, $response, $args) {
        $r = new DhcpResponse();
        $this->ci->logger->addInfo("Full subnet list");
        $r->success();
        $r->setData("haha");
        return $response->withStatus($r->getCode())->withJson($r);
    }
}