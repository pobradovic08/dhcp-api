<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/30/2016
 * Time: 7:37 PM
 */
class GroupEntryTest extends PHPUnit_Framework_TestCase {

    public function validData () {
        return array (
            "one" => array (array ('group_id' => 1,
                'group_subnet_id' => 1,
                'group_name' => 'test_group',
                'group_description' => 'Test group name')),
            "two" => array (array ('group_id' => 99999,
                'group_subnet_id' => 999999,
                'group_name' => 'test_group2',
                'group_description' => 'Test group name 2'))
        );
    }

    public function validDataWithoutId () {
        return array (
            "one" => array (array ('group_subnet_id' => 1,
                'group_name' => 'test_group',
                'group_description' => 'Test group name')),
            "two" => array (array ('group_subnet_id' => 999999,
                'group_name' => 'test_group2',
                'group_description' => 'Test group name 2'))
        );
    }

    public function invalidData () {
        return array (
            "missing_id" => array (array ('group_name' => 'test_group',
                'group_description' => 'Test group name')),
            "missing_name" => array (array ('group_subnet_id' => 1,
                'group_description' => 'Test group name')),
            "missing_description" => array (array ('group_subnet_id' => 1,
                'group_name' => 'test_group'))
        );
    }

    /**
     * @dataProvider validDataWithoutId
     */
    public function testGroupCreationWithoutId ($params) {
        $this->group = new GroupEntry($params);
        $this->assertInstanceOf (GroupEntry::class, $this->group);
        $this->assertEquals (null, $this->group->getId ());
        $this->assertEquals ($params['group_subnet_id'], $this->group->getSubnetId ());
        $this->assertEquals ($params['group_name'], $this->group->getName ());
        $this->assertEquals ($params['group_description'], $this->group->getDescription ());
    }

    /**
     * @dataProvider validData
     */
    public function testGroupCreationWithId ($params) {
        $this->group = new GroupEntry($params);
        $this->assertInstanceOf (GroupEntry::class, $this->group);
        $this->assertEquals ($params['group_id'], $this->group->getId ());
        $this->assertEquals ($params['group_subnet_id'], $this->group->getSubnetId ());
        $this->assertEquals ($params['group_name'], $this->group->getName ());
        $this->assertEquals ($params['group_description'], $this->group->getDescription ());
    }

    /**
     * @dataProvider invalidData
     * @expectedException InvalidArgumentException
     */
    public function testGroupCreationWithoutRequiredArguments ($params) {
        $this->group = new GroupEntry($params);
    }

    /**
     * @dataProvider validData
     */
    public function testSerializedJsonKeys ($params) {
        $this->group = new GroupEntry($params);
        $this->assertArrayHasKey('group_id', $this->group->serialize());
        $this->assertArrayHasKey('group_subnet_id', $this->group->serialize());
        $this->assertArrayHasKey('group_name', $this->group->serialize());
        $this->assertArrayHasKey('group_description', $this->group->serialize());

    }

    /**
     * @dataProvider validData
     */
    public function testSerializedJsonValues ($params) {
        $this->group = new GroupEntry($params);
        $this->assertEquals($this->group->getId(), $this->group->serialize()['group_id']);
        $this->assertEquals($this->group->getSubnetId(), $this->group->serialize()['group_subnet_id']);
        $this->assertEquals($this->group->getName(), $this->group->serialize()['group_name']);
        $this->assertEquals($this->group->getDescription(), $this->group->serialize()['group_description']);
    }
}