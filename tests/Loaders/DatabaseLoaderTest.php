<?php namespace Waavi\Translation\Test\Loaders;

use Waavi\Translation\Loaders\DatabaseLoader;
use Waavi\Translation\Repositories\TranslationRepository;
use Waavi\Translation\Test\TestCase;
use \Mockery;

class DatabaseLoaderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->repo   = Mockery::mock(TranslationRepository::class);
        $this->loader = new DatabaseLoader('en', $this->repo);
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_returns_from_database()
    {
        $data = [
            ['item' => 'one', 'text' => 'first'],
            ['item' => 'two', 'text' => 'second'],
        ];
        $expected = [
            'one' => 'first',
            'two' => 'second',
        ];
        $this->repo->shouldReceive('getItems')->with('en', 'name', 'group')->once()->andReturn($data);
        $results = $this->loader->loadSource('en', 'group', 'name');
        $this->assertEquals($expected, $results);
    }
}
