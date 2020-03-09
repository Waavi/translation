<?php namespace Waavi\Translation\Facades;

use \Illuminate\Support\Facades\Facade;

/**
 * @method static string localeFromRequest($segment = 0)
 * @method static string localize($url, $locale, $segment = 0)
 * @method static string|null getLocaleFromUrl($url, $segment = 0)
 * @method static string cleanUrl($url, $segment = 0)
 *
 * @see \Waavi\Translation\UriLocalizer
 */
class UriLocalizer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translation.uri.localizer';
    }
}
