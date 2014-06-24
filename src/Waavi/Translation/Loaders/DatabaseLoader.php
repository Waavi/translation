<?php namespace Waavi\Translation\Loaders;

use Illuminate\Translation\LoaderInterface;
use Waavi\Translation\Loaders\Loader;
use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class DatabaseLoader extends Loader implements LoaderInterface {
	/**
	 * Load the messages strictly for the given locale.
	 *
	 * @param  Language  	$language
	 * @param  string  		$group
	 * @param  string  		$namespace
	 * @return array
	 */
	public function loadRawLocale($locale, $group, $namespace = null)
	{
		$result = array();
		$namespace = $namespace ?: '*';

		$fallback_locale = \Config::get("app.fallback_locale");

		$fallback_language = $fallback_locale !== null && $locale != $fallback_locale ?
			$this->languageProvider->findByLocale($fallback_locale) : null;

		$language = $this->languageProvider->findByLocale($locale);

		$load_language_entries = function($language_entity) use ($group, $namespace){
			$lang_entries = array();
			$entries = $language_entity->entries()->where('group', '=', $group)->where('namespace', '=', $namespace)->get();

			if ($entries) {
				foreach($entries as $entry) {
					array_set($lang_entries, $entry->item, $entry->text);
				}
			}

			return $lang_entries;
		};

		if($language){
			$result = $load_language_entries($language);
		}

		if($fallback_language){
			$fallback_result = $load_language_entries($fallback_language);
			$result = self::arrayMergeRecursiveDistinct($fallback_result, $result);
		}

		return $result;
	}

	private static function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
	{
		$merged = $array1;

		foreach($array2 as $key => & $value)
		{
			$merged[$key] = is_array($value) && isset($merged[$key]) && is_array($merged[$key]) ?
				self::arrayMergeRecursiveDistinct($merged[$key], $value) : $value;
		}

		return $merged;
	}
}