<?php namespace Waavi\Translation\Test\Repositories;

use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;
use Waavi\Translation\Test\TestCase;

class LanguageRepositoryTest extends TestCase
{
    public function setUp()
    {
        // During the parent's setup, both a 'es' 'Spanish' and 'en' 'English' languages are inserted into the database.
        parent::setUp();
        $this->languageRepository    = \App::make(LanguageRepository::class);
        $this->translationRepository = \App::make(TranslationRepository::class);
    }

    /**
     * @test
     */
    public function test_can_create()
    {
        $this->english = factory(App\Translator\Models\Language::class)->create(['locale' => 'en']);

        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->english->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);
    }

    /**
     * @test
     */
    public function test_create_disallows_duplicate_locale()
    {

    }

    /**
     * @test
     */
    public function test_create_disallows_duplicate_name()
    {

    }

    /**
     * @test
     */
    public function test_can_update()
    {

    }

    /**
     * @test
     */
    public function test_update_disallows_duplicate_locale()
    {

    }

    /**
     * @test
     */
    public function test_update_disallows_duplicate_name()
    {

    }
}
