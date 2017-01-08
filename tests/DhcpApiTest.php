<?php

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 7:22 PM
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
            [self::$base . 'endhosts', false],
            [self::$base . 'endhosts/id/1', true],
            [self::$base . 'endhosts/id/9999', true],
            [self::$base . 'endhosts/search/pav', true],
            [self::$base . 'endhosts/mac/1234.5678.abcd', true],
            [self::$base . 'endhosts/mac/2D-06-CA-C8-65-2C', true],
            [self::$base . 'endhosts/types', false],
            [self::$base . 'endhosts/types/id/1', true],
            [self::$base . 'endhosts/types/id/9999', true],
            [self::$base . 'reservations', false],
            [self::$base . 'reservations/id/1', true],
            [self::$base . 'reservations/id/9999', true],
            [self::$base . 'reservations/ip/1.1.1.1', true],
            [self::$base . 'reservations/ip/10.20.30.1', true],
            [self::$base . 'reservations/subnet/1', false],
            [self::$base . 'reservations/subnet/9999', false],
            [self::$base . 'reservations/group/1', false],
            [self::$base . 'reservations/group/9999', false],
            [self::$base . 'reservations/mac/1234.5678.abcd', false],
            [self::$base . 'reservations/mac/2D-06-CA-C8-65-2C', false],
            [self::$base . 'subnets', false],
            [self::$base . 'subnets/id/2', true],
            [self::$base . 'subnets/id/9999', true],
            [self::$base . 'subnets/id/2/free', true],
            [self::$base . 'subnets/id/9999/free', true],
            [self::$base . 'subnets/ip/1.12.123.1', true],
            [self::$base . 'subnets/ip/10.20.30.1', true],
            [self::$base . 'subnets/vlan/29', true],
            [self::$base . 'subnets/id/2/groups', true],
            [self::$base . 'subnets/id/2/groups/id/1', true],
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
            $response = $this->c->request('GET', $url);
            // Code is 2XX so no exception
            $body = $response->getBody()->getContents();
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertJson($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertTrue($json->success);
        } catch ( \GuzzleHttp\Exception\ClientException $e ) {
            // Exception because we didn't get 2XX
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertJson($body);
            $this->assertTrue($can_fail);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertFalse($json->success);
        } catch ( \GuzzleHttp\Exception\ServerException $e ) {
            $this->fail("Got 5XX HTTP code");
        }
    }

    public function invalidUrls () {
        return [
            [self::$base . 'endhosts/id/0'],
            [self::$base . 'endhosts/types/id/0'],
            [self::$base . 'reservations/id/0'],
            [self::$base . 'reservations/subnet/0'],
            [self::$base . 'reservations/group/0',],
            [self::$base . 'subnets/id/0'],
            [self::$base . 'subnets/id/0/free'],
            [self::$base . 'subnets/ip/256.12.123.1'],
            [self::$base . 'subnets/vlan/0'],
            [self::$base . 'subnets/vlan/4095'],
            [self::$base . 'subnets/vlan/4096'],
            [self::$base . 'subnets/id/0/groups'],
            [self::$base . 'subnets/id/1/groups/id/0', true],
        ];
    }

    /**
     * @dataProvider invalidUrls
     * @param $url
     */
    public function testApiEndpointsWithInvalidArguments ($url) {
        try {
            $this->c->request('GET', $url);
        } catch ( \GuzzleHttp\Exception\ClientException $e ) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $this->assertEquals(400, $response->getStatusCode());
            $this->assertJson($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertFalse($json->success);
        } catch ( \GuzzleHttp\Exception\ServerException $e ) {
            $this->fail("Got 5XX HTTP code");
        }
    }
}
