<?php namespace Waavi\Translation\Test\Cache;

use Illuminate\Cache\ArrayStore;
use Waavi\Translation\Cache\TaggedRepository;
use Waavi\Translation\Test\TestCase;

class TaggedRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        // During the parent's setup, both a 'es' 'Spanish' and 'en' 'English' languages are inserted into the database.
        parent::setUp();
        $this->repo = new TaggedRepository(new ArrayStore, 'translation');
    }

    /**
     * @test
     */
    public function has_returns_false_when_no_entry_present()
    {
        $this->assertFalse($this->repo->has('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function has_returns_true_if_entry_present()
    {
        $this->repo->put('en', 'namespace', 'group', 'value', 60);
        $this->assertTrue($this->repo->has('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function get_returns_null_if_empty()
    {
        $this->assertNull($this->repo->get('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function get_return_content_if_hit()
    {
        $this->repo->put('en', 'namespace', 'group', 'value', 60);
        $this->assertEquals('value', $this->repo->get('en', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_flush_removes_just_the_group()
    {
        $this->repo->put('en', 'namespace', 'group', 'value', 60);
        $this->repo->put('es', 'namespace', 'group', 'valor', 60);
        $this->repo->flush('en', 'namespace', 'group');
        $this->assertNull($this->repo->get('en', 'namespace', 'group'));
        $this->assertEquals('valor', $this->repo->get('es', 'namespace', 'group'));
    }

    /**
     * @test
     */
    public function test_flush_all_removes_all()
    {
        $this->repo->put('en', 'namespace', 'group', 'value', 60);
        $this->repo->put('es', 'namespace', 'group', 'value', 60);
        $this->repo->flushAll();
        $this->assertNull($this->repo->get('en', 'namespace', 'group'));
        $this->assertNull($this->repo->get('es', 'namespace', 'group'));
    }
}
