<?php namespace Waavi\Translation\Test\Cache;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore;
use Waavi\Translation\Cache\RepositoryFactory;
use Waavi\Translation\Cache\SimpleRepository;
use Waavi\Translation\Cache\TaggedRepository;
use Waavi\Translation\Test\TestCase;

class RepositoryFactoryTest extends TestCase
{
    public function setUp()
    {
        // During the parent's setup, both a 'es' 'Spanish' and 'en' 'English' languages are inserted into the database.
        parent::setUp();
    }

    /**
     * @test
     */
    public function test_returns_simple_cache_if_non_taggable_store()
    {
        $store = new FileStore(\App::make('files'), __DIR__ . '/temp');
        $repo  = RepositoryFactory::make($store, 'translation');
        $this->assertEquals(SimpleRepository::class, get_class($repo));
    }

    /**
     * @test
     */
    public function test_returns_simple_cache_if_taggable_store()
    {
        $store = new ArrayStore;
        $repo  = RepositoryFactory::make($store, 'translation');
        $this->assertEquals(TaggedRepository::class, get_class($repo));
    }
}
