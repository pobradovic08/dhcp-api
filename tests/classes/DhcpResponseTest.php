<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/31/2016
 * Time: 1:09 AM
 */

namespace Dhcp;

class DhcpResponseTest extends \PHPUnit_Framework_TestCase {

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
