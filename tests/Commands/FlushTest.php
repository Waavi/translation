<?php namespace Waavi\Translation\Test\Commands;

use Waavi\Translation\Commands\CacheFlushCommand;
use Waavi\Translation\Test\TestCase;

class FlushTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $cacheStore      = $this->app['cache']->getStore();
        $cacheRepository = CacheRepositoryFactory::make($cacheStore, $this->app['config']->get('translator.cache.suffix'));
        $this->command   = new CacheFlushCommand($cacheRepository, $this->app['config']->get('translator.cache.enabled'));
    }

    /**
     * @test
     */
    public function it_loads_files_into_database()
    {
        $this->assertTrue(false);
    }
}
