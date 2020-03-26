<?php


class AuthTest extends PHPUnit\Framework\TestCase {

    public function testAuth(){

        $url = \PHP7API\App\Config::config('url', 'environment');

        $response = getResponseFromCurl($url, [
            'route' => 'welcome',
            'route_1' => 'test',
        ], getAuthorizationHeader());

        $this->assertNotEquals([
            'success' => 0,
            'message' => 'unauthorized'
        ], $response);

    }

    public function testNotAuthorized(){

        $url = \PHP7API\App\Config::config('url', 'environment');

        $response = getResponseFromCurl($url, [
            'route' => 'unknown_route',
        ]);
        $type = \PHP7API\App\Config::config('type', 'auth');
        if (strtolower($type) == 'none'):
            $this->assertNotEquals([
                'success' => 0,
                'message' => 'unauthorized'
            ], $response);
        else:
            $this->assertEquals([
                'success' => 0,
                'message' => 'unauthorized'
            ], $response);
        endif;


    }
}
