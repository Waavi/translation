<?php namespace Waavi\Translation\Test\Cache;

use Illuminate\Cache\ArrayStore;
use Waavi\Translation\Cache\SimpleRepository;
use Waavi\Translation\Test\TestCase;

class SimpleRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        // During the parent's setup, both a 'es' 'Spanish' and 'en' 'English' languages are inserted into the database.
        parent::setUp();
        $this->repo = new SimpleRepository(new ArrayStore, 'translation');
    }

    /**
     * @test
     */
    public function test_has_with_no_entry()
    {
        $this->assertFalse($this->repo->has('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_has_returns_true_if_entry()
    {
        $this->repo->put('en', 'namespace', 'group', ['key' => 'value'], 60);
        $this->assertTrue($this->repo->has('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_get_returns_null_if_empty()
    {
        $this->assertNull($this->repo->get('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_get_return_content_if_hit()
    {
        $this->repo->put('en', 'namespace', 'group', 'value', 60);
        $this->assertEquals('value', $this->repo->get('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_flush_removes_all()
    {
        $this->repo->put('en', 'namespace', 'group', 'value', 60);
        $this->repo->put('es', 'namespace', 'group', 'valor', 60);
        $this->repo->flush('en', 'namespace', 'group');
        $this->assertNull($this->repo->get('en', 'namespace', 'group'));
        $this->assertNull($this->repo->get('es', 'namespace', 'group'));
    }
}
