<?php namespace Waavi\Translation\Test\Commands;

use Waavi\Translation\Commands\FileLoaderCommand;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;
use Waavi\Translation\Test\TestCase;

class LoadTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->languageRepository    = \App::make(LanguageRepository::class);
        $this->translationRepository = \App::make(TranslationRepository::class);
        $translationsPath            = realpath(__DIR__ . '/../lang');
        $this->command               = new FileLoaderCommand($this->languageRepository, $this->translationRepository, \App::make('files'), $translationsPath, 'en');
    }

    /**
     * @test
     */
    public function it_loads_files_into_database()
    {
        $file = realpath(__DIR__ . '/../lang/en/auth.php');
        $this->command->loadFile($file, 'en');
        $translations = $this->translationRepository->all();

        $this->assertEquals(3, $translations->count());

        $this->assertEquals('en', $translations[0]->locale);
        $this->assertEquals('*', $translations[0]->namespace);
        $this->assertEquals('auth', $translations[0]->group);
        $this->assertEquals('login.label', $translations[0]->item);
        $this->assertEquals('Enter your credentials', $translations[0]->text);

        $this->assertEquals('en', $translations[1]->locale);
        $this->assertEquals('*', $translations[1]->namespace);
        $this->assertEquals('auth', $translations[1]->group);
        $this->assertEquals('login.action', $translations[1]->item);
        $this->assertEquals('Login', $translations[1]->text);

        $this->assertEquals('en', $translations[2]->locale);
        $this->assertEquals('*', $translations[2]->namespace);
        $this->assertEquals('auth', $translations[2]->group);
        $this->assertEquals('simple', $translations[2]->item);
        $this->assertEquals('Simple', $translations[2]->text);
    }

    /**
     * @test
     */
    public function it_loads_files_in_subdirectories_into_database()
    {
        $directory = realpath(__DIR__ . '/../lang/es');
        $this->command->loadDirectory($directory, 'es');
        $translations = $this->translationRepository->all()->sortBy('id');

        $this->assertEquals(2, $translations->count());

        $this->assertEquals('es', $translations[0]->locale);
        $this->assertEquals('*', $translations[0]->namespace);
        $this->assertEquals('welcome/page', $translations[0]->group);
        $this->assertEquals('title', $translations[0]->item);
        $this->assertEquals('Bienvenido', $translations[0]->text);

        $this->assertEquals('es', $translations[1]->locale);
        $this->assertEquals('*', $translations[1]->namespace);
        $this->assertEquals('auth', $translations[1]->group);
        $this->assertEquals('login.action', $translations[1]->item);
        $this->assertEquals('Identifícate', $translations[1]->text);
    }

    /**
     * @test
     */
    public function it_doesnt_load_undefined_locales()
    {
        $this->command->handle();
        $locales = $this->translationRepository->all()->pluck('locale')->toArray();
        $this->assertTrue(in_array('en', $locales));
        $this->assertTrue(in_array('es', $locales));
        $this->assertFalse(in_array('ca', $locales));
    }

    /**
     * @test
     */
    public function it_loads_overwritten_vendor_files_correctly()
    {
        $this->command->handle();

        $translations = $this->translationRepository->all();

        $this->assertEquals(9, $translations->count());

        $this->assertEquals('Texto proveedor', $translations->where('locale', 'es')->where('namespace', 'package')->where('group', 'example')->where('item', 'entry')->first()->text);
        $this->assertEquals('Vendor text', $translations->where('locale', 'en')->where('namespace', 'package')->where('group', 'example')->where('item', 'entry')->first()->text);
    }

    /**
     *  @test
     */
    public function it_doesnt_overwrite_locked_translations()
    {
        $trans = $this->translationRepository->create([
            'locale'    => 'en',
            'namespace' => '*',
            'group'     => 'auth',
            'item'      => 'login.label',
            'text'      => 'No override',
        ]);
        $trans->locked = true;
        $trans->save();

        $file = realpath(__DIR__ . '/../lang/en/auth.php');
        $this->command->loadFile($file, 'en');
        $translations = $this->translationRepository->all();

        $this->assertEquals(3, $translations->count());

        $this->assertEquals('en', $translations[0]->locale);
        $this->assertEquals('*', $translations[0]->namespace);
        $this->assertEquals('auth', $translations[0]->group);
        $this->assertEquals('login.label', $translations[0]->item);
        $this->assertEquals('No override', $translations[0]->text);

        $this->assertEquals('en', $translations[1]->locale);
        $this->assertEquals('*', $translations[1]->namespace);
        $this->assertEquals('auth', $translations[1]->group);
        $this->assertEquals('login.action', $translations[1]->item);
        $this->assertEquals('Login', $translations[1]->text);
    }

    /**
     *  @test
     */
    public function it_doesnt_load_empty_arrays()
    {
        $file = realpath(__DIR__ . '/../lang/en/empty.php');
        $this->command->loadFile($file, 'en');
        $translations = $this->translationRepository->all();

        $this->assertEquals(1, $translations->count());

        $this->assertEquals('en', $translations[0]->locale);
        $this->assertEquals('*', $translations[0]->namespace);
        $this->assertEquals('empty', $translations[0]->group);
        $this->assertEquals('emptyString', $translations[0]->item);
        $this->assertEquals('', $translations[0]->text);
    }
}
