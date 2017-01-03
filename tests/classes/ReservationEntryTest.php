<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/2/2017
 * Time: 8:46 PM
 */
class ReservationEntryTest extends PHPUnit_Framework_TestCase {


    public function validData () {
        return [
            [['reservation_id' => 1, 'ip' => '10.20.30.1', 'active' => true,
                'comment' => 'Reservation comment', 'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1,
                'update_time' => 0, 'insert_time' => 0]]
        ];
    }

    public function validOptionalMissingData () {
        return [
            [['reservation_id' => 1, 'ip' => '10.20.30.1', 'active' => true,
                'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.1', 'active' => true,
                'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.1', 'active' => true,
                'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1]],
            [['ip' => '10.20.30.1', 'active' => true,
                'subnet_id' => 2, 'group_id' => 2, 'end_host_id' => 1]],
        ];
    }

    public function testAttributesExists () {
        $data = array (
            'ip' => '10.20.30.1',
            'active' => true,
            'subnet_id' => 2,
            'group_id' => 2,
            'end_host_id' => 1
        );
        $this->r = new ReservationEntry($data);
        $attributes = array (
            'reservation_id', 'ip', 'active', 'subnet', 'subnet_id', 'group', 'group_id', 'end_host', 'end_host_id',
            'insert_time', 'update_time', 'comment'
        );
        foreach ($attributes as $attribute) {
            $this->assertObjectHasAttribute ($attribute, $this->r);
        }
    }

    /**
     * @dataProvider validData
     */
    public function testValidCreation ($data) {
        $this->r = new ReservationEntry($data);
        $this->assertEquals ($data['reservation_id'], $this->r->getId ());
        $this->assertEquals ($data['ip'], $this->r->getIp ());
        $this->assertEquals ($data['active'], $this->r->isActive ());
        $this->assertEquals ($data['comment'], $this->r->getComment ());
        $this->assertEquals ($data['subnet_id'], $this->r->getSubnetId ());
        $this->assertEquals ($data['group_id'], $this->r->getGroupId ());
        $this->assertEquals ($data['end_host_id'], $this->r->getEndHostId ());
    }

    /**
     * @dataProvider validOptionalMissingData
     * @param $data
     */
    public function testValidCreationWithOptionalsMissing ($data) {
        $this->r = new ReservationEntry($data);
        $this->assertInstanceOf (ReservationEntry::class, $this->r);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidCreation () {

    }
}
