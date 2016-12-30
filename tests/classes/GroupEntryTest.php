<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 12/30/2016
 * Time: 7:37 PM
 */
class GroupEntryTest extends PHPUnit_Framework_TestCase {

    public function createGroupWithouthId(){
        $params = array(
            'group_subnet_id' => 1,
            'group_name' => 'test_group',
            'group_description' => 'Test group name');
        $this->group = new GroupEntry($params);

        $this->assertInstanceOf(GroupEntry::class, $this->group);
    }
}