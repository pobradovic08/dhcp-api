<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/30/2016
 * Time: 9:14 PM
 */

namespace classes;


class EndHostTypeEntryTest extends \PHPUnit_Framework_TestCase {

    public function validDataWithoutId () {
        return array (
            'one' => array (array (
                'end_host_type_description' => "End host type description"
            ))
        );
    }

    public function validData () {
        return array (
            array (array (
                'end_host_type_id' => 1,
                'end_host_type_description' => "End host type description"
            )),
            array (array (
                'end_host_type_id' => 9999,
                'end_host_type_description' => "End host type description 2"
            )),
        );
    }

    public function invalidData(){
        return array (
            // Negative ID
            array (array (
                'end_host_type_id' => -1,
                'end_host_type_description' => "End host type description"
            )),
            // ID is string
            array (array (
                'end_host_type_id' => "asdasd",
                'end_host_type_description' => "End host type description"
            )),
            // ID is not greater then 0
            array (array (
                'end_host_type_id' => 0,
                'end_host_type_description' => "End host type description"
            )),
            // ID is float
            array (array (
                'end_host_type_id' => 1.5,
                'end_host_type_description' => "End host type description"
            )),
            // Description is longer than 64 chars
            array (array (
                'end_host_type_id' => 1,
                'end_host_type_description' => "End host type descriptionEnd host
                type descriptionEnd host type description"
            )),
            // Description is missing
            array (array (
                'end_host_type_id' => 9999,
            )),
        );
    }

    /**
     * @dataProvider validDataWithoutId
     */
    public function testValidTypeCreationWithoutId ($data) {
        $this->type = new \EndHostTypeEntry($data);
        $this->assertInstanceOf(\EndHostTypeEntry::class, $this->type);
        $this->assertEquals(null, $this->type->getId());
        $this->assertEquals($data['end_host_type_description'], $this->type->getDescription());
    }

    /**
     * @dataProvider validData
     */
    public function testValidTypeCreation ($data) {
        $this->type = new \EndHostTypeEntry($data);
        $this->assertInstanceOf(\EndHostTypeEntry::class, $this->type);
        $this->assertEquals($data['end_host_type_id'], $this->type->getId());
        $this->assertEquals($data['end_host_type_description'], $this->type->getDescription());
    }

    /**
     * @dataProvider invalidData
     * @expectedException \InvalidArgumentException
     */
    public function testCreationWithoutRequiredData ($data){
        $this->type = new \EndHostTypeEntry($data);
    }

    /**
     * @dataProvider validData
     */
    public function testSerializedJsonKeys($data){
        $this->type = new \EndHostTypeEntry($data);
        $this->assertArrayHasKey('end_host_type_id', $this->type->serialize());
        $this->assertArrayHasKey('end_host_type_description', $this->type->serialize());
    }

    /**
     * @dataProvider validData
     */
    public function testSerializedJsonValues($data){
        $this->type = new \EndHostTypeEntry($data);
        $this->assertEquals($this->type->getId(), $this->type->serialize()['end_host_type_id']);
        $this->assertEquals($this->type->getDescription(), $this->type->serialize()['end_host_type_description']);
    }
}
