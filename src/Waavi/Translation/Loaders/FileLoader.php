<?php namespace Waavi\Translation\Loaders;

use Illuminate\Translation\LoaderInterface;
use Illuminate\Translation\FileLoader as LaravelFilerLoader;
use Waavi\Translation\Loaders\Loader;
use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class FileLoader extends Loader implements LoaderInterface {

	/**
	 * The laravel file loader instance.
	 *
	 * @var \Illuminate\Translation\FileLoader
	 */
	protected $laravelFileLoader;

	/**
	 * 	Create a new mixed loader instance.
	 *
	 * 	@param  \Waavi\Lang\Providers\LanguageProvider  			$languageProvider
	 * 	@param 	\Waavi\Lang\Providers\LanguageEntryProvider		$languageEntryProvider
	 *	@param 	\Illuminate\Foundation\Application  					$app
	 */
	public function __construct($languageProvider, $languageEntryProvider, $app)
	{
		parent::__construct($languageProvider, $languageEntryProvider, $app);
		$this->laravelFileLoader = new LaravelFilerLoader($app['files'], $app['path'].'/lang');
	}

	/**
	 * Load the messages strictly for the given locale without checking the cache or in case of a cache miss.
	 *
	 * @param  string  $locale
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	public function loadRawLocale($locale, $group, $namespace = null)
	{
		$namespace = $namespace ?: '*';
		return $this->laravelFileLoader->load($locale, $group, $namespace);
	}

}