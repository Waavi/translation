<?php namespace Waavi\Translation\Test\Repositories;

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
        $this->english = factory(App\Translator\Models\Language::class)->create(['locale' => 'en']);

        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->english->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);
    }

    public function test_language_is_required()
    {
        $this->setExpectedException('App\Utils\Exceptions\ValidatorException');
        $entry = Translator::insert(null, 'grupo', 'elemento', 'Texto');
    }

    public function test_group_is_required()
    {
        $this->setExpectedException('App\Utils\Exceptions\ValidatorException');
        $entry = Translator::insert($this->spanish->id, '', 'elemento', 'Texto');
    }

    public function test_item_is_required()
    {
        $this->setExpectedException('App\Utils\Exceptions\ValidatorException');
        $entry = Translator::insert($this->spanish->id, 'grupo', '', 'Texto');
    }

    public function test_text_not_required()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', '');
        $this->assertTrue($entry->exists);
    }

    /**
     *    Cannot insert if the code already exists for the given language
     */
    public function test_cannot_repeat_same_code_on_same_language()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->errors()->isEmpty());
        $this->assertTrue($entry->exists);

        $this->setExpectedException('App\Utils\Exceptions\ValidatorException');
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
    }

    /**
     *    Update.
     */
    public function test_update_works()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.updated', Mockery::any());
        $entry->setText('Nuevo Texto');
        $translation = Translator::translate($entry, $this->spanish->locale);
        Assert::equals('Nuevo Texto', $translation->text);
    }

    /**
     *    Test that entries can be forcefully updated.
     */
    public function test_update_and_lock()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->errors()->isEmpty());
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.updated', Mockery::any());
        $entry->setTextAndLock('Nuevo Texto');
        $translation = Translator::translate($entry, $this->spanish->locale);
        Assert::equals('Nuevo Texto', $translation->text);
    }

    /**
     *    Cannot update if locked
     */
    public function test_update_fails_if_lock()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->errors()->isEmpty());
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.updated', Mockery::any());
        $entry->setTextAndLock('Nuevo Texto');
        $translation = Translator::translate($entry, $this->spanish->locale);
        Assert::equals('Nuevo Texto', $translation->text);

        $this->setExpectedException('App\Utils\Exceptions\ValidatorException');
        $entry->setText('Texto Fallido');
    }

    /**
     *    Force update
     */
    public function test_force_update()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->errors()->isEmpty());
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.updated', Mockery::any());
        $entry->setTextAndLock('Nuevo Texto');
        $translation = Translator::translate($entry, $this->spanish->locale);
        Assert::equals('Nuevo Texto', $translation->text);

        Event::shouldReceive('fire')->once()->with('translation.updated', Mockery::any());
        $entry->setTextAndLock('Texto guay');
        $translation = Translator::translate($entry, $this->spanish->locale);
        Assert::equals('Texto guay', $translation->text);
    }

    /**
     *    Test records can be marked as reviewed.
     */
    public function test_flag_as_reviewed()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->errors()->isEmpty());
        $this->assertTrue($entry->exists);

        // Mark the entry as unstable:
        $entry->unstable = 1;
        $entry->save();

        // Now flag as reviewed:
        $entry->flagAsReviewed();
        $translation = Translator::translate($entry, $this->spanish->locale);
        Assert::equals(0, $translation->unstable);
    }

    public function test_siblings_unstable_if_default()
    {
        $this->asserFalse(true);
    }

    /**
     *    Test that we can get the translation for a given text and locale
     */
    public function test_translate()
    {
        $inSpanish   = factory(App\Translator\Models\LanguageEntry::class)->create(['language_id' => $this->spanish->id, 'group' => 'my-group', 'item' => 'my-item', 'text' => 'Oh Si!']);
        $inEnglish   = factory(App\Translator\Models\LanguageEntry::class)->create(['language_id' => $this->english->id, 'group' => 'my-group', 'item' => 'my-item', 'text' => 'Oh Yeah!']);
        $translation = Translator::translate($inSpanish, $this->english->locale);
        Assert::equals('Oh Yeah!', $translation->text);
    }

    /**
     *    Test that when asking to translate a code - language combination that doesn't exist, we get back the code.
     */
    public function test_translate_returns_original_if_translation_not_found()
    {
        $inSpanish   = factory(App\Translator\Models\LanguageEntry::class)->create(['language_id' => $this->spanish->id, 'text' => 'Oh Si!']);
        $translation = Translator::translate($inSpanish, $this->english->locale);
        Assert::equals('Oh Si!', $translation->text);
    }
}
