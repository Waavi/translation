<?php namespace Waavi\Translation\Cache;

use Illuminate\Contracts\Cache\Store;
use \ReflectionClass;

class RepositoryFactory
{
    public static function make(Store $store, $cacheTag)
    {
        $cacheReflection = new ReflectionClass(get_class($store));
        $storeParent     = $cacheReflection->getParentClass();
        $parentName      = $storeParent ? $storeParent->name : '';
        return $parentName == 'Illuminate\Cache\TaggableStore' ? new TaggedRepository($store, $cacheTag) : new SimpleRepository($store, $cacheTag);
    }
}
