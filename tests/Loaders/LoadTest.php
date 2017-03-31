<?php namespace Waavi\Translation\Test\Loaders;

use Illuminate\Translation\FileLoader as LaravelFileLoader;
use Waavi\Translation\Loaders\FileLoader;
use Waavi\Translation\Test\TestCase;
use \Mockery;

class LoadTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->laravelLoader = Mockery::mock(LaravelFileLoader::class);
        // We will use the file loader:
        $this->fileLoader = new FileLoader('en', $this->laravelLoader);
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_merges_default_and_target_locales()
    {
        $en = [
            'simple' => 'Simple',
            'nested' => [
                'one' => 'First',
                'two' => 'Second',
            ],
        ];
        $es = [
            'simple' => 'OverSimple',
            'nested' => [
                'one' => 'OverFirst',
            ],
        ];
        $expected = [
            'simple' => 'OverSimple',
            'nested' => [
                'one' => 'OverFirst',
                'two' => 'Second',
            ],
        ];
        $this->laravelLoader->shouldReceive('load')->with('en', 'group', 'name')->andReturn($en);
        $this->laravelLoader->shouldReceive('load')->with('es', 'group', 'name')->andReturn($es);
        $this->assertEquals($expected, $this->fileLoader->load('es', 'group', 'name'));
    }

    /**
     *  @test
     */
    public function it_returns_translation_code_if_text_not_found()
    {
        $this->assertEquals('auth.code', trans('auth.code'));
    }
}
