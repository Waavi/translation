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
        $this->assertNotNull($this->languageRepository->create(['locale' => 'ca', 'name' => 'Catalan']));
    }

    /**
     * @test
     */
    public function test_has_table()
    {
        $this->assertTrue($this->languageRepository->tableExists());
    }

    /**
     * @test
     */
    public function test_create_disallows_duplicate_locale()
    {
        $this->assertNull($this->languageRepository->create(['locale' => 'en', 'name' => 'Catalan']));
    }

    /**
     * @test
     */
    public function test_create_disallows_duplicate_name()
    {
        $this->assertNull($this->languageRepository->create(['locale' => 'ca', 'name' => 'English']));
    }

    /**
     * @test
     */
    public function test_can_update()
    {
        $this->assertTrue($this->languageRepository->update(['id' => 1, 'locale' => 'ens', 'name' => 'Englishs']));
        $lang = $this->languageRepository->find(1);
        $this->assertEquals('ens', $lang->locale);
        $this->assertEquals('Englishs', $lang->name);
    }

    /**
     * @test
     */
    public function test_update_disallows_duplicate_locale()
    {
        $this->assertFalse($this->languageRepository->update(['id' => 1, 'locale' => 'es', 'name' => 'Englishs']));
    }

    /**
     * @test
     */
    public function test_update_disallows_duplicate_name()
    {
        $this->assertFalse($this->languageRepository->update(['id' => 1, 'locale' => 'ens', 'name' => 'Spanish']));
    }

    /**
     * @test
     */
    public function it_can_delete()
    {
        $this->languageRepository->delete(2);
        $this->assertEquals(1, $this->languageRepository->all()->count());
    }

    /**
     * @test
     */
    public function it_can_restore()
    {
        $this->languageRepository->delete(2);
        $this->assertEquals(1, $this->languageRepository->all()->count());
        $this->languageRepository->restore(2);
        $this->assertEquals(2, $this->languageRepository->all()->count());
    }

    /**
     * @test
     */
    public function it_can_find_by_locale()
    {
        $language = $this->languageRepository->findByLocale('es');
        $this->assertNotNull($language);
        $this->assertEquals('es', $language->locale);
        $this->assertEquals('Spanish', $language->name);
    }

    /**
     * @test
     */
    public function it_can_find_trashed_by_locale()
    {
        $this->languageRepository->delete(2);
        $language = $this->languageRepository->findTrashedByLocale('es');
        $this->assertNotNull($language);
        $this->assertEquals('es', $language->locale);
        $this->assertEquals('Spanish', $language->name);
    }

    /**
     * @test
     */
    public function it_can_find_all_except_one()
    {
        $this->languageRepository->create(['locale' => 'ca', 'name' => 'Catalan']);
        $languages = $this->languageRepository->allExcept('es');
        $this->assertNotNull($languages);
        $this->assertEquals(2, $languages->count());

        $this->assertEquals('en', $languages[0]->locale);
        $this->assertEquals('English', $languages[0]->name);
        $this->assertEquals('ca', $languages[1]->locale);
        $this->assertEquals('Catalan', $languages[1]->name);
    }

    /**
     * @test
     */
    public function it_can_get_a_list_of_all_available_locales()
    {
        $this->assertEquals(['en', 'es'], $this->languageRepository->availableLocales());
    }

    /**
     * @test
     */
    public function it_can_check_a_locale_exists()
    {
        $this->assertTrue($this->languageRepository->isValidLocale('es'));
        $this->assertFalse($this->languageRepository->isValidLocale('ca'));
    }

    /**
     * @test
     */
    public function it_can_calculate_the_percent_translated()
    {
        $this->assertEquals(0, $this->languageRepository->percentTranslated('es'));

        $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->translationRepository->create([
            'locale'    => 'en',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->translationRepository->create([
            'locale'    => 'en',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item2',
            'text'      => 'text',
        ]);

        $this->assertEquals(50, $this->languageRepository->percentTranslated('es'));
        $this->assertEquals(100, $this->languageRepository->percentTranslated('en'));
    }
}
