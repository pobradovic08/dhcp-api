<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/1/2017
 * Time: 2:09 PM
 */
class SubnetEntryTest extends \PHPUnit_Framework_TestCase {

    public function validData () {
        return [
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"],
                ['first' => '10.20.30.1', 'last' => '10.20.30.254', 'cidr' => 24,
                    'network' => '10.20.30.0', 'broadcast' => '10.20.30.255']],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.255',
                'subnet_description' => "Test subnet description"],
                ['first' => '10.20.30.0', 'last' => '10.20.30.0', 'cidr' => 32,
                    'network' => '10.20.30.0', 'broadcast' => '10.20.30.0']],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '0.0.0.0', 'network_mask' => '0.0.0.0',
                'subnet_description' => "Test subnet description"],
                ['first' => '0.0.0.1', 'last' => '255.255.255.254', 'cidr' => 0,
                    'network' => '0.0.0.0', 'broadcast' => '255.255.255.255']],
        ];
    }

    public function invalidData () {
        return [
            // Missing fields
            [['vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 2,
                'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
            ]],
            // Invalid description
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet descriptionTest subnet descriptionTest subnet description
                Test subnet descriptionTest subnet descriptionTest subnet descriptionTest subnet description"]],
            // Invalid Subnet and VLAN IDs
            [['subnet_id' => "1", 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => "2",
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => -1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 0, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => -1,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 0,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 4095,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            // Invalid IP
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => 'asd', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            // Invalid masks
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => 'asd',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.0.255.0',
                'subnet_description' => "Test subnet description"]],
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.256',
                'subnet_description' => "Test subnet description"]],
            //Invalid network address (valid IP but not the network address)
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.1', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"]],
        ];
    }

    public function testHasAllAttributes () {
        $data = array (
            'subnet_id' => 1,
            'vlan' => 2,
            'network' => '10.20.30.0',
            'network_mask' => '255.255.255.0',
            'subnet_description' => "Test subnet description"
        );
        $this->s = new SubnetEntry($data);
        $this->assertObjectHasAttribute ('subnet_id', $this->s);
        $this->assertObjectHasAttribute ('vlan', $this->s);
        $this->assertObjectHasAttribute ('network', $this->s);
        $this->assertObjectHasAttribute ('network_mask', $this->s);
        $this->assertObjectHasAttribute ('subnet_description', $this->s);
        $this->assertObjectHasAttribute ('first_host_address', $this->s);
        $this->assertObjectHasAttribute ('last_host_address', $this->s);
        $this->assertObjectHasAttribute ('network_address', $this->s);
        $this->assertObjectHasAttribute ('broadcast_address', $this->s);
        $this->assertObjectHasAttribute ('cidr', $this->s);
    }

    /**
     * @dataProvider validData
     * @param $data
     * @param $results
     */
    public function testValidSubnetIpAndMask ($data, $results) {
        $this->s = new SubnetEntry($data);
        $this->assertEquals ($data['subnet_id'], $this->s->getId ());
        $this->assertEquals ($data['vlan'], $this->s->getVlan ());
        $this->assertEquals ($data['network'], $this->s->getNetwork ());
        $this->assertEquals ($data['network_mask'], $this->s->getNetworkMask ());
        $this->assertEquals ($data['subnet_description'], $this->s->getDescription ());
        /*
         * Test calculation of significant addresses and CIDR
         */
        $this->assertEquals ($results['network'], $this->s->getNetworkAddress ());
        $this->assertEquals ($results['broadcast'], $this->s->getBroadcastAddress ());
        $this->assertEquals ($results['first'], $this->s->getFirstHostAddress ());
        $this->assertEquals ($results['last'], $this->s->getLastHostAddress ());
        $this->assertEquals ($results['cidr'], $this->s->getCidr ());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidData
     */
    public function testInvalidSubnetIpAndMask ($data) {
        $this->s = new SubnetEntry($data);
    }
}
