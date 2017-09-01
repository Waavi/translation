<?php namespace Waavi\Translation\Loaders;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Translation\Loader as LoaderContract;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;

abstract class Loader implements LoaderContract
{
    /**
     * The default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     *  Create a new loader instance.
     *
     *  @param  \Waavi\Translation\Repositories\LanguageRepository      $languageRepository
     *  @param  \Waavi\Translation\Repositories\TranslationRepository   $translationRepository
     *  @param  \Illuminate\Config\Repository                           $config
     */
    public function __construct($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        if ($locale != $this->defaultLocale) {
            return array_replace_recursive(
                $this->loadSource($this->defaultLocale, $group, $namespace),
                $this->loadSource($locale, $group, $namespace)
            );
        }
        return $this->loadSource($locale, $group, $namespace);
    }

    /**
     * Load the messages for the given locale from the loader source (cache, file, database, etc...)
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    abstract public function loadSource($locale, $group, $namespace = null);

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    abstract public function addNamespace($namespace, $hint);

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string  $path
     * @return void
     **/
    abstract public function addJsonPath($path);

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    abstract public function namespaces();
}
