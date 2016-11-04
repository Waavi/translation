<?php namespace Waavi\Translation;

use Illuminate\Http\Request;
use Waavi\Translation\Repositories\LanguageRepository;

class UriLocalizer
{
    /**
     * @param LanguageRepository $languageRepository
     * @param Request $request
     */
    public function __construct(LanguageRepository $languageRepository, Request $request)
    {
        $this->request          = $request;
        $this->availableLocales = $languageRepository->availableLocales();
    }

    /**
     *  Returns the locale present in the current url, if any.
     *
     *  @param  integer $segment     Index of the segment containing locale info
     *  @return string
     */
    public function localeFromRequest($segment = 0)
    {
        $url = $this->request->getUri();
        return $this->getLocaleFromUrl($url, $segment);
    }

    /**
     *  Localizes the given url to the given locale. Removes domain if present.
     *  Ex: /home => /es/home, /en/home => /es/home, http://www.domain.com/en/home => /en/home, https:://domain.com/ => /en
     *  If a non zero segment index is given, and the url doesn't have enought segments, the url is unchanged.
     *
     *  @param  string  $url
     *  @param  string  $locale
     *  @param  integer $segment     Index of the segment containing locale info
     *  @return string
     */
    public function localize($url, $locale, $segment = 0)
    {
        $cleanUrl  = $this->cleanUrl($url, $segment);
        $parsedUrl = $this->parseUrl($cleanUrl, $segment);

        // Check if there are enough segments, if not return url unchanged:
        if (count($parsedUrl['segments']) >= $segment) {
            array_splice($parsedUrl['segments'], $segment, 0, $locale);
        }
        return $this->pathFromParsedUrl($parsedUrl);
    }

    /**
     *  Extract the first valid locale from a url
     *
     *  @param  string  $url
     *  @param  integer $segment     Index of the segment containing locale info
     *  @return string|null $locale
     */
    public function getLocaleFromUrl($url, $segment = 0)
    {
        return $this->parseUrl($url, $segment)['locale'];
    }

    /**
     *  Removes the domain and locale (if present) of a given url.
     *  Ex: http://www.domain.com/locale/random => /random, https://www.domain.com/random => /random, http://domain.com/random?param=value => /random?param=value
     *
     *  @param  string  $url
     *  @param  integer $segment     Index of the segment containing locale info
     *  @return string
     */
    public function cleanUrl($url, $segment = 0)
    {
        $parsedUrl = $this->parseUrl($url, $segment);
        // Remove locale from segments:
        if ($parsedUrl['locale']) {
            unset($parsedUrl['segments'][$segment]);
            $parsedUrl['locale'] = false;
        }
        return $this->pathFromParsedUrl($parsedUrl);
    }

    /**
     *  Parses the given url in a similar way to PHP's parse_url, with the following differences:
     *      Forward and trailling slashed are removed from the path value.
     *      A new "segments" key replaces 'path', with the uri segments in array form ('/es/random/thing' => ['es', 'random', 'thing'])
     *      A 'locale' key is added, with the value of the locale found in the current url
     *
     *  @param  string  $url
     *  @param  integer $segment     Index of the segment containing locale info
     *  @return mixed
     */
    protected function parseUrl($url, $segment = 0)
    {
        $parsedUrl             = parse_url($url);
        $parsedUrl['segments'] = array_values(array_filter(explode('/', $parsedUrl['path']), 'strlen'));
        $localeCandidate       = array_get($parsedUrl['segments'], $segment, false);
        $parsedUrl['locale']   = in_array($localeCandidate, $this->availableLocales) ? $localeCandidate : null;
        $parsedUrl['query']    = array_get($parsedUrl, 'query', false);
        $parsedUrl['fragment'] = array_get($parsedUrl, 'fragment', false);
        unset($parsedUrl['path']);
        return $parsedUrl;
    }

    /**
     *  Returns the uri for the given parsed url based on its segments, query and fragment
     *
     *  @return string
     */
    protected function pathFromParsedUrl($parsedUrl)
    {
        $path = '/' . implode('/', $parsedUrl['segments']);
        if ($parsedUrl['query']) {
            $path .= "?{$parsedUrl['query']}";
        }
        if ($parsedUrl['fragment']) {
            $path .= "#{$parsedUrl['fragment']}";
        }
        return $path;
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
