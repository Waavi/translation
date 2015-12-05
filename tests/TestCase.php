<?php

namespace Waavi\Translation\Test;

use File;
use Orchestra\Testbench\TestCase as Orchestra;
use Route;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();
        //$this->app['cache']->clear();
        $this->initializeDirectory($this->getTempDirectory());
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

    protected function getPackageAliases($app)
    {
        return [
            'Localizer' => \Waavi\Translation\Localizer::class,
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
            'database' => $this->getTempDirectory() . '/database.sqlite',
            'prefix'   => '',
        ]);
        $app['config']->set('app.key', 'sF5r4kJy5HEcOEx3NWxUcYj1zLZLHxuu');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTempDirectory() . '/database.sqlite', null);
        $this->artisan('migrate', ['--realpath' => realpath(__DIR__ . '/../database/migrations')]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpRoutes($app)
    {
        Route::any('/', function () {
            return 'home of ' . (auth()->check() ? auth()->user()->id : 'anonymous');
        });
        Route::any('/random', function () {
            return str_random();
        });
        Route::any('/redirect', function () {
            return redirect('/');
        });
        Route::any('/uncacheable', ['middleware' => 'doNotCacheResponse', function () {
            return 'uncacheable ' . str_random();
        }]);
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__ . '/temp' . ($suffix == '' ? '' : '/' . $suffix);
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }
}
