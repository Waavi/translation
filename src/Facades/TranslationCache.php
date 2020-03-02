<?php namespace Waavi\Translation\Facades;

use \Illuminate\Support\Facades\Facade;

/**
 * @method static bool has($locale, $group, $namespace)
 * @method static mixed get($locale, $group, $namespace)
 * @method static void put($locale, $group, $namespace, $content, $minutes)
 * @method static void flush($locale, $group, $namespace)
 * @method static void flushAll($locale, $group, $namespace)
 *
 * @see \Waavi\Translation\Cache\CacheRepositoryInterface
 */
class TranslationCache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translation.cache.repository';
    }
}
