<?php namespace Waavi\Translation\Test\Localizer;

// PHPUnit wrappers:
use UriLocalizer;
use Waavi\Translation\Test\TestCase;

class LocalizeUriTest extends TestCase
{
    /**
     * @test
     */
    public function test_home_no_locale()
    {
        $this->assertEquals('/es', UriLocalizer::localize('/', 'es'));
        $this->assertEquals('/es', UriLocalizer::localize('', 'es'));
    }

    /**
     * @test
     */
    public function test_home_with_locale()
    {
        $this->assertEquals('/es', UriLocalizer::localize('/en', 'es'));
        $this->assertEquals('/es', UriLocalizer::localize('en', 'es'));
    }

    /**
     * @test
     */
    public function test_random_page_no_locale()
    {
        $this->assertEquals('/es/random', UriLocalizer::localize('/random', 'es'));
        $this->assertEquals('/es/random', UriLocalizer::localize('random', 'es'));
        $this->assertEquals('/es/random', UriLocalizer::localize('/random/', 'es'));
        $this->assertEquals('/es/random', UriLocalizer::localize('random/', 'es'));
    }

    /**
     * @test
     */
    public function test_random_page_with_locale()
    {
        $this->assertEquals('/es/random', UriLocalizer::localize('/en/random', 'es'));
        $this->assertEquals('/es/random', UriLocalizer::localize('en/random', 'es'));
        $this->assertEquals('/es/random', UriLocalizer::localize('/en/random/', 'es'));
        $this->assertEquals('/es/random', UriLocalizer::localize('en/random/', 'es'));
    }

    /**
     * @test
     */
    public function it_ignores_unexesting_locales()
    {
        $this->assertEquals('/es/ca/random', UriLocalizer::localize('/ca/random', 'es'));
    }

    /**
     * @test
     */
    public function it_maintains_get_parameters()
    {
        $this->assertEquals('/es/random?param1=value1&param2=', UriLocalizer::localize('random?param1=value1&param2=', 'es'));
    }

    /**
     * @test
     */
    public function it_localizes_when_locale_is_not_first()
    {
        $this->assertEquals('/api/es/random', UriLocalizer::localize('api/random', 'es', 1));
        $this->assertEquals('/api/es/random', UriLocalizer::localize('api/en/random', 'es', 1));
    }
}
