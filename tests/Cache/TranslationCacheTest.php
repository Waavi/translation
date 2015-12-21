<?php namespace Waavi\Translation\Test\Cache;

use Waavi\Translation\Test\TestCase;

class TranslationCacheTest extends TestCase
{
    public function setUp()
    {
        // During the parent's setup, both a 'es' 'Spanish' and 'en' 'English' languages are inserted into the database.
        parent::setUp();
    }

    /**
     * @test
     */
    public function test_has_with_no_entry()
    {
        $this->assertFalse(\TranslationCache::has('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_has_returns_true_if_entry()
    {
        \TranslationCache::put('en', 'namespace', 'group', 'key', 'value');
        $this->assertTrue(\TranslationCache::has('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_get_returns_null_if_empty()
    {
        $this->assertNull(\TranslationCache::get('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_get_return_content_if_hit()
    {
        \TranslationCache::put('en', 'namespace', 'group', 'value', 60);
        $this->assertEquals('value', \TranslationCache::get('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_flush_removes_just_the_group()
    {
        \TranslationCache::put('en', 'namespace', 'group', 'value', 60);
        \TranslationCache::put('es', 'namespace', 'group', 'valor', 60);
        \TranslationCache::flush('en', 'namespace', 'group');
        $this->assertNull(\TranslationCache::get('en', 'namespace', 'group'));
        $this->assertEquals('valor', \TranslationCache::get('es', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_flush_all_removes_all()
    {
        \TranslationCache::put('en', 'namespace', 'group', 'value', 60);
        \TranslationCache::put('es', 'namespace', 'group', 'value', 60);
        \TranslationCache::flushAll();
        $this->assertNull(\TranslationCache::get('en', 'namespace', 'group'));
        $this->assertNull(\TranslationCache::get('es', 'namespace', 'group'));
    }
}
