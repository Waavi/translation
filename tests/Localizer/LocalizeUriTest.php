<?php namespace Waavi\Translation\Test\Localizer;

// PHPUnit wrappers:
use Waavi\Translation\Test\TestCase;

class LocalizeUriTest extends TestCase
{

    /**
     *  Test the localize uri method works with the homepage:
     *
     *    @return void
     */
    public function testHomeWithSlash()
    {
        $result = Translator::localizeUri('es', '/');
        Assert::equals($result, '/es');
    }

    /**
     *  Test the localize uri method works with the homepage:
     *
     *    @return void
     */
    public function testHomeWithoutSlash()
    {
        $result = Translator::localizeUri('es', '');
        Assert::equals($result, '/es');
    }

    /**
     *  Test the localize uri method works with the homepage:
     *
     *    @return void
     */
    public function testWithoutSlash()
    {
        $result = Translator::localizeUri('es', 'my-profile');
        Assert::equals('/es/my-profile', $result);
    }

    /**
     *  Test that it works with uris starting with a trailing slash
     *
     *    @return void
     */
    public function testWithSlash()
    {
        $result = Translator::localizeUri('es', 'my-profile');
        Assert::equals('/es/my-profile', $result);
    }

    /**
     *  Test that it works with uris starting with a trailing slash
     *
     *    @return void
     */
    public function testWithTrailingSlash()
    {
        $result = Translator::localizeUri('es', 'my-profile/');
        Assert::equals('/es/my-profile', $result);
    }

    /**
     *  Test that it works with uris starting with a trailing slash
     *
     *    @return void
     */
    public function testWithLocale()
    {
        $result = Translator::localizeUri('es', 'en/my-profile/');
        Assert::equals('/es/my-profile', $result);
    }

    /**
     *  Test that it works with uris starting with a trailing slash
     *
     *    @return void
     */
    public function testWithLocaleAndSlash()
    {
        $result = Translator::localizeUri('es', '/en/my-profile/');
        Assert::equals('/es/my-profile', $result);
    }

    /**
     *  Test that it works with uris starting with a trailing slash
     *
     *    @return void
     */
    public function testWithLocaleAndTrailingSlash()
    {
        $result = Translator::localizeUri('es', 'en/my-profile/yeah/');
        Assert::equals('/es/my-profile/yeah', $result);
    }

    /**
     *  Test that it works with uris starting with a trailing slash
     *
     *    @return void
     */
    public function testWithGetParameters()
    {
        $result = Translator::localizeUri('es', 'my-profile?name=paco&jsurname=');
        Assert::equals('/es/my-profile?name=paco&jsurname=', $result);
    }
}
