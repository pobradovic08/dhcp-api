<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/25/2017
 * Time: 10:59 PM
 */

namespace Dhcp\Controller;

use \Interop\Container\ContainerInterface as ContainerInterface;
use Dhcp\Response;

class BaseController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->ci->capsule;
        $this->r = new Response();
    }
}