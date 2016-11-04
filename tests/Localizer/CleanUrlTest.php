<?php namespace Waavi\Translation\Test\Localizer;

use UriLocalizer;
use Waavi\Translation\Test\TestCase;

class CleanUrlTest extends TestCase
{
    /**
     * @test
     */
    public function it_cleans_empty_url()
    {
        $this->assertEquals('/', UriLocalizer::cleanUrl(''));
        $this->assertEquals('/', UriLocalizer::cleanUrl('/'));
    }

    /**
     * @test
     */
    public function it_cleans_uri()
    {
        $this->assertEquals('/random', UriLocalizer::cleanUrl('random/'));
    }

    /**
     * @test
     */
    public function it_cleans_http_url()
    {
        $this->assertEquals('/random', UriLocalizer::cleanUrl('http://domain.com/random/'));
    }

    /**
     * @test
     */
    public function it_cleans_https_url()
    {
        $this->assertEquals('/random', UriLocalizer::cleanUrl('https://domain.com/random/'));
    }

    /**
     * @test
     */
    public function it_keeps_query_string()
    {
        $this->assertEquals('/random?param=value&param=', UriLocalizer::cleanUrl('https://domain.com/random/?param=value&param='));
    }

    /**
     * @test
     */
    public function it_removes_locale_string()
    {
        $this->assertEquals('/random?param=value&param=', UriLocalizer::cleanUrl('https://domain.com/es/random/?param=value&param='));
    }

    /**
     * @test
     */
    public function it_removes_locale_string_in_custom_position()
    {
        $this->assertEquals('/api/random?param=value&param=', UriLocalizer::cleanUrl('https://domain.com/api/es/random/?param=value&param=', 1));
    }

    /**
     * @test
     */
    public function it_keeps_invalid_locale_string()
    {
        $this->assertEquals('/ca/random?param=value&param=', UriLocalizer::cleanUrl('https://domain.com/ca/random/?param=value&param='));
    }
}
