<?php namespace Waavi\Translation\Test\Middleware;

use Waavi\Translation\Test\TestCase;

class TranslationMiddlewareTest extends TestCase
{
    public function test_extract_locale()
    {
        $candidates = ['es', 'bullshit'];
        $locale     = Translator::extractFirstValidLocale($candidates);
        Assert::equals('es', $locale);

        $candidates = ['crap', 'en', 'bullshit'];
        $locale     = Translator::extractFirstValidLocale($candidates);
        Assert::equals('en', $locale);
    }

    public function testReturnsFirstIfTwoValid()
    {
        $candidates = ['crap', 'en', 'es', 'bullshit'];
        $locale     = Translator::extractFirstValidLocale($candidates);
        Assert::equals('en', $locale);
    }

    public function testReturnsNullIfNoneValid()
    {
        $candidates = ['crap', 'bullshit'];
        $locale     = Translator::extractFirstValidLocale($candidates);
        Assert::null($locale);
    }
}
