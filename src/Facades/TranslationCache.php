<?php namespace Waavi\Translation\Facades;

use \Illuminate\Support\Facades\Facade;

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
