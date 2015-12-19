<?php namespace Waavi\Translation\Cache;

use Illuminate\Contracts\Cache\Store;

class TaggedRepository implements CacheRepositoryInterface
{
    /**
     * The cache store implementation.
     *
     * @var \Illuminate\Contracts\Cache\Store
     */
    protected $store;

    /**
     * The translation cache tag
     *
     * @var string
     */
    protected $cacheTag;

    /**
     * Create a new cache repository instance.
     *
     * @param  \Illuminate\Contracts\Cache\Store  $store
     * @return void
     */
    public function __construct(Store $store, $cacheTag)
    {
        $this->store    = $store;
        $this->cacheTag = $cacheTag;
    }

    /**
     *  Checks if an entry with the given key exists in the cache.
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return boolean
     */
    public function has($locale, $group, $namespace)
    {
        return !is_null($this->get($locale, $group, $namespace));
    }

    /**
     *  Get an item from the cache
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return mixed
     */
    public function get($locale, $group, $namespace)
    {
        $key = $this->getKey($locale, $group, $namespace);
        return $this->store->tags([$this->cacheTag, $key])->get($key);
    }

    /**
     *  Put an item into the cache store
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @param  mixed   $content
     *  @param  integer $minutes
     *  @return void
     */
    public function put($locale, $group, $namespace, $content, $minutes)
    {
        $key = $this->getKey($locale, $group, $namespace);
        $this->store->tags([$this->cacheTag, $key])->put($key, $content, $minutes);
    }

    /**
     *  Flush the cache for the given entries
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return void
     */
    public function flush($locale, $group, $namespace)
    {
        $key = $this->getKey($locale, $group, $namespace);
        $this->store->tags([$key])->flush();
    }

    /**
     *  Completely flush the cache
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return void
     */
    public function flushAll()
    {
        $this->store->tags([$this->cacheTag])->flush();
    }

    /**
     *  Returns a unique cache key.
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return string
     */
    protected function getKey($locale, $group, $namespace)
    {
        return md5("{$this->cacheTag}-{$locale}-{$group}-{$namespace}");
    }
}
