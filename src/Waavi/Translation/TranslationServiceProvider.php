<?php namespace Waavi\Translation;

use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use \Illuminate\Translation\FileLoader;
use Waavi\Translation\Providers\LanguageProvider;
use Waavi\Translation\Providers\LangEntryProvider;

class LangServiceProvider extends LaravelTranslationServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('waavi/lang');
	}

	/**
	 * Register the translation line loader.
	 *
	 * @return void
	 */
	protected function registerLoader()
	{
		$this->app['translation.loader'] = $this->app->share(function($app)
		{
			$languageModel = new LanguageProvider($app['config']['waavi/lang::language.model']);
			$langEntryModel = new LangEntryProvider($app['config']['waavi/lang::language_entry.model']);
			$fileLoader = new FileLoader($app['files'], $app['path'].'/lang');
			if ($app['config']['waavi/lang::driver'] == 'database') {
				return $fileLoader;
			} else {
				return new DBLoader($fileLoader, $languageModel, $langEntryModel, $app['config']['app.locale']);
			}
		});
	}

}