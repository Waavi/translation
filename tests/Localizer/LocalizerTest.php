<?php namespace Waavi\Translation\Test\Localizer;

use Waavi\Translation\Test\TestCase;

class LocalizerTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->spanish = factory(App\Translator\Models\Language::class)->create(['locale' => 'ep']);
    }

    /**
     *    We can insert new entries.
     */
    public function test_insert_works()
    {
        $this->english = factory(App\Translator\Models\Language::class)->create(['locale' => 'en']);

        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);

        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->english->id, 'grupo', 'elemento', 'Texto', '*');
        $this->assertTrue($entry->exists);
    }

    public function test_namespace_not_required_defaults_to_asterisk()
    {
        Event::shouldReceive('fire')->once()->with('translation.new', Mockery::any());
        $entry = Translator::insert($this->spanish->id, 'grupo', 'elemento', 'Texto');
        $this->assertTrue($entry->errors()->isEmpty());
        $this->assertTrue($entry->exists);
        Assert::equals('*', $entry->namespace);
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
}
