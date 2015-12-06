<?php namespace Waavi\Translation\Test\Middleware;

use Waavi\Translation\Test\TestCase;

class TranslationMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_redirect_to_default_if_no_locale()
    {
        $response   = $this->call('GET', '/');
        $statusCode = $response->getStatusCode();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->has('location'));
        $this->assertEquals('http://localhost/en', $response->headers->get('location'));
    }

    /**
     * @test
     */
    public function it_will_redirect_to_browser_locale_before_default()
    {
        $response   = $this->call('GET', '/', [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'es']);
        $statusCode = $response->getStatusCode();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->has('location'));
        $this->assertEquals('http://localhost/es', $response->headers->get('location'));
    }

    /**
     * @test
     */
    public function it_will_redirect_if_invalid_locale()
    {
        $response   = $this->call('GET', '/ca');
        $statusCode = $response->getStatusCode();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->has('location'));
        $this->assertEquals('http://localhost/en/ca', $response->headers->get('location'));
    }

    /**
     * @test
     */
    public function it_will_not_redirect_if_valid_locale()
    {
        $response   = $this->call('GET', '/es');
        $statusCode = $response->getStatusCode();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hola mundo', $response->getContent());
    }

    /**
     *  @test
     */
    public function it_will_ignore_post_requests()
    {
        $response   = $this->call('POST', '/');
        $statusCode = $response->getStatusCode();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('POST answer', $response->getContent());
    }
}
