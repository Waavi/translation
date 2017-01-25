<?php namespace Waavi\Translation\Loaders;

use Illuminate\Translation\LoaderInterface;
use Waavi\Translation\Repositories\TranslationRepository;

class DatabaseLoader extends Loader implements LoaderInterface
{
    /**
     *  The default locale.
     *  @var string
     */
    protected $defaultLocale;

    /**
     *  Translations repository.
     *  @var \Waavi\Translation\Repositories\TranslationRepository
     */
    protected $translationRepository;

    /**
     *  Create a new mixed loader instance.
     *
     *  @param  string                                                  $defaultLocale
     *  @param  \Waavi\Translation\Repositories\TranslationRepository   $translationRepository
     */
    public function __construct($defaultLocale, TranslationRepository $translationRepository)
    {
        parent::__construct($defaultLocale);
        $this->translationRepository = $translationRepository;
    }

    /**
     *  Load the messages strictly for the given locale.
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return array
     */
    public function loadSource($locale, $group, $namespace = '*')
    {
        return $this->translationRepository->loadSource($locale, $namespace, $group);
    }

    /**
     *  Add a new namespace to the loader.
     *
     *  @param  string  $namespace
     *  @param  string  $hint
     *  @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces()
    {
        return $this->hints;
    }
}
