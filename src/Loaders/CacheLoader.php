<?php namespace Waavi\Translation\Loaders;

use Waavi\Translation\Cache\CacheRepositoryInterface as Cache;

class CacheLoader extends Loader
{
    /**
     * The default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     *  The laravel cache instance.
     *
     *  @var \Illuminate\Config\Repository
     */
    protected $cache;

    /**
     *  The loader fallback instance in case of a cache miss.
     *
     *  @var Loader
     */
    protected $fallback;

    /**
     *  The cache timeout in minutes.
     *
     *  @var string
     */
    protected $cacheTimeout;

    /**
     *  Create a new mixed loader instance.
     *
     *  @param  string                                                      $defaultLocale
     *  @param  \Waavi\Translation\Contracts\CacheRepositoryInterface       $cache              Cache repository.
     *  @param  \Waavi\Translation\Loaders\Loader                           $fallback           Translation loader to use on cache miss.
     *  @param  integer                                                     $cacheTimeout       In minutes.
     */
    public function __construct($defaultLocale, Cache $cache, Loader $fallback, $cacheTimeout)
    {
        parent::__construct($defaultLocale);
        $this->cache        = $cache;
        $this->fallback     = $fallback;
        $this->cacheTimeout = $cacheTimeout;
    }

    /**
     *  Load the messages for the given locale.
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return array
     */
    public function loadSource($locale, $group, $namespace = '*')
    {
        if ($this->cache->has($locale, $group, $namespace)) {
            return $this->cache->get($locale, $group, $namespace);
        } else {
            $source = $this->fallback->load($locale, $group, $namespace);
            $this->cache->put($locale, $group, $namespace, $source, $this->cacheTimeout);
            return $source;
        }
    }

    /**
     *  Add a new namespace to the loader.
     *
     *  @param  string  $namespace
     *  @param  string  $hint
     *  @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->fallback->addNamespace($namespace, $hint);
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string  $path
     * @return void
     */
    public function addJsonPath($path)
    {
        //
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces()
    {
        return $this->fallback->namespaces();
    }
}
