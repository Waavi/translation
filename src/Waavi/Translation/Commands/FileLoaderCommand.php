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
    $this->path                   = app_path().DIRECTORY_SEPARATOR.'lang';
  }

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$localeDirs = $this->finder->directories($this->path);

		foreach($localeDirs as $localeDir)
		{
			$locale = str_replace($this->path.'/', '', $localeDir);
			$language = $this->languageProvider->findByLocale($locale);

			if ($language)
			{
				$langFiles = $this->finder->files($localeDir);
				$langDirectories = $this->finder->directories($localeDir);

				foreach($langDirectories as $langDirectory)
				{
					$group = str_replace($localeDir.'/', '', $langDirectory);
					$lines = $this->fireSubDir($localeDir, $langDirectory, $locale);
					$this->languageEntryProvider->loadArray($lines, $language, $group, null, $locale == $this->fileLoader->getDefaultLocale());
				}

				foreach($langFiles as $langFile)
				{
					$group = str_replace(array($localeDir.'/', '.php'), '', $langFile);
					$lines = $this->fileLoader->loadRawLocale($locale, $group);
					$this->languageEntryProvider->loadArray($lines, $language, $group, null, $locale == $this->fileLoader->getDefaultLocale());
				}
			}
		}
	}

	/**
	 * Get translations from subfolders.
	 *
	 * @return array
	 */
	private function fireSubDir($localeDir, $langDirectory, $locale)
	{
		$array    = array();
		$allFiles = $this->finder->allFiles($langDirectory);

		foreach($allFiles as $file)
		{
			$filePathname = $file->getPathname();
			$relativePath = $file->getRelativePath();
			$dirGroup = str_replace(array($localeDir.'/', '.php'), '', $filePathname);
			$fileGroup = str_replace(array($langDirectory.'/', '.php'), '', $filePathname);

			$lines = $this->fileLoader->loadRawLocale($locale, $dirGroup);

			if( empty($relativePath) )
			{
				$lines = $this->fileLoader->loadRawLocale($locale, $dirGroup);
				$array = array_merge($array, array($fileGroup => $lines) );
			}
			else
			{
				$lines = $this->fileLoader->loadRawLocale($locale, $dirGroup);
				$fileDot = str_replace('/', '.', $fileGroup);
				$array = array_merge_recursive($array, array_undot(array($fileDot => $lines)));
			}
		}

		return $array;
	}
}
