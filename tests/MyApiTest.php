<?php
namespace Tests;

use Eafion\MyApi\App as App;
use Slim\Http\Environment;
use Slim\Http\Request;

class MyApiTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = (new App())->get();
    }

    /**
     * Test that shorten won't accept an empty url
     */
    public function testEmptyUrl()
    {
        $requestData = [ 'provider' => 'bitly', 'url' => '' ];

        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/shorten',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody($requestData);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $this->assertEquals(422, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertContains("url is missing", $result["message"]);
    }

    /**
     * Test that shorten won't accept a malformed url
     */
    public function testMalformedUrl()
    {
        $requestData = [ 'provider' => 'rebrandly', 'url' => 'malformedUrl' ];

        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/shorten',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody($requestData);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $this->assertEquals(422, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertContains("url is not valid", $result["message"]);
    }

    /**
     * Test that shorten will select default provider if not specified
     */
    public function testEmptyProvider()
    {
        $requestData = [ 'provider' => '', 'url' => 'https://www.youtube.com/watch?v=5_5q_vIgPkA' ];

        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/shorten',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody($requestData);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $this->assertEquals(201, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertContains("bitly", $result["provider_used"]);
        $this->assertContains("No known provider", $result["provider_requested"]);
    }

    /**
     * Test that created url is shorter than the one provided
     */
    public function testShortenUrl()
    {
        $requestData = [ 'provider' => '', 'url' => 'https://www.youtube.com/watch?v=mMd9CGIhqDY' ];

        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/shorten',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody($requestData);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $this->assertEquals(201, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertGreaterThan(strlen($result['shortened_url']), strlen($requestData['url']));
    }
}