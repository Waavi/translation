<?php

namespace Waavi\Translation\Cache;

interface CacheRepositoryInterface
{
    /**
     *  Checks if an entry with the given key exists in the cache.
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return boolean
     */
    public function has($locale, $group, $namespace);

    /**
     *  Get an item from the cache
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return mixed
     */
    public function get($locale, $group, $namespace);

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
    public function put($locale, $group, $namespace, $content, $minutes);

    /**
     *  Flush the cache for the given entries
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return void
     */
    public function flush($locale, $group, $namespace);

    /**
     *  Completely flush the cache
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return void
     */
    public function flushAll();
}
