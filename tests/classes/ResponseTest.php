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

class ResponseTest extends \PHPUnit_Framework_TestCase {

    public function goodCodes () {
        return array ([
            100, 400, 500, 304, 210, 420
        ]);
    }

    public function badCodes () {
        return array ([
            -1, 0, 600, 9999, 2.4, 1.0, "1", '1', true, ''
        ]);
    }

    public function validMessages () {
        return [
            ["Test message", "Test message"],
            [123, "123"],
            [123.555, "123.555"],
            [false, ""],
            [true, "1"]
        ];
    }

    public function data () {
        return [
            ["asd"],
            [$this],
            [array ("one", "two")],
            [new \Exception("test")],
        ];
    }

    public function setUp () {
        $this->r = new Response();
    }

    public function testNewInstance () {
        $this->assertObjectHasAttribute ('success', $this->r);
        $this->assertObjectHasAttribute ('code', $this->r);
        $this->assertObjectHasAttribute ('messages', $this->r);
        $this->assertObjectHasAttribute ('data', $this->r);
    }

    public function testSuccess () {
        $this->r->fail ();
        $this->r->success ();
        $this->assertTrue ($this->r->isSuccessful ());
    }

    public function testFail () {
        $this->r->success ();
        $this->r->fail ();
        $this->assertFalse ($this->r->isSuccessful ());
        $this->assertEquals(400, $this->r->getCode());
        $this->r->success();
        $this->r->fail(404);
        $this->assertFalse($this->r->isSuccessful());
        $this->assertEquals(404, $this->r->getCode());
        $this->r->success();
        $this->r->clearMessages();
        $this->r->fail(403, "Forbidden");
        $this->assertFalse($this->r->isSuccessful());
        $this->assertEquals(403, $this->r->getCode());
        $this->assertEquals("Forbidden", $this->r->getMessages()[0]);
    }

    /**
     * @dataProvider badCodes
     * @expectedException \InvalidArgumentException
     * @param $code
     */
    public function testBadRequestCode ($code) {
        $this->r->setCode ($code);
    }

    /**
     * @dataProvider goodCodes
     * @param $code
     */
    public function testGoodCodes ($code) {
        $this->r->setCode ($code);
        $this->assertEquals ($code, $this->r->getCode ());
    }

    public function testClearMessages () {
        $this->r->clearMessages ();
        $this->assertEmpty ($this->r->getMessages ());
    }

    /**
     * @dataProvider validMessages
     * @param $message
     * @param $string_message
     */
    public function testAddValidMessage ($message, $string_message) {
        $this->r->clearMessages ();
        $this->r->addMessage ($message);
        $this->assertEquals (1, count ($this->r->getMessages ()));
        $this->assertEquals ($string_message, $this->r->getMessages ()[0]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddInvalidMessage () {
        $this->r->addMessage ($this);
    }

    /**
     * @dataProvider data
     */
    public function testIsDataSet ($data) {
        $this->r->setData ($data);
        $this->assertEquals ($data, $this->r->getData ());
    }
}
