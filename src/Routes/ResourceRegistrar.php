<?php

namespace Waavi\Translation\Routes;

use Illuminate\Routing\ResourceRegistrar as LRR;
use Illuminate\Routing\Router;
use Waavi\Translation\Repositories\LanguageRepository;

class ResourceRegistrar extends LRR
{
    /**
     * The language repository.
     *
     * @var array
     */
    protected $languageRepository;

    /**
     * Create a new resource registrar instance.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct(Router $router, LanguageRepository $languageRepository)
    {
        parent::__construct($router);
        $this->languageRepository = $languageRepository;
    }

    /**
     * Get the resource name for a grouped resource.
     *
     * @param  string  $prefix
     * @param  string  $resource
     * @param  string  $method
     * @return string
     */
    protected function getGroupResourceName($prefix, $resource, $method)
    {
        $availableLocales = $this->languageRepository->availableLocales();

        // Remove segments from group prefix that are equal to one of the available locales:
        $groupSegments = explode('/', $this->router->getLastGroupPrefix());
        $groupSegments = array_filter($groupSegments, function ($segment) use ($availableLocales) {
            return !in_array($segment, $availableLocales);
        });
        $group = trim(implode('.', $groupSegments), '.');

        if (empty($group)) {
            return trim("{$prefix}{$resource}.{$method}", '.');
        }

        return trim("{$prefix}{$group}.{$resource}.{$method}", '.');
    }
}
