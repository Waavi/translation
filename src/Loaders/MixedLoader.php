<?php namespace Waavi\Translation\Loaders;

class MixedLoader extends Loader
{
    /**
     *  The default locale.
     *  @var string
     */
    protected $defaultLocale;

    /**
     *  The file loader.
     *  @var \Waavi\Translation\Loaders\Loader
     */
    protected $primaryLoader;

    /**
     *  The database loader.
     *  @var \Waavi\Translation\Loaders\Loader
     */
    protected $secondaryLoader;

    /**
     *  Create a new mixed loader instance.
     *
     *  @param  string  $defaultLocale
     *  @param  Loader  $primaryLoader
     *  @param  Loader  $secondaryLoader
     */
    public function __construct($defaultLocale, Loader $primaryLoader, Loader $secondaryLoader)
    {
        parent::__construct($defaultLocale);
        $this->primaryLoader   = $primaryLoader;
        $this->secondaryLoader = $secondaryLoader;
    }

    /**
     *  Load the messages strictly for the given locale.
     *
     *  @param  string   $locale
     *  @param  string   $group
     *  @param  string   $namespace
     *  @return array
     */
    public function loadSource($locale, $group, $namespace = '*')
    {
        return array_replace_recursive(
            $this->secondaryLoader->loadSource($locale, $group, $namespace),
            $this->primaryLoader->loadSource($locale, $group, $namespace)
        );
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
        $this->primaryLoader->addNamespace($namespace, $hint);
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string  $path
     * @return void
     */
    public function addJsonPath($path)
    {
        //
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
