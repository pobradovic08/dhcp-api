<?php

/**
 * ISC-DHCP Web API
 * Copyright (C) 2016  Pavle Obradovic (pajaja)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Dhcp;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/31/2016
 * Time: 1:08 AM
 */
class Response {

    /**
     * DhcpResponse constructor.
     */
    public function __construct () {
        $this->success = false;
        $this->code = 400;
        $this->messages = array ();
        $this->data = array ();
    }

    public function clearMessages(){
        $this->messages = array();
    }

    public function addMessage ($message) {
        try {
            $this->messages[] = strval ($message);
        }catch (\Exception $e){
            throw new \InvalidArgumentException("Message must be a string");
        }
    }

    public function getMessages(){
        return $this->messages;
    }

    public function success ($message = null) {
        $this->success = true;
        $this->setCode(200);
        if ($message) {
            $this->addMessage($message);
        }
    }

    public function isSuccessful () {
        return $this->success;
    }

    public function fail ($code = 400, $message = null) {
        $this->success = false;
        $this->setCode($code);
        if ($message) {
            $this->addMessage($message);
        }
    }

    public function setCode ($code) {
        if (Validator::validateHttpCode($code)) {
            $this->code = (int)$code;
        } else {
            throw new \InvalidArgumentException("Invalid status code");
        }
    }

    public function getCode ($default_code = 200) {
        if($this->code){
            return $this->code;
        }elseif(Validator::validateHttpCode($default_code)){
            return $default_code;
        }else{
            return 500;
        }
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function getJson() {

    }
}