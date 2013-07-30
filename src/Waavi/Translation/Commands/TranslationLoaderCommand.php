<?php namespace Waavi\Translation\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TranslationLoaderCommand extends Command {

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function loadFilesIntoDatabase()
  {
    $path = app_path().'/lang';
    $finder = new Filesystem();
    $localeDirs = $finder->directories($path);
    foreach($localeDirs as $localeDir) {
      $locale = str_replace($path.'/', '', $localeDir);
      $language = $this->languageProvider->findByLocale($locale);
      $langFiles = $finder->files($localeDir);
      if ($language) {
        foreach($langFiles as $langFile) {
          $group = str_replace('.php', '', str_replace($localeDir.'/', '', $langFile));
          $lines = $this->loadFromFile($locale, $group);
          $this->languageEntryProvider->loadArray($lines, $language, $group);
        }
      }
    }
  }

    /**
     * Provide user feedback, based on success or not.
     *
     * @param  boolean $successful
     * @param  string $path
     * @return void
     */
    protected function printResult($successful, $path)
    {
        if ($successful)
        {
            return $this->info("Created {$path}");
        }

        $this->error("Could not create {$path}");
    }

    /**
     * Get the path to the file that should be generated.
     *
     * @return string
     */
    protected function getPath()
    {
       return $this->option('path') . '/' . strtolower($this->argument('name')) . '.blade.php';
    }

}