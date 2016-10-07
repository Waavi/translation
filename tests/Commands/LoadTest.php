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
        $this->command               = new FileLoaderCommand($this->languageRepository, $this->translationRepository, \App::make('files'), \App::make('translation.loader'), $translationsPath, 'en');
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
        $translations = $this->translationRepository->all();

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
        $this->command->fire();
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
        $this->command->fire();

        $translations = $this->translationRepository->all();

        $this->assertEquals(9, $translations->count());

        $this->assertEquals('en', $translations[7]->locale);
        $this->assertEquals('package', $translations[7]->namespace);
        $this->assertEquals('example', $translations[7]->group);
        $this->assertEquals('entry', $translations[7]->item);
        $this->assertEquals('Vendor text', $translations[7]->text);

        $this->assertEquals('es', $translations[8]->locale);
        $this->assertEquals('package', $translations[8]->namespace);
        $this->assertEquals('example', $translations[8]->group);
        $this->assertEquals('entry', $translations[8]->item);
        $this->assertEquals('Texto proveedor', $translations[8]->text);
    }

    /**
     * @test
     */
    public function it_loads_hinted_namespaces_from_the_loader_correctly()
    {
        $this->app['translation.loader']->addNamespace('hinted', __DIR__.'/../hinted/');

        $this->command->fire();

        $translations = $this->translationRepository->getItems('en', 'hinted', 'translation');

        $this->assertEquals(1, count($translations));

        $this->assertEquals('en', $translations[0]['locale']);
        $this->assertEquals('hinted', $translations[0]['namespace']);
        $this->assertEquals('translation', $translations[0]['group']);
        $this->assertEquals('by_hinting', $translations[0]['item']);
        $this->assertEquals('It works', $translations[0]['text']);
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
