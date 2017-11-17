<?php namespace Waavi\Translation\Test\Commands;

use Mockery;
use Waavi\Translation\Test\TestCase;

class FlushTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->cacheRepository = \App::make('translation.cache.repository');
    }

    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function it_does_nothing_if_cache_disabled()
    {
        $this->cacheRepository->put('en', 'group', 'namespace', 'value', 60);
        $this->assertTrue($this->cacheRepository->has('en', 'group', 'namespace'));
        $command = Mockery::mock('Waavi\Translation\Commands\CacheFlushCommand[info]', [$this->cacheRepository, false]);
        $command->shouldReceive('info')->with('The translation cache is disabled.')->once();
        $command->handle();
        $this->assertTrue($this->cacheRepository->has('en', 'group', 'namespace'));
    }

    /**
     * @test
     */
    public function it_flushes_the_cache()
    {
        $this->cacheRepository->put('en', 'group', 'namespace', 'value', 60);
        $this->assertTrue($this->cacheRepository->has('en', 'group', 'namespace'));
        $command = Mockery::mock('Waavi\Translation\Commands\CacheFlushCommand[info]', [$this->cacheRepository, true]);
        $command->shouldReceive('info')->with('Translation cache cleared.')->once();
        $command->handle();
        $this->assertFalse($this->cacheRepository->has('en', 'group', 'namespace'));
    }
}
