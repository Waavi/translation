<?php namespace Waavi\Translation;

use Illuminate\Http\Request;
use Waavi\Translation\Repositories\LanguageRepository;

class UriLocalizer
{
    public function __construct(LanguageRepository $languageRepository, Request $request)
    {
        $this->request            = $request;
        $this->languageRepository = $languageRepository;
    }

    /**
     *  Returns the locale present in the current url, if any.
     *
     *  @return string
     */
    public function localeFromRequest()
    {
        $url = $this->request->getUri();
        return $this->getLocaleFromUrl($url);
    }

    /**
     *  Localizes the given url to the given locale. Removes domain if present.
     *  Ex: /home => /es/home, /en/home => /es/home, http://www.domain.com/en/home => /en/home, https:://domain.com/ => /en
     *
     *  @param  string  $url
     *  @param  string  $locale
     *  @return string
     */
    public function localize($url, $locale)
    {
        $uri = $this->cleanUrl($url);
        return $this->removeTrailingSlash("/{$locale}{$uri}");
    }

    /**
     *  Extract the first valid locale from a url
     *
     *  @param  string      $url
     *  @return string|null $locale
     */
    public function getLocaleFromUrl($url)
    {
        $parsedUrl = parse_url($url);
        $path      = array_get($parsedUrl, 'path', '');

        // Remove forward slash from path if any:
        $path = strlen($path) >= 1 && substr($path, 0, 1) === '/' ? substr($path, 1) : $path;
        // Get locale:
        $pathSegments     = explode('/', $path);
        $localeCandidate  = array_get($pathSegments, 0, false);
        $availableLocales = $this->languageRepository->availableLocales();

        return in_array($localeCandidate, $availableLocales) ? $localeCandidate : null;
    }

    /**
     *  Removes the domain and locale (if present) of a given url.
     *  Ex: http://www.domain.com/locale/random => /random, https://www.domain.com/random => /random, http://domain.com/random?param=value => /random?param=value
     *
     *  @param  string $url
     *  @return string
     */
    public function cleanUrl($url)
    {
        // Parse the url:
        $parsedUrl = parse_url($url);
        $path      = array_get($parsedUrl, 'path', '');
        $query     = array_get($parsedUrl, 'query', false);

        // Remove front and trailing slashes from path if any:
        $path = $this->removeTrailingSlash($this->removeFrontSlash($path));

        // Remove locale from path:
        $pathSegments     = explode('/', $path);
        $localeCandidate  = array_get($pathSegments, 0, false);
        $availableLocales = $this->languageRepository->availableLocales();
        if ($localeCandidate && in_array($localeCandidate, $availableLocales)) {
            unset($pathSegments[0]);
        }
        $path = implode('/', $pathSegments);
        $path = strlen($path) >= 1 && substr($path, 0, 1) === '/' ? $path : "/{$path}";

        return $query ? $path . '?' . $query : $path;
    }

    /**
     *  Remove the front slash from a string
     *
     *  @param  string $path
     *  @return string
     */
    protected function removeFrontSlash($path)
    {
        return strlen($path) > 0 && substr($path, 0, 1) === '/' ? substr($path, 1) : $path;
    }

    /**
     *  Remove the trailing slash from a string
     *
     *  @param  string $path
     *  @return string
     */
    protected function removeTrailingSlash($path)
    {
        return strlen($path) > 0 && substr($path, -1) === '/' ? substr($path, 0, -1) : $path;
    }
}
