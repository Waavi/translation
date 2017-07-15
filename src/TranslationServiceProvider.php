<?php namespace Waavi\Translation;

use Illuminate\Translation\FileLoader as LaravelFileLoader;
use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use Waavi\Translation\Commands\FileLoaderCommand;
use Waavi\Translation\Loaders\CacheLoader;
use Waavi\Translation\Loaders\DatabaseLoader;
use Waavi\Translation\Loaders\FileLoader;
use Waavi\Translation\Loaders\MixedLoader;
use Waavi\Translation\Middleware\TranslationMiddleware;
use Waavi\Translation\Models\Translation;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;

class TranslationServiceProvider extends LaravelTranslationServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/translator.php' => config_path('translator.php'),
        ]);
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translator.php', 'translator');

        parent::register();
        $this->registerCommand();
        $this->app->singleton('urilocalizer', UriLocalizer::class);
        $this->app[\Illuminate\Routing\Router::class]->middleware('localize', TranslationMiddleware::class);
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $app = $this->app;
        $this->app->singleton('translation.loader', function ($app) {
            $source        = $app['config']->get('translator.source');
            $defaultLocale = $app['config']->get('app.locale');
            $loader        = null;
            switch ($source) {
                case 'mixed':
                    $laravelFileLoader = new LaravelFileLoader($app['files'], $app->basePath() . '/resources/lang');
                    $fileLoader        = new FileLoader($defaultLocale, $laravelFileLoader);
                    $databaseLoader    = new DatabaseLoader($defaultLocale, $app->make(TranslationRepository::class));
                    $loader            = new MixedLoader($defaultLocale, $fileLoader, $databaseLoader);
                    break;
                case 'database':
                    $loader = new DatabaseLoader($defaultLocale, $app->make(TranslationRepository::class));
                    break;
                default:case 'files':
                    $laravelFileLoader = new LaravelFileLoader($app['files'], $app->basePath() . '/resources/lang');
                    $loader            = new FileLoader($defaultLocale, $laravelFileLoader);
            }
            if ($app['config']->get('translator.cache.enabled')) {
                $cacheStore = $app['cache']->store($app['config']->get('cache.default'));
                $loader     = new CacheLoader($defaultLocale, $cacheStore, $loader, $app['config']->get('translator.cache.timeout'), $app['config']->get('translator.cache.suffix'));
            }
            return $loader;
        });
    }

    protected function registerCommand()
    {
        $app                   = $this->app;
        $defaultLocale         = $app['config']->get('app.locale');
        $languageRepository    = $app->make(LanguageRepository::class);
        $translationRepository = $app->make(TranslationRepository::class);
        $translationsPath      = $app->basePath() . '/resources/lang';
        $command               = new FileLoaderCommand($languageRepository, $translationRepository, $app['files'], $translationsPath, $defaultLocale);

        $this->app['command.translator:load'] = $command;
        $this->commands('command.translator:load');
    }
}
