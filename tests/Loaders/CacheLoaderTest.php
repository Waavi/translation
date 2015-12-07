<?php namespace Waavi\Translation\Test\Loaders;

use Illuminate\Cache\Repository as Cache;
use Waavi\Translation\Loaders\CacheLoader;
use Waavi\Translation\Loaders\Loader;
use Waavi\Translation\Test\TestCase;
use \Mockery;

class CacheLoaderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->cache       = Mockery::mock(Cache::class);
        $this->fallback    = Mockery::mock(Loader::class);
        $this->cacheLoader = new CacheLoader('en', $this->cache, $this->fallback, 60, 'translation');
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_returns_from_cache_if_hit()
    {
        $key = $this->cacheLoader->generateCacheKey('en', 'group', 'name');
        $this->cache->shouldReceive('has')->with($key)->once()->andReturn(true);
        $this->cache->shouldReceive('get')->with($key)->once()->andReturn('cache hit');
        $this->assertEquals('cache hit', $this->cacheLoader->loadSource('en', 'group', 'name'));
    }

    /**
     * @test
     */
    public function it_returns_from_fallback_and_stores_in_cache_if_miss()
    {
        $key = $this->cacheLoader->generateCacheKey('en', 'group', 'name');
        $this->cache->shouldReceive('has')->with($key)->once()->andReturn(false);
        $this->fallback->shouldReceive('load')->with('en', 'group', 'name')->once()->andReturn('cache miss');
        $this->cache->shouldReceive('put')->with($key, 'cache miss', 60)->once()->andReturn(true);
        $this->assertEquals('cache miss', $this->cacheLoader->loadSource('en', 'group', 'name'));
    }
}
