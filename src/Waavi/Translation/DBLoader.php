<?php namespace Waavi\Translation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\LoaderInterface;
use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class DBLoader implements LoaderInterface {

	protected $fileLoader;
	protected $languageProvider;
	protected $languageEntryProvider;
	protected $cacheEnabled;
	protected $cacheTimeout;

	/**
	 * All of the namespace hints.
	 *
	 * @var array
	 */
	protected $hints = array();

	/**
	 * 	Create a new file loader instance.
	 *
	 * 	@param  \Illuminate\Translation\FileLoader  					$fileLoader
	 * 	@param  \Waavi\Lang\Providers\LanguageProvider  			$languageProvider
	 * 	@param 	\Waavi\Lang\Providers\LanguageEntryProvider		$languageEntryProvider
	 *	@param 	string 																				$defaultLocale
	 */
	public function __construct($fileLoader, $languageProvider, $languageEntryProvider, $app)
	{
		$this->fileLoader 						= $fileLoader;
		$this->languageProvider 			= $languageProvider;
		$this->languageEntryProvider 	= $languageEntryProvider;
		$this->app 										= $app;
		$this->defaultLocale 					= $app['config']['app.locale'];
		$this->cacheEnabled 					= $app['config']['waavi/translation::cache.enabled'];
		$this->cacheTimeout 					= $app['config']['waavi/translation::cache.timeout'];
	}

	/**
	 * Load text for the given locale, group and namespace. Gives priority to key pairs found in files, then database, then default lang file, then default lang in the db.
	 *
	 * @param  string  $locale
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	public function load($locale, $group, $namespace = null)
	{
		// Check the cache:
		$cacheKey = "$locale.$group.$namespace";
		if ($this->cacheEnabled && $this->app['cache']->has($cacheKey)) {
			return $this->app['cache']->get($cacheKey);
		}

		// Always load the default locale
		$defaultLocaleFile 	= array();
		$defaultLocaleDB 		= array();
		if ($locale != $this->defaultLocale) {
			$defaultLocaleDB 		= $this->loadFromDB($this->defaultLocale, $group, $namespace);
			$defaultLocaleFile 	= $this->loadFromFile($this->defaultLocale, $group, $namespace);
		}

		// Load locale given by param
		$localeDB 	= $this->loadFromDB($locale, $group, $namespace);
		$localeFile = $this->loadFromFile($locale, $group, $namespace);

		// Return the merge of all translation sources, by priority:
		//	1. Locale file on disk.
		//	2. Locale database entries.
		//	3. Default locale file on disk.
		// 	4. Default locale database entries.
		$langLines = array_merge($defaultLocaleDB, $defaultLocaleFile, $localeDB, $localeFile);

		if ($this->cacheEnabled) {
			$this->app['cache']->put($cacheKey, $langLines, $this->cacheTimeout);
		}

		return $langLines;
	}

	/**
	 *	Load text for the given locale, group and namespace from the database.
	 *
	 *	@param 	string 	$locale
	 * 	@param  string  $group
	 * 	@param  string  $namespace
	 * 	@return array
	 */
	private function loadFromDB($locale, $group, $namespace)
	{
		$namespace = $namespace == '*' ? null : $namespace;
		$langArray = array();
		$language = $this->languageProvider->findByLocale($locale);
		if ($language) {
			$entries = $language->entries()->where('group', '=', $group)->where('namespace', '=', $namespace)->get();
			if ($entries) {
				foreach($entries as $entry) {
					array_set($langArray, $entry->item, $entry->text);
				}
			}
		}
		return $langArray;
	}

	/**
	 *	Load text for the given locale, group and namespace from file.
	 *
	 *	@param 	string 	$locale
	 * 	@param  string  $group
	 * 	@param  string  $namespace
	 * 	@return array
	 */
	private function loadFromFile($locale, $group, $namespace)
	{
		return $this->fileLoader->load($locale, $group, $namespace);
	}

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string  $namespace
	 * @param  string  $hint
	 * @return void
	 */
	public function addNamespace($namespace, $hint)
	{
		return;
	}

}