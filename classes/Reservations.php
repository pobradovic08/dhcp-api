<?php

class Reservations {

    protected $ci;

    public function __construct(ContainerInterface $ci){
        $this->ci = $ci;
    }


    public function test($request, $response, $args){
        print "ASDASD";
    }

}
