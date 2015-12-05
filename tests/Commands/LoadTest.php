<?php namespace Waavi\Translation\Test\Commands;

use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;
use Waavi\Translation\Test\TestCase;

class LoadTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->languageRepository    = App::make(LanguageRepository::class);
        $this->translationRepository = App::make(TranslationRepository::class);
    }

    /**
     * @test
     */
    public function it_loads_files_into_database()
    {
        Artisan::call('translator:load');
        $this->assertTrue(false);
    }

    /**
     * @test
     */
    public function it_loads_files_in_subdirectories_into_database()
    {

    }

    /**
     * @test
     */
    public function it_doesnt_load_undefined_locales()
    {

    }

    /**
     * @test
     */
    public function it_loads_overwritten_vendor_files_correctly()
    {

    }
}
