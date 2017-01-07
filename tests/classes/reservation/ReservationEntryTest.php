<?php

namespace Dhcp\Reservation;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/2/2017
 * Time: 8:46 PM
 */
class ReservationEntryTest extends \PHPUnit_Framework_TestCase {

    public $r;

    public function fullData () {
        $subnet = new \Dhcp\Subnet\SubnetEntry(['subnet_id' => 1, 'network' => '10.0.0.0', 'network_mask' => '255.255.255.0',
                                                'vlan'      => 4, 'subnet_description' => 'Test subnet']);
        $group = new \Dhcp\Group\GroupEntry(['group_id'          => 10, 'group_subnet_id' => 1, 'group_name' => 'test_name',
                                             'group_description' => 'Test group description']);
        $end_host_type = new \Dhcp\EndHostType\EndHostTypeEntry(['end_host_type_id'          => 1000,
                                                                 'end_host_type_description' => 'Test end host']);
        $end_host = new \Dhcp\EndHost\EndHostEntry(['end_host_id'   => 100,
                                                    'hostname'      => 'test.example.com',
                                                    'mac'           => '1234.5678.9abc',
                                                    'end_host_type' => $end_host_type]);
        return [
            [['reservation_id' => 1,
              'ip'          => '10.20.30.1',
              'active'      => true,
              'comment'     => 'Reservation comment',
              'subnet_id'   => 3, 'group_id' => 2, 'end_host_id' => 1,
              'update_time' => 0, 'insert_time' => 0]],
            [['reservation_id' => 1,
              'ip'          => '10.20.30.1',
              'active'      => true,
              'comment'     => 'Reservation comment',
              'subnet'      => $subnet,
              'subnet_id'   => 1,
              'group'       => $group,
              'group_id'    => 10,
              'end_host'    => $end_host,
              'end_host_id' => 100,
              'update_time' => 0, 'insert_time' => 0]],
        ];
    }


    public function validData () {
        return [
            [['reservation_id' => 1, 'ip' => '10.20.30.1', 'active' => true,
              'comment'     => 'Reservation comment', 'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1,
              'update_time' => 0, 'insert_time' => 0]],
            [['reservation_id' => 1, 'ip' => '10.20.30.1', 'active' => true,
              'comment'     => 'Reservation comment', 'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1,
              'update_time' => 0, 'insert_time' => 0]],
        ];
    }

    public function validOptionalMissingData () {
        return [
            [['reservation_id' => 1, 'ip' => '10.20.30.1', 'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.0', 'subnet_id' => 1, 'group_id' => 3, 'end_host_id' => 5]],
            [['ip' => '10.20.30.1', 'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 6]],
            [['ip' => '10.20.30.2', 'subnet_id' => 3, 'group_id' => 1, 'end_host_id' => 7]],
        ];
    }

    public function invalidData () {
        return [
            // Missing required data
            [['subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.2', 'group_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.2', 'subnet_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.2', 'subnet_id' => 0, 'group_id' => 1, 'end_host_id' => 7]],
            [['ip' => '10.20.30.2', 'subnet_id' => -1, 'group_id' => 1, 'end_host_id' => 7]],
            [['ip' => '10.20.30.2', 'subnet_id' => "3", 'group_id' => 1, 'end_host_id' => 7]],
            [['ip' => '10.20.30.2', 'subnet_id' => true, 'group_id' => 2]],
            [['ip' => '10.20.30.1', 'active' => true,]],
        ];
    }

    public function testAttributesExists () {
        $data = [
            'ip'          => '10.20.30.1',
            'active'      => true,
            'subnet_id'   => 2,
            'group_id'    => 2,
            'end_host_id' => 1,
        ];
        $this->r = new ReservationEntry($data);
        $attributes = [
            'reservation_id', 'ip', 'active', 'subnet', 'subnet_id', 'group', 'group_id', 'end_host', 'end_host_id',
            'insert_time', 'update_time', 'comment',
        ];
        foreach ( $attributes as $attribute ) {
            $this->assertObjectHasAttribute($attribute, $this->r);
        }
    }

    /**
     * @dataProvider validData
     */
    public function testValidCreation ($data) {
        $this->r = new ReservationEntry($data);
        $this->assertEquals($data['reservation_id'], $this->r->getId());
        $this->assertEquals($data['ip'], $this->r->getIp());
        $this->assertEquals($data['active'], $this->r->isActive());
        $this->assertEquals($data['comment'], $this->r->getComment());
        $this->assertEquals($data['subnet_id'], $this->r->getSubnetId());
        $this->assertEquals($data['group_id'], $this->r->getGroupId());
        $this->assertEquals($data['end_host_id'], $this->r->getEndHostId());
    }

    /**
     * @dataProvider validOptionalMissingData
     * @param $data
     */
    public function testValidCreationWithOptionalsMissing ($data) {
        $this->r = new ReservationEntry($data);
        $this->assertInstanceOf(ReservationEntry::class, $this->r);
    }

    /**
     * @dataProvider invalidData
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidCreation ($data) {
        $this->r = new ReservationEntry($data);
    }

    /**
     * @dataProvider fullData
     * @param $data
     */
    public function testSerializedData ($data) {
        $this->r = new ReservationEntry($data);
        $this->assertInstanceOf(ReservationEntry::class, $this->r);
        $a = $this->r->serialize();
        $this->assertArrayHasKey('reservation_id', $a);
        $this->assertArrayHasKey('ip', $a);
        $this->assertArrayHasKey('active', $a);
        $this->assertArrayHasKey('reservation_comment', $a);
        $this->assertArrayHasKey('reservation_insert_time', $a);
        $this->assertArrayHasKey('reservation_update_time', $a);
        $this->assertArrayHasKey('end_host', $a);
        $this->assertArrayHasKey('end_host_id', $a);
        $this->assertArrayHasKey('group', $a);
        $this->assertArrayHasKey('group_id', $a);
        $this->assertArrayHasKey('subnet', $a);
        $this->assertArrayHasKey('subnet_id', $a);

        $this->assertJson(json_encode($a), "Reservation JSON invalid");

        $this->assertEquals($data['reservation_id'], $a['reservation_id']);
        $this->assertEquals($data['ip'], $a['ip']);
        $this->assertEquals($data['active'], $a['active']);
        $this->assertEquals($data['comment'], $a['reservation_comment']);
        $this->assertEquals($data['insert_time'], $a['reservation_insert_time']);
        $this->assertEquals($data['update_time'], $a['reservation_update_time']);
        // Test just ID, testing object serialization is done in their respective unit tests
        $this->assertEquals($data['end_host_id'], $a['end_host_id']);
        $this->assertEquals($data['group_id'], $a['group_id']);
        $this->assertEquals($data['subnet_id'], $a['subnet_id']);
    }
}
