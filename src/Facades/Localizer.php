<?php namespace Waavi\Translation\Facades;

use \Illuminate\Support\Facades\Facade;

class Localizer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Localizer';
    }
}
