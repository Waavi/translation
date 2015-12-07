<?php namespace Waavi\Translation\Test\Loaders;

use Waavi\Translation\Loaders\DatabaseLoader;
use Waavi\Translation\Loaders\FileLoader;
use Waavi\Translation\Loaders\MixedLoader;
use Waavi\Translation\Test\TestCase;
use \Mockery;

class MixedLoaderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->fileLoader  = Mockery::mock(FileLoader::class);
        $this->dbLoader    = Mockery::mock(DatabaseLoader::class);
        $this->mixedLoader = new MixedLoader('en', $this->fileLoader, $this->dbLoader);
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_merges_file_and_db()
    {
        $file = [
            'in.file' => 'File',
            'no.db'   => 'No database',
        ];
        $db = [
            'in.file' => 'Database',
            'no.file' => 'No file',
        ];
        $expected = [
            'in.file' => 'File',
            'no.db'   => 'No database',
            'no.file' => 'No file',
        ];
        $this->fileLoader->shouldReceive('loadSource')->with('en', 'group', 'name')->andReturn($file);
        $this->dbLoader->shouldReceive('loadSource')->with('en', 'group', 'name')->andReturn($db);
        $this->assertEquals($expected, $this->mixedLoader->load('en', 'group', 'name'));
    }
}
