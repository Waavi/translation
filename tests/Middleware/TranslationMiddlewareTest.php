<?php
namespace Waavi\Translation\Test\Middleware;

use Waavi\Translation\Repositories\TranslationRepository;
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

    /**
     *  @test
     */
    public function it_sets_the_app_locale()
    {
        $response = $this->call('GET', '/en/locale');
        $this->assertEquals('en', $response->getContent());
        $response = $this->call('GET', '/es/locale');
        $this->assertEquals('es', $response->getContent());
    }

    /**
     *  @test
     */
    public function it_detects_the_app_locale_in_custom_segment()
    {
        $response = $this->call('GET', '/api/v1/en/locale');
        $this->assertEquals('en', $response->getContent());
        $response = $this->call('GET', '/api/v1/es/locale');
        $this->assertEquals('es', $response->getContent());
    }

    /**
     * @test
     */
    public function it_redirects_invalid_locale_in_custom_segment()
    {
        $response   = $this->call('GET', '/api/v1/ca/locale');
        $statusCode = $response->getStatusCode();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->has('location'));
        $this->assertEquals('http://localhost/api/v1/en/ca/locale', $response->headers->get('location'));
    }

    /**
     * @test
     */
    public function it_keeps_locale_in_post_requests_with_no_locale_set()
    {
        $translationRepository = \App::make(TranslationRepository::class);
        $trans                 = $translationRepository->create([
            'locale'    => 'en',
            'namespace' => '*',
            'group'     => 'welcome',
            'item'      => 'title',
            'text'      => 'Welcome',
        ]);

        $trans = $translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'welcome',
            'item'      => 'title',
            'text'      => 'Bienvenido',
        ]);

        $this->call('GET', '/es');
        $response   = $this->call('POST', '/welcome');
        $statusCode = $response->getStatusCode();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Bienvenido', $response->getContent());

        $this->call('GET', '/en');
        $response   = $this->call('POST', '/welcome');
        $statusCode = $response->getStatusCode();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Welcome', $response->getContent());
    }
}
