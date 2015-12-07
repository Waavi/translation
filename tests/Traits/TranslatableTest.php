<?php namespace Waavi\Translation\Test\Traits;

use Illuminate\Database\Eloquent\Model;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;
use Waavi\Translation\Test\TestCase;
use Waavi\Translation\Traits\Translatable;

class TranslatableTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        \Schema::create('dummies', function ($table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('title_translation')->nullable();
            $table->string('slug')->nullable();
            $table->string('text')->nullable();
            $table->string('text_translation')->nullable();
            $table->timestamps();
        });
        $this->languageRepository    = \App::make(LanguageRepository::class);
        $this->translationRepository = \App::make(TranslationRepository::class);
    }

    /**
     *    Check an entry is created when saving
     */
    public function test_it_works()
    {
        $dummy        = new Dummy;
        $dummy->title = 'Título del dummy';
        $dummy->text  = 'Texto del dummy';
        $saved        = $dummy->save() ? true : false;
        $this->assertTrue($saved);
        $this->assertEquals(1, Dummy::count());
        // Check that there is a language entry in the database:
        $textTranslation  = $this->translationRepository->findByCode('en', 'translation', 'translatable', 'waavi.translation.test.dummy.text');
        $titleTranslation = $this->translationRepository->findByCode('en', 'translation', 'translatable', 'waavi.translation.test.dummy.title');
        $this->assertEquals('Título del dummy', $titleTranslation->text);
        $this->assertEquals('Título del dummy', $dummy->title);
        $this->assertEquals('slug', $dummy->slug);
        $this->assertEquals('Texto del dummy', $textTranslation->text);
        $this->assertEquals('Texto del dummy', $dummy->text);
        // Delete it:
        $deleted = $dummy->delete();
        $this->assertTrue($deleted);
        $this->assertEquals(0, Dummy::count());
        $this->assertEquals(0, $this->translationRepository->count());
    }
}

class Dummy extends Model
{
    use Translatable;

    protected $fillable = ['title', 'text'];

    protected $translatableAttributes = ['title', 'text'];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug']  = 'slug';
    }
}
