<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/31/2016
 * Time: 1:08 AM
 */
class DhcpResponse {

    /**
     * DhcpResponse constructor.
     */
    public function __construct () {
        $this->success = false;
        $this->code = 500;
        $this->messages = array ();
        $this->data = array ();
    }

    public function clearMessages(){
        $this->messages = array();
    }

    public function addMessage ($message) {
        try {
            $this->messages[] = strval ($message);
        }catch (Exception $e){
            throw new InvalidArgumentException("Message must be a string");
        }
    }

    public function getMessages(){
        return $this->messages;
    }

    public function success () {
        $this->success = true;
    }

    public function isSuccessful () {
        return $this->success;
    }

    public function fail () {
        $this->success = false;
    }

    public function setCode ($code) {
        if (is_int ($code) and $code >= 100 and $code < 600) {
            $this->code = (int)$code;
        } else {
            throw new InvalidArgumentException("Invalid status code");
        }
    }

    public function getCode () {
        return $this->code;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }
}