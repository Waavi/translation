<?php namespace Waavi\Translation\Middleware;

use App\Translator\Facades\Translator;
use App\Utils\General\Helper;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use \App;

class TranslationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Schema::hasTable('languages') and Helper::domain() == Config::get('app.domains.front')) {
            $uriLocale     = $request->segment(1);
            $browserLocale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            $candidates    = [$uriLocale, $browserLocale];
            $locale        = Translator::extractFirstValidLocale($candidates);
            if ($locale) {
                Lang::setLocale($locale);
            }
            $locale = Lang::getLocale();
            View::share('currentLanguage', App::make('App\Translator\Repositories\LanguageRepository')->findByLocale($locale));
            View::share('selectableLanguages', App::make('App\Translator\Repositories\LanguageRepository')->allExcept($locale));
        }

        return $next($request);
    }
}
