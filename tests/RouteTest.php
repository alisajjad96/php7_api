<?php


class RouteTest extends PHPUnit\Framework\TestCase {


    public function testWelcome(){

        $url = \PHP7API\App\Config::config('url', 'environment');

        $response = getResponseFromCurl($url, [
            'route' => 'welcome',
            'route_1' => 'test',
        ], getAuthorizationHeader());

        $this->assertEquals([
            'success' => 1,
            'message' => 'success'
        ], $response);

    }

    public function testRouteNotMatched(){

        $url = \PHP7API\App\Config::config('url', 'environment');

        $response = getResponseFromCurl($url, [
            'route' => 'unknown_route',
        ], getAuthorizationHeader());

        $this->assertEquals([
            'success' => 0,
            'message' => "Unknown Route"
        ], $response);
    }
}
