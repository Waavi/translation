<?php namespace Waavi\Translation\Test\Localizer;

// PHPUnit wrappers:
use Waavi\Translation\Test\TestCase;

class TranslateTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->spanish = factory(App\Translator\Models\Language::class)->create(['locale' => 'ep']);
        $this->english = factory(App\Translator\Models\Language::class)->create(['locale' => 'en']);
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
