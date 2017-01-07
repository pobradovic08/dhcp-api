<?php

namespace Dhcp;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/2/2017
 * Time: 5:33 PM
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

    public function validIds () {
        return [
            [1], [2], [41], [25], [22], [999], [555], [65535], [65536]
        ];
    }

    public function validVlanIds () {
        return [
            [1], [2], [41], [25], [22], [4094]
        ];
    }

    public function invalidIds () {
        return [
            [-1], [0], ["asd"], [true], [false], [[]], [new \ArrayObject()],
        ];
    }

    public function invalidVlanIds () {
        return [
            [-1], [0], [4095], [9999], ["asd"], [true], [false], [[]], [new \ArrayObject()],
        ];
    }

    public function validIps () {
        return [
            ['1.1.1.1'], ['1.1.1.0'], ['0.0.0.0'], ['0.1.1.1'], ['255.255.255.255']
        ];
    }

    public function invalidIps () {
        return [
            ['1.1.1.256'], ['1.1.1.'], [169090815], [-4125876481], ['asd']
        ];
    }

    public function validMasks () {
        return [['255.255.255.255'], ['255.255.255.254'], ['255.255.255.252'], ['255.255.255.248'],
            ['255.255.255.240'], ['255.255.255.224'], ['255.255.255.192'], ['255.255.255.128'],
            ['255.255.255.0'], ['255.255.254.0'], ['255.255.252.0'], ['255.255.248.0'],
            ['255.255.240.0'], ['255.255.224.0'], ['255.255.192.0'], ['255.255.128.0'],
            ['255.255.0.0'], ['255.254.0.0'], ['255.252.0.0'], ['255.248.0.0'], ['255.240.0.0'],
            ['255.224.0.0'], ['255.192.0.0'], ['255.128.0.0'], ['255.0.0.0'], ['254.0.0.0'],
            ['252.0.0.0'], ['248.0.0.0'], ['240.0.0.0'], ['224.0.0.0'], ['192.0.0.0'], ['128.0.0.0']];
    }

    public function invalidMasks () {
        return [
            ['255.255.255.253'], [169090815], [-4125876481], [0], ['asd'], [false], [true]
        ];
    }

    public function validDescription () {
        return [
            ['Test description'], ['TestdescriptionTestdescriptionTestdescriptionTestdescriptionTest'],
            ['Test deskrip�n'], ['???? ?????????']
        ];
    }

    public function invalidDescription () {
        return [
            [''], ['TestdescriptionTestdescriptionTestdescriptionTestdescriptionTestd']
        ];
    }

    /**
     * @dataProvider validIds
     */
    public function testValidIds ($id) {
        $this->assertTrue (Validator::validateId ($id));
    }

    /**
     * @dataProvider invalidIds
     * @param $id
     */
    public function testInvalidIds ($id) {
        $this->assertFalse (Validator::validateId ($id));
    }

    /**
     * @dataProvider validVlanIds
     * @param $vlan
     */
    public function testValidVlanIds ($vlan) {
        $this->assertTrue (Validator::validateVlanId ($vlan));
    }

    /**
     * @dataProvider invalidVlanIds
     * @param $vlan
     */
    public function testInvalidVlanIds ($vlan) {
        $this->assertFalse (Validator::validateVlanId ($vlan));
    }

    /**
     * @dataProvider validIps
     * @param $ip
     */
    public function testValidIpAddresses ($ip) {
        $this->assertTrue (Validator::validateIpAddress ($ip));
    }

    /**
     * @dataProvider invalidIps
     * @param $ip
     */
    public function testInvalidIpAddresses ($ip) {
        $this->assertFalse (Validator::validateIpAddress ($ip));
    }

    /**
     * @dataProvider validMasks
     * @param $mask
     */
    public function testValidMasks ($mask) {
        $this->assertTrue (Validator::validateIpMask ($mask));
    }

    /**
     * @dataProvider invalidMasks
     * @param $mask
     */
    public function testInvalidMasks ($mask) {
        $this->assertFalse (Validator::validateIpMask ($mask));
    }

    /**
     * @dataProvider validDescription
     * @param $d
     */
    public function testValidDescriptions ($d) {
        $this->assertTrue (Validator::validateDescription ($d));
    }

    /**
     * @dataProvider invalidDescription
     * @param $d
     */
    public function testInvalidDescriptions ($d) {
        $this->assertFalse (Validator::validateDescription ($d));
    }
}
