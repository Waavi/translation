<?php
namespace Waavi\Translation\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Waavi\Translation\Repositories\LanguageRepository;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();
        //$this->app['cache']->clear();
        $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Waavi\Translation\TranslationServiceProvider::class,
        ];
    }

    /**
     * @param $app
     */
    protected function getPackageAliases($app)
    {
        return [
            'UriLocalizer'     => \Waavi\Translation\Facades\UriLocalizer::class,
            'TranslationCache' => \Waavi\Translation\Facades\TranslationCache::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('app.key', 'sF5r4kJy5HEcOEx3NWxUcYj1zLZLHxuu');
        $app['config']->set('translator.source', 'database');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->artisan('migrate');
        // Seed the spanish and english languages
        $languageRepository = \App::make(LanguageRepository::class);
        $languageRepository->create(['locale' => 'en', 'name' => 'English']);
        $languageRepository->create(['locale' => 'es', 'name' => 'Spanish']);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpRoutes($app)
    {
        \Route::get('/', ['middleware' => 'localize', function () {
            return 'Whoops';
        }]);
        \Route::get('/ca', ['middleware' => 'localize', function () {
            return 'Whoops ca';
        }]);
        \Route::post('/', ['middleware' => 'localize', function () {
            return 'POST answer';
        }]);
        \Route::get('/es', ['middleware' => 'localize', function () {
            return 'Hola mundo';
        }]);
        \Route::get('/en', ['middleware' => 'localize', function () {
            return 'Hello world';
        }]);
        \Route::get('/en/locale', ['middleware' => 'localize', function () {
            return \App::getLocale();
        }]);
        \Route::get('/es/locale', ['middleware' => 'localize', function () {
            return \App::getLocale();
        }]);
        \Route::get('/api/v1/en/locale', ['middleware' => 'localize:2', function () {
            return \App::getLocale();
        }]);
        \Route::get('/api/v1/es/locale', ['middleware' => 'localize:2', function () {
            return \App::getLocale();
        }]);
        \Route::get('/api/v1/ca/locale', ['middleware' => 'localize:2', function () {
            return 'Whoops ca';
        }]);
        \Route::post('/welcome', ['middleware' => 'localize', function () {
            return trans('welcome.title');
        }]);
    }
}
