<?php

/**
 * ISC-DHCP Web API
 * Copyright (C) 2016  Pavle Obradovic (pajaja)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


class DhcpApiTest extends PHPUnit_Framework_TestCase {

    public $c;
    public static $base = 'http://test-srv-pavle.vektor.net/';

    public function setUp () {
        $this->c = new \GuzzleHttp\Client();
    }

    public function validUrls () {
        // [0] -> url, [1] -> can it fail with 404?
        return [
            ['endhosts', false],
            ['endhosts/id/1', true],
            ['endhosts/id/9999', true],
            ['endhosts/search/pav', true],
            ['endhosts/mac/1234.5678.abcd', true],
            ['endhosts/mac/2D-06-CA-C8-65-2C', true],
            ['endhosts/types', false],
            ['endhosts/types/id/1', true],
            ['endhosts/types/id/9999', true],
            ['reservations', false],
            ['reservations/id/1', true],
            ['reservations/id/9999', true],
            ['reservations/ip/1.1.1.1', true],
            ['reservations/ip/10.20.30.1', true],
            ['reservations/subnet/1', true],
            ['reservations/subnet/9999', true],
            ['reservations/group/1', true],
            ['reservations/group/9999', true],
            ['reservations/mac/1234.5678.abcd', true],
            ['reservations/mac/2D-06-CA-C8-65-2C', true],
            ['subnets', false],
            ['subnets/id/2', true],
            ['subnets/id/9999', true],
            ['subnets/id/2/free', true],
            ['subnets/id/9999/free', true],
            ['subnets/ip/1.12.123.1', true],
            ['subnets/ip/10.20.30.1', true],
            ['subnets/vlan/29', true],
            ['subnets/id/2/groups', true],
            ['subnets/id/2/groups/id/1', true],
        ];
    }

    /**
     * @dataProvider validUrls
     * @param $url
     * @param $can_fail
     */
    public function testApiEndpointsGetResponseCodes ($url, $can_fail) {
        try {
            // Main stuff
            $response = $this->c->request('GET', self::$base . $url);
            // Code is 2XX so no exception
            $body = $response->getBody()->getContents();
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertJson($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertTrue($json->success);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Exception because we didn't get 2XX
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertJson($body);
            $this->assertTrue($can_fail);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertFalse($json->success);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail("Got 5XX HTTP code");
        }
    }

    public function invalidUrls () {
        return [
            ['endhosts/id/0'],
            ['endhosts/types/id/0'],
            ['reservations/id/0'],
            ['reservations/subnet/0'],
            ['reservations/group/0',],
            ['subnets/id/0'],
            ['subnets/id/0/free'],
            ['subnets/ip/256.12.123.1'],
            ['subnets/vlan/0'],
            ['subnets/vlan/4095'],
            ['subnets/vlan/4096'],
            ['subnets/id/0/groups'],
            ['subnets/id/1/groups/id/0', true],
        ];
    }

    /**
     * @dataProvider invalidUrls
     * @param $url
     */
    public function testApiEndpointsWithInvalidArguments ($url) {
        try {
            $this->c->request('GET', self::$base . $url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $this->assertEquals(400, $response->getStatusCode());
            $this->assertJson($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertFalse($json->success);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail("Got 5XX HTTP code");
        }
    }

    //TODO: Subnet - Create, Update, Delete
    public $subnet_id;
    public function testInsertSubnet () {
        try {
            $response = $this->c->post(self::$base . 'subnets',
                                       ['json' => [
                                           'vlan' => 666
                                       ]]);
            $this->fail($response->getBody()->getContents());
        }catch (\GuzzleHttp\Exception\ServerException $e){
            $this->fail("aaa");
        }
    }

    public function testUpdateSubnet () {
        $this->fail();
    }

    public function testDeleteSubnet () {
        $this->fail();
    }

    //TODO: Else
    public function testInsertGroup () {
        $this->fail();
    }

    public function testInsertEndHostType () {
        $this->fail();
    }

    public function testInsertEndHost () {
        $this->fail();
    }

    public function testInsetReservation () {
        $this->fail();
    }
    //TODO Update
    //TODO Delete
}
