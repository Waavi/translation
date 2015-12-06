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

    protected function getPackageAliases($app)
    {
        return [
            'UriLocalizer' => \Waavi\Translation\Facades\UriLocalizer::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('app.key', 'sF5r4kJy5HEcOEx3NWxUcYj1zLZLHxuu');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->artisan('migrate', ['--realpath' => realpath(__DIR__ . '/../database/migrations')]);
        // Seed the spanish and english languages
        $languageRepository = \App::make(LanguageRepository::class);
        $languageRepository->create(['locale' => 'en', 'name' => 'English']);
        $languageRepository->create(['locale' => 'es', 'name' => 'Spanish']);
    }
}
