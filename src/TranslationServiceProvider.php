<?php namespace Waavi\Translation;

use Illuminate\Translation\FileLoader as LaravelFileLoader;
use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use Waavi\Translation\Commands\FileLoaderCommand;
use Waavi\Translation\Facades\Translator;
use Waavi\Translation\Loaders\DatabaseLoader;
use Waavi\Translation\Loaders\FileLoader;
use Waavi\Translation\Loaders\MixedLoader;
use Waavi\Translation\Models\Language;
use Waavi\Translation\Models\Translation;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;

class TranslationServiceProvider extends LaravelTranslationServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translator.php', 'translator');

        $this->registerLoader();
        $this->registerCommand();
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $app                             = $this->app;
        $this->app['translation.loader'] = $this->app->share(function ($app) {
            $source        = $app['config']->get('translator.source');
            $defaultLocale = $app['config']->get('app.locale');
            $loader        = null;
            switch ($source) {
                case 'mixed':
                    $laravelFileLoader = new LaravelFileLoader($app['files'], $app->basePath() . '/resources/lang');
                    $fileLoader        = new FileLoader($defaultLocale, $laravelFileLoader);
                    $databaseLoader    = new DatabaseLoader($defaultLocale, new TranslationRepository(new Translation));
                    $loader            = new MixedLoader($defaultLocale, $fileLoader, $databaseLoader);
                case 'database':
                    $loader = new DatabaseLoader($defaultLocale, new TranslationRepository(new Translation));
                default:case 'files':
                    $laravelFileLoader = new LaravelFileLoader($app['files'], $app->basePath() . '/resources/lang');
                    $loader            = new FileLoader($defaultLocale, $laravelFileLoader);
            }
            if ($app['config']->get('translator.cache.enabled')) {
                $loader = new CacheLoader($defaultLocale, $app['cache'], $loader, $app['config']->get('translator.cache.timeout'), $app['config']->get('translator.cache.suffix'));
            }
            return $loader;
        });
    }

    /**
     *  Register the translator alias
     *
     *  @return void
     */
    protected function registerTranslator()
    {
        $this->app['translator'] = $this->app->share(function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            return $trans;
        });
    }

    protected function registerCommand()
    {
        $app                   = $this->app;
        $defaultLocale         = $app['config']->get('app.locale');
        $languageRepository    = new LanguageRepository(new Language);
        $translationRepository = new TranslationRepository(new Translation);
        $translationsPath      = $app->basePath() . '/resources/lang';
        $command               = new FileLoaderCommand($languageRepository, $translationRepository, $app['files'], $translationsPath, $defaultLocale);

        $this->app['command.translator:load'] = $command;
        $this->commands('command.translator:load');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['translator', 'translation.loader'];
    }

}
