<?php namespace Waavi\Translation\Test\Traits;

use Illuminate\Database\Eloquent\Model;
use Mockery;
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
     * @test
     */
    public function it_saves_translations()
    {
        $dummy        = new Dummy;
        $dummy->title = 'Dummy title';
        $dummy->text  = 'Dummy text';
        $saved        = $dummy->save() ? true : false;
        $this->assertTrue($saved);
        $this->assertEquals(1, Dummy::count());
        $this->assertEquals('slug', $dummy->slug);
        // Check that there is a language entry in the database:
        $titleTranslation = $this->translationRepository->findByLangCode('en', $dummy->translationCodeFor('title'));
        $this->assertEquals('Dummy title', $titleTranslation->text);
        $this->assertEquals('Dummy title', $dummy->title);
        $textTranslation = $this->translationRepository->findByLangCode('en', $dummy->translationCodeFor('text'));
        $this->assertEquals('Dummy text', $textTranslation->text);
        $this->assertEquals('Dummy text', $dummy->text);
        // Delete it:
        $deleted = $dummy->delete();
        $this->assertTrue($deleted);
        $this->assertEquals(0, Dummy::count());
        $this->assertEquals(0, $this->translationRepository->count());
    }

    /**
     * @test
     */
    public function it_flushes_cache()
    {
        $cacheMock = Mockery::mock(\Waavi\Translation\Cache\SimpleRepository::class);
        $this->app->bind('translation.cache.repository', function ($app) use ($cacheMock) {return $cacheMock;});
        $cacheMock->shouldReceive('flush')->with('en', 'translatable', '*');
        $dummy        = new Dummy;
        $dummy->title = 'Dummy title';
        $dummy->text  = 'Dummy text';
        $saved        = $dummy->save() ? true : false;
        $this->assertTrue($saved);
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
