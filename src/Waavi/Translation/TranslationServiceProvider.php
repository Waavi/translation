<?php namespace Waavi\Translation;

use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use Illuminate\Translation\FileLoader;
use Waavi\Translation\Providers\LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider;

class TranslationServiceProvider extends LaravelTranslationServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('waavi/translation', 'waavi/translation', __DIR__.'/../..');
	}

	/**
	 * Register the translation line loader.
	 *
	 * @return void
	 */
	protected function registerLoader()
	{
		$app = $this->app;
		$this->app['translation.loader'] = $this->app->share(function($app)
		{
			$languageModel 	= new LanguageProvider($app['config']['waavi/translation::language.model']);
			$langEntryModel = new LanguageEntryProvider($app['config']['waavi/translation::language_entry.model']);
			$fileLoader 		= new FileLoader($app['files'], $app['path'].'/lang');
			switch ($app['config']['waavi/translation::driver']) {
				case 'file':
					return $fileLoader;
				default:
				case 'database':
					return new DBLoader($fileLoader, $languageModel, $langEntryModel, $app);
			}
		});
	}

}