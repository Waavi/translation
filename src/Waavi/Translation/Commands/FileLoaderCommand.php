<?php namespace Waavi\Translation\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class FileLoaderCommand extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'translator:load';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Load language files into the database.";

  /**
   *  Create a new mixed loader instance.
   *
   *  @param  \Waavi\Lang\Providers\LanguageProvider        $languageProvider
   *  @param  \Waavi\Lang\Providers\LanguageEntryProvider   $languageEntryProvider
   *  @param  \Illuminate\Foundation\Application            $app
   */
  public function __construct($languageProvider, $languageEntryProvider, $fileLoader)
  {
    parent::__construct();
    $this->languageProvider       = $languageProvider;
    $this->languageEntryProvider  = $languageEntryProvider;
    $this->fileLoader             = $fileLoader;
    $this->finder                 = new Filesystem();
    $this->path                   = app_path().'/lang';
  }

  /**
    * Execute the console command.
    *
    * @return void
    */
    public function fire()
    { 

        $this->loadFiles($this->finder->directories($this->path));
        
        $registeredNamespaces = $this->fileLoader->getHints();

        if(!empty($registeredNamespaces)) 
        {
            $this->loadNamespaceDirectories($registeredNamespaces);
        }
        
    }

    /**
     * check all directories inside registered namespaces
     * 
     * @param  array $registeredNamespaces
     * @return void
     */
    public function loadNamespaceDirectories($registeredNamespaces) 
    {
        foreach ($registeredNamespaces as $namespace => $directory) 
        {
            $this->loadFiles($this->finder->directories($directory), $namespace);
        }
    }

    /**
     * Load files to database
     * 
     * @param  array $directories
     * @param  string $namespace
     * @return void
     */
    public function loadFiles($directories, $namespace = null) 
    {
        foreach($directories as $directory) 
        {
            $locale = basename($directory);
            $language = $this->languageProvider->findByLocale($locale);
            if ($language) 
            {
                $langFiles = $this->finder->files($directory);
                foreach($langFiles as $langFile) 
                {
                    $group = str_replace(array($directory.'/', '.php'), '', $langFile);
                    $lines = $this->fileLoader->loadRawLocale($locale, $group, $namespace);
                    $this->languageEntryProvider->loadArray($lines, $language, $group, $namespace, $locale == $this->fileLoader->getDefaultLocale());
                }
            }
        }
    }
}