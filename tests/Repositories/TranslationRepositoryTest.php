<?php namespace Waavi\Translation\Test\Repositories;

use Waavi\Translation\Models\Translation;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;
use Waavi\Translation\Test\TestCase;

class TranslationRepositoryTest extends TestCase
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
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);

        $this->assertTrue($translation->exists());

        $this->assertEquals('es', $translation->locale);
        $this->assertEquals('*', $translation->namespace);
        $this->assertEquals('group', $translation->group);
        $this->assertEquals('item', $translation->item);
        $this->assertEquals('text', $translation->text);
    }

    /**
     * @test
     */
    public function test_namespace_is_required()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->assertNull($translation);
    }

    /**
     * @test
     */
    public function test_locale_is_required()
    {
        $translation = $this->translationRepository->create([
            'locale'    => '',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->assertNull($translation);
    }

    /**
     * @test
     */
    public function test_group_is_required()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => '',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->assertNull($translation);
    }

    /**
     * @test
     */
    public function test_item_is_required()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => '',
            'text'      => 'text',
        ]);
        $this->assertNull($translation);
    }

    /**
     * @test
     */
    public function test_text_not_required()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => '',
        ]);
        $this->assertNotNull($translation);
        $this->assertTrue($translation->exists());
    }

    /**
     * @test
     */
    public function test_cannot_repeat_same_code_on_same_language()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->assertNotNull($translation);
        $this->assertTrue($translation->exists());

        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $this->assertNull($translation);
    }

    /**
     * @test
     */
    public function test_update_works()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);

        $this->assertTrue($this->translationRepository->update($translation->id, 'new text'));

        $translation = $this->translationRepository->find($translation->id);

        $this->assertNotNull($translation);
        $this->assertEquals('new text', $translation->text);
        $this->assertFalse($translation->isLocked());
    }

    /**
     * @test
     */
    public function test_update_and_lock()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);

        $this->assertTrue($this->translationRepository->updateAndLock($translation->id, 'new text'));

        $translation = $this->translationRepository->find($translation->id);

        $this->assertNotNull($translation);
        $this->assertEquals('new text', $translation->text);
        $this->assertTrue($translation->isLocked());
    }

    /**
     * @test
     */
    public function test_update_fails_if_lock()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $translation->lock();
        $translation->save();

        $this->assertFalse($this->translationRepository->update($translation->id, 'new text'));
    }

    /**
     * @test
     */
    public function test_force_update()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $translation->lock();
        $translation->save();

        $this->assertTrue($this->translationRepository->updateAndLock($translation->id, 'new text'));

        $translation = $this->translationRepository->find($translation->id);

        $this->assertNotNull($translation);
        $this->assertEquals('new text', $translation->text);
        $this->assertTrue($translation->isLocked());
    }

    /**
     * @test
     */
    public function test_delete()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $translation2 = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item2',
            'text'      => 'text',
        ]);
        $this->assertEquals(2, $this->translationRepository->count());
        $this->translationRepository->delete($translation->id);
        $this->assertEquals(1, $this->translationRepository->count());
    }

    /**
     * @test
     */
    public function it_deletes_other_locales_if_default()
    {
        $translation = $this->translationRepository->create([
            'locale'    => 'en',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $translation2 = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item',
            'text'      => 'text',
        ]);
        $translation3 = $this->translationRepository->create([
            'locale'    => 'es',
            'namespace' => '*',
            'group'     => 'group',
            'item'      => 'item2',
            'text'      => 'text',
        ]);
        $this->assertEquals(3, $this->translationRepository->count());
        $this->translationRepository->delete($translation->id);
        $this->assertEquals(1, $this->translationRepository->count());
    }

    /**
     * @test
     */
    public function it_loads_arrays()
    {
        $array = [
            'simple' => 'Simple',
            'group'  => [
                'item' => 'Item',
                'meti' => 'metI',
            ],
        ];
        $this->translationRepository->loadArray($array, 'en', 'file');

        $translations = $this->translationRepository->all();

        $this->assertEquals(3, $translations->count());

        $this->assertEquals('en', $translations[0]->locale);
        $this->assertEquals('*', $translations[0]->namespace);
        $this->assertEquals('file', $translations[0]->group);
        $this->assertEquals('simple', $translations[0]->item);
        $this->assertEquals('Simple', $translations[0]->text);

        $this->assertEquals('en', $translations[1]->locale);
        $this->assertEquals('*', $translations[1]->namespace);
        $this->assertEquals('file', $translations[1]->group);
        $this->assertEquals('group.item', $translations[1]->item);
        $this->assertEquals('Item', $translations[1]->text);

        $this->assertEquals('en', $translations[2]->locale);
        $this->assertEquals('*', $translations[2]->namespace);
        $this->assertEquals('file', $translations[2]->group);
        $this->assertEquals('group.meti', $translations[2]->item);
        $this->assertEquals('metI', $translations[2]->text);
    }

    /**
     * @test
     */
    public function load_arrays_does_not_overwrite_locked_translations()
    {
        $array = [
            'simple' => 'Simple',
            'group'  => [
                'item' => 'Item',
                'meti' => 'metI',
            ],
        ];
        $this->translationRepository->loadArray($array, 'en', 'file');
        $this->translationRepository->updateAndLock(1, 'Complex');
        $this->translationRepository->loadArray($array, 'en', 'file');

        $translations = $this->translationRepository->all();

        $this->assertEquals(3, $translations->count());

        $this->assertEquals('en', $translations[0]->locale);
        $this->assertEquals('*', $translations[0]->namespace);
        $this->assertEquals('file', $translations[0]->group);
        $this->assertEquals('simple', $translations[0]->item);
        $this->assertEquals('Complex', $translations[0]->text);
    }

    /**
     * @test
     */
    public function it_picks_a_random_untranslated_entry()
    {
        $array = ['simple' => 'Simple'];
        $this->translationRepository->loadArray($array, 'en', 'file');

        $translation = $this->translationRepository->randomUntranslated('es');
        $this->assertNotNull($translation);
    }

    /**
     * @test
     */
    public function it_lists_all_untranslated_entries()
    {
        $array = ['simple' => 'Simple', 'complex' => 'Complex'];
        $this->translationRepository->loadArray($array, 'en', 'file');
        $array = ['simple' => 'Simple'];
        $this->translationRepository->loadArray($array, 'es', 'file');

        $translations = $this->translationRepository->untranslated('es');
        $this->assertNotNull($translations);
        $this->assertEquals(1, $translations->count());
        $this->assertEquals('Complex', $translations[0]->text);
    }

    /**
     * @test
     */
    public function it_finds_by_code()
    {
        $array = ['simple' => 'Simple', 'complex' => 'Complex'];
        $this->translationRepository->loadArray($array, 'en', 'file');
        $translation = $this->translationRepository->findByCode('en', '*', 'file', 'complex');
        $this->assertNotNull($translation);
        $this->assertEquals('Complex', $translation->text);
    }

    /**
     * @test
     */
    public function it_gets_all_items_in_a_group()
    {
        $array = ['simple' => 'Simple', 'complex' => 'Complex'];
        $this->translationRepository->loadArray($array, 'en', 'file');
        $array = ['test2' => 'test'];
        $this->translationRepository->loadArray($array, 'en', 'file2');

        $translations = $this->translationRepository->getItems('en', '*', 'file');
        $this->assertNotNull($translations);
        $this->assertEquals(2, count($translations));
        $this->assertEquals('simple', $translations[1]['item']);
        $this->assertEquals('Simple', $translations[1]['text']);
        $this->assertEquals('complex', $translations[0]['item']);
        $this->assertEquals('Complex', $translations[0]['text']);
    }

    /**
     * @test
     */
    public function it_flag_as_unstable()
    {
        $array = ['simple' => 'Simple', 'complex' => 'Complex'];
        $this->translationRepository->loadArray($array, 'es', 'file');

        $this->translationRepository->flagAsUnstable('*', 'file', 'complex');

        $translations = $this->translationRepository->pendingReview('es');
        $this->assertEquals(1, $translations->count());
        $this->assertEquals('Complex', $translations[0]->text);
    }

    /**
     * @test
     */
    public function it_searches_by_code_fragment()
    {
        $array = ['simple' => 'Simple', 'complex' => 'Complex'];
        $this->translationRepository->loadArray($array, 'es', 'file', 'namespace');
        $array = ['test' => '2', 'hhh' => 'Juan'];
        $this->translationRepository->loadArray($array, 'es', 'fichero');

        $this->assertEquals(2, $this->translationRepository->search('es', 'space::')->count());
        $this->assertEquals(1, $this->translationRepository->search('es', 'Juan')->count());
        $this->assertEquals(1, $this->translationRepository->search('es', 'st.2')->count());
        $this->assertEquals(0, $this->translationRepository->search('es', 'ple.2')->count());
    }

    /**
     * @test
     */
    public function it_translates_text()
    {
        $array = ['lang' => 'Castellano', 'multi' => 'Multiple', 'multi2' => 'Multiple'];
        $this->translationRepository->loadArray($array, 'es', 'file');
        $array = ['lang' => 'English', 'other' => 'Random', 'multi' => 'Multi', 'multi2' => 'Many'];
        $this->translationRepository->loadArray($array, 'en', 'file');

        $this->assertEquals(['Castellano'], $this->translationRepository->translateText('English', 'en', 'es'));
        $this->assertEquals(['English'], $this->translationRepository->translateText('Castellano', 'es', 'en'));
        $this->assertEquals([], $this->translationRepository->translateText('Complex', 'en', 'es'));
        $this->assertEquals(['Multi', 'Many'], $this->translationRepository->translateText('Multiple', 'es', 'en'));
    }

    /**
     * @test
     */
    public function test_flag_as_reviewed()
    {
        $array = ['simple' => 'Simple', 'complex' => 'Complex'];
        $this->translationRepository->loadArray($array, 'es', 'file');

        $this->translationRepository->flagAsUnstable('*', 'file', 'complex');
        $translations = $this->translationRepository->pendingReview('es');
        $this->assertEquals(1, $translations->count());
        $this->translationRepository->flagAsReviewed(2);
        $translations = $this->translationRepository->pendingReview('es');
        $this->assertEquals(0, $translations->count());
    }
}
