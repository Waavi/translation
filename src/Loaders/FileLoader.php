<?php namespace Waavi\Translation\Loaders;

use Illuminate\Translation\FileLoader as LaravelFileLoader;

class FileLoader extends Loader
{
    /**
     * The default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * The laravel file loader instance.
     *
     * @var \Illuminate\Translation\FileLoader
     */
    protected $laravelFileLoader;

    /**
     *  Create a new mixed loader instance.
     *
     *  @param  string                              $defaultLocale
     *  @param  \Illuminate\Translation\FileLoader  $laravelFileLoader
     *  @return void
     */
    public function __construct($defaultLocale, LaravelFileLoader $laravelFileLoader)
    {
        parent::__construct($defaultLocale);
        $this->laravelFileLoader = $laravelFileLoader;
    }

    /**
     * Load the messages strictly for the given locale without checking the cache or in case of a cache miss.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function loadSource($locale, $group, $namespace = '*')
    {
        return $this->laravelFileLoader->load($locale, $group, $namespace);
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
        $this->hints[$namespace] = $hint;
        $this->laravelFileLoader->addNamespace($namespace, $hint);
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string  $path
     * @return void
     */
    public function addJsonPath($path)
    {
        $this->laravelFileLoader->addJsonPath($path);
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces()
    {
        return $this->hints;
    }
}
