<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/30/2016
 * Time: 9:54 PM
 */

namespace Dhcp\EndHost;


class EndHostEntryTest extends \PHPUnit_Framework_TestCase {

    public function validMinimalData () {
        return array (
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '1234.1234.1234',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '12:34:12:34:12:34',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '12-34-12-34-12-34',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '12-34-12-AB-CD-EF',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '12-34-12-ab-cd-ef',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '12:34:12:AB:CD:EF',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '12:34:12:ab:cd:ef',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '1234.12AB.CDEF',
                'end_host_type_id' => 1)),
            array (array (
                'hostname' => 'test.hostname.com',
                'mac' => '1234.12ab.cdef',
                'end_host_type_id' => 1)),
        );
    }

    /**
     * @dataProvider validMinimalData
     */

    public function testValidCreationWithoutIdAndTypeObject ($data) {
        $this->eh = new EndHostEntry($data);
        $this->assertInstanceOf(EndHostEntry::class, $this->eh);

    }

    /**
     * @dataProvider validMinimalData
     */
    public function testGetterReturnTypes($data){
        $this->eh = new EndHostEntry($data);
        //$this->assertInternalType ('int', $this->eh->getId ());
        $this->assertInternalType ('string', $this->eh->getMac ());
        $this->assertInternalType ('string', $this->eh->getMacHex ());
        $this->assertTrue(ctype_xdigit($this->eh->getMacHex()));
    }
}
