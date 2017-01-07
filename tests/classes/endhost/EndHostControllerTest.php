<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/7/2017
 * Time: 10:11 PM
 */

namespace Dhcp\EndHost;


class EndHostControllerTest extends \PHPUnit_Framework_TestCase {
    public $c;
    public $id;
    public $db;
    public static $base = 'http://test-srv-pavle.vektor.net/endhosts';


    public function testGetAllEndHosts () {
        $this->confirmJsonAndCode(self::$base, true, 200);
    }

    public function testGetSingleEndHost () {
        //$this->confirmJsonAndCode(self::$base . '/id/' . $this->id, true, 200);
        $this->confirmJsonAndCode(self::$base . '/id/99999', false, 404);
        $this->confirmJsonAndCode(self::$base . '/id/0', false, 400);
    }

    public function confirmJsonAndCode ($url, $success, $code) {
        try {
            // Main stuff
            $response = $this->c->request('GET', $url);
            // Code is 2XX so no exception
            $body = $response->getBody()->getContents();
            $this->assertEquals($code, $response->getStatusCode());
            $this->assertJson($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            if ($success) {
                $this->assertTrue($json->success);
            } else {
                $this->assertFalse($json->success);
            }
        } catch ( \GuzzleHttp\Exception\ClientException $e ) {
            // Exception because we didn't get 2XX
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertJson($body);
            $json = json_decode($body);
            $this->assertObjectHasAttribute('success', $json);
            if ($success) {
                $this->assertTrue($json->success);
            } else {
                $this->assertFalse($json->success);
            }
        } catch ( \GuzzleHttp\Exception\ServerException $e ) {
            $this->fail("Got 5XX HTTP code");
        }
    }
}
