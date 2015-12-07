<?php namespace Waavi\Translation\Loaders;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Translation\LoaderInterface;

class CacheLoader extends Loader implements LoaderInterface
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
     *  The cache key suffix
     *
     *  @var string
     */
    protected $cacheSuffix;

    /**
     *  Create a new mixed loader instance.
     *
     *  @param  string                              $defaultLocale
     *  @param  \Illuminate\Cache\Repository        $cache              Cache repository.
     *  @param  \Waavi\Translation\Loaders\Loader   $fallback           Translation loader to use on cache miss.
     *  @param  integer                             $cacheTimeout       In minutes.
     *  @param  string                              $cacheSuffix        Suffix for the cache.
     */
    public function __construct($defaultLocale, Cache $cache, Loader $fallback, $cacheTimeout, $cacheSuffix)
    {
        parent::__construct($defaultLocale);
        $this->cache        = $cache;
        $this->fallback     = $fallback;
        $this->cacheTimeout = $cacheTimeout;
        $this->cacheSuffix  = $cacheSuffix;
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
        $key = $this->generateCacheKey($locale, $group, $namespace);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        } else {
            $source = $this->fallback->load($locale, $group, $namespace);
            $this->cache->put($key, $source, $this->cacheTimeout);
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
     *  Generates a cache key based on the locale, group and namespace
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return array
     */
    public function generateCacheKey($locale, $group, $namespace)
    {
        $langKey = md5("{$locale}-{$group}-{$namespace}");
        return "{$this->cacheSuffix}-{$langKey}";
    }
}
