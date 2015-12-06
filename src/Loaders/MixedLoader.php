<?php namespace Waavi\Translation\Loaders;

use Illuminate\Translation\LoaderInterface;

class MixedLoader extends Loader implements LoaderInterface
{
    /**
     *  The default locale.
     *  @var string
     */
    protected $defaultLocale;

    /**
     *  The file loader.
     *  @var \Waavi\Translation\Loaders\FileLoader
     */
    protected $fileLoader;

    /**
     *  The database loader.
     *  @var \Waavi\Translation\Loaders\DatabaseLoader
     */
    protected $databaseLoader;

    /**
     *  Create a new mixed loader instance.
     *
     *  @param  string          $defaultLocale
     *  @param  FileLoader      $fileLoader
     *  @param  DatabaseLoader  $databaseLoader
     */
    public function __construct($defaultLocale, FileLoader $fileLoader, DatabaseLoader $databaseLoader)
    {
        parent::__construct($defaultLocale);
        $this->fileLoader     = $fileLoader;
        $this->databaseLoader = $databaseLoader;
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
            $this->databaseLoader->loadSource($locale, $group, $namespace),
            $this->fileLoader->loadSource($locale, $group, $namespace)
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
        $this->fileLoader->addNamespace($namespace, $hint);
    }
}
