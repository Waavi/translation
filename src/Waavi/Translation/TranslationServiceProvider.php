<?php namespace Waavi\Translation;

use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;
use Waavi\Translation\Loaders\FileLoader;
use Waavi\Translation\Loaders\DatabaseLoader;
use Waavi\Translation\Loaders\MixedLoader;
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

			$mode 					= $app['config']['waavi/translation::mode'];
			if ($mode == 'auto')	$mode = $app['config']['debug'] ? 'mixed'	:	'database';

			switch ($mode) {
				default:
				case 'mixed':
					return new MixedLoader($languageModel, $langEntryModel, $app);
				case 'filesystem':
					return new FileLoader($languageModel, $langEntryModel, $app);
				default:
				case 'database':
					return new DatabaseLoader($languageModel, $langEntryModel, $app);
			}
		});
	}

}