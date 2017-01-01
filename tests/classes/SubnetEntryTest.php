<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/1/2017
 * Time: 2:09 PM
 */

namespace classes;

class SubnetEntryTest extends \PHPUnit_Framework_TestCase {

    public function validData () {
        return [
            [['subnet_id' => 1, 'vlan' => 2,
                'network' => '10.20.30.0', 'network_mask' => '255.255.255.0',
                'subnet_description' => "Test subnet description"],
                ['first' => '10.20.30.1', 'last' => '10.20.30.254',
                    'network' => '10.20.30.0', 'broadcast' => '10.20.30.255']]
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
        $this->s = new \SubnetEntry($data);
        $this->assertObjectHasAttribute ('subnet_id', $this->s);
        $this->assertObjectHasAttribute ('vlan', $this->s);
        $this->assertObjectHasAttribute ('network', $this->s);
        $this->assertObjectHasAttribute ('network_mask', $this->s);
        $this->assertObjectHasAttribute ('subnet_description', $this->s);
    }

    /**
     * @dataProvider validData
     * @param $data
     * @param $results
     */
    public function testValidSubnetIpAndMask ($data, $results) {
        $this->s = new \SubnetEntry($data);
        $this->assertEquals($results['network'], $this->s->getNetworkAddress());
    }

    public function testInvalidSubnetIpAndMask () {

    }
}
