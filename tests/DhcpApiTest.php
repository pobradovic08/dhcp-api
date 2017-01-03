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
            [DhcpApiTest::$base . 'endhosts', false],
            [DhcpApiTest::$base . 'endhosts/id/1', true],
            [DhcpApiTest::$base . 'endhosts/id/9999', true],
            [DhcpApiTest::$base . 'endhosts/mac/2D-06-CA-C8-65-2C', true],
            [DhcpApiTest::$base . 'endhosts/types', false],
            [DhcpApiTest::$base . 'endhosts/types/id/1', true],
            [DhcpApiTest::$base . 'endhosts/types/id/9999', true],
            [DhcpApiTest::$base . 'reservations', false],
            [DhcpApiTest::$base . 'subnets', false],
            [DhcpApiTest::$base . 'subnets/id/1', true],
            [DhcpApiTest::$base . 'subnets/id/9999', true],
        ];
    }

    /**
     * @dataProvider validUrls
     * @param $url
     * @param $can_fail
     */
    public function testEndHostsResponseCodes ($url, $can_fail) {
        try {
            $response = $this->c->request ('GET', $url);
            $body = $response->getBody ()->getContents ();
            $this->assertEquals (200, $response->getStatusCode ());
            $this->assertJson ($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertTrue($json->success);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse ();
            $body = $response->getBody ()->getContents ();
            $this->assertEquals (404, $response->getStatusCode ());
            $this->assertJson($body);
            $this->assertTrue ($can_fail);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            $this->assertFalse($json->success);
        }
    }
}
