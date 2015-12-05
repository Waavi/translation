<?php namespace Waavi\Translation\Test\Traits;

use Waavi\Translation\Test\TestCase;

class TranslatableTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Schema::create('dummies', function ($table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('title_translation')->nullable();
            $table->string('slug')->nullable();
            $table->string('text')->nullable();
            $table->string('text_translation')->nullable();
            $table->timestamps();
        });
    }

    /**
     *    Check an entry is created when saving
     */
    public function test_it_works()
    {
        $dummy        = new Dummy();
        $dummy->title = 'Título del dummy';
        $dummy->text  = 'Texto del dummy';
        $saved        = $dummy->save() ? true : false;
        Assert::true($saved);
        Assert::equals(1, Dummy::count());
        // Check that there is a language entry in the database:
        $entries = App::make('App\Translator\Repositories\LanguageEntryRepository')->getByCode($dummy->title_translation);
        Assert::equals(1, $entries->count());
        Assert::equals('Título del dummy', $entries->first()->text);
        Assert::equals('Título del dummy', $dummy->title);
        Assert::equals('slug', $dummy->slug);
        $entries = App::make('App\Translator\Repositories\LanguageEntryRepository')->getByCode($dummy->text_translation);
        Assert::equals(1, $entries->count());
        Assert::equals('Texto del dummy', $entries->first()->text);
        Assert::equals('Texto del dummy', $dummy->text);
        // Delete it:
        $deleted = $dummy->delete();
        Assert::true($deleted);
        Assert::equals(0, Dummy::count());
        Assert::equals(0, LanguageEntry::count());
    }
}

class Dummy extends \Illuminate\Database\Eloquent\Model
{
    use \Waavi\Translation\Traits\Translatable;

    protected $fillable = ['title', 'text'];

    protected $translatableAttributes = ['title', 'text'];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug']  = 'slug';
    }
}
