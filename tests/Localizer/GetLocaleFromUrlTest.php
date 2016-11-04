<?php namespace Waavi\Translation\Test\Localizer;

use UriLocalizer;
use Waavi\Translation\Test\TestCase;

class GetLocaleFromUrlTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_locale_from_url()
    {
        $this->assertEquals('es', UriLocalizer::getLocaleFromUrl('http://domain.com/es/random/'));
    }

    /**
     * @test
     */
    public function it_returns_locale_from_uri()
    {
        $this->assertEquals('es', UriLocalizer::getLocaleFromUrl('/es/random/'));
        $this->assertEquals('es', UriLocalizer::getLocaleFromUrl('es/random/'));
    }

    /**
     * @test
     */
    public function it_return_null_if_no_locale_found()
    {
        $this->assertNull(UriLocalizer::getLocaleFromUrl('/random/'));
        $this->assertNull(UriLocalizer::getLocaleFromUrl('ca/random/'));
    }

    /**
     * @test
     */
    public function it_returns_locale_from_url_in_custom_position()
    {
        $this->assertEquals('es', UriLocalizer::getLocaleFromUrl('http://domain.com/api/es/random/', 1));
    }
}
