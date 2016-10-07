<?php namespace Waavi\Translation\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Waavi\Translation\Loaders\Loader;
use Illuminate\Translation\LoaderInterface;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;

class FileLoaderCommand extends Command
{
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
     *  @param  \Waavi\Lang\Providers\LanguageProvider        $languageRepository
     *  @param  \Waavi\Lang\Providers\LanguageEntryProvider   $translationRepository
     *  @param  \Illuminate\Foundation\Application            $app
     */
    public function __construct(LanguageRepository $languageRepository, TranslationRepository $translationRepository, Filesystem $files, LoaderInterface $loader, $translationsPath, $defaultLocale)
    {
        parent::__construct();
        $this->languageRepository    = $languageRepository;
        $this->translationRepository = $translationRepository;
        $this->path                  = $translationsPath;
        $this->files                 = $files;
        $this->defaultLocale         = $defaultLocale;
        $this->loader                = $loader;
    }

    /**
     *  Execute the console command.
     *
     *  @return void
     */
    public function fire()
    {
        // First attempt to load any hinted namespaces from, the loader.
        // We can only do this if we are using the Waavi translation
        // loader, since the default hints variable is protected.
        if ($this->loader instanceof Loader && isset($this->loader->hints)) {
            foreach ($this->loader->hints as $namespace => $path) {
                $this->loadLocaleDirectories($path, $namespace);
            }
        }

        // Next, load the language files from the resources folder.
        // This way namespaces published to the resources path
        // can overwrite the translations loaded in above.
        $this->loadLocaleDirectories($this->path);
    }

    /**
     *  Loads all locale directories in the given path (/en, /es, /fr) as long as the locale corresponds to a language in the database.
     *  If a vendor directory is found not inside another vendor directory, the files within it will be loaded with the corresponding namespace.
     *
     *  @param  string  $path           Full path to the root directory of the locale directories. Usually /path/to/laravel/resources/lang
     *  @param  string  $namespace      Namespace where the language files should be inserted.
     *  @return void
     */
    public function loadLocaleDirectories($path, $namespace = '*')
    {
        $availableLocales = $this->languageRepository->availableLocales();
        $directories      = $this->files->directories($path);
        foreach ($directories as $directory) {
            $locale = basename($directory);
            if (in_array($locale, $availableLocales)) {
                $this->loadDirectory($directory, $locale, $namespace);
            }
            if ($locale === 'vendor' && $namespace === '*') {
                $this->loadVendor($directory);
            }
        }
    }

    /**
     *  Load all vendor overriden localization packages. Calls loadLocaleDirectories with the appropriate namespace.
     *
     *  @param  string  $path   Path to vendor locale root, usually /path/to/laravel/resources/lang/vendor.
     *  @see    http://laravel.com/docs/5.1/localization#overriding-vendor-language-files
     *  @return void
     */
    public function loadVendor($path)
    {
        $directories = $this->files->directories($path);
        foreach ($directories as $directory) {
            $namespace = basename($directory);
            $this->loadLocaleDirectories($directory, $namespace);
        }
    }

    /**
     *  Load all files inside a locale directory and its subdirectories.
     *
     *  @param  string  $path       Path to locale root. Ex: /path/to/laravel/resources/lang/en
     *  @param  string  $locale     Locale to apply when loading the localization files.
     *  @param  string  $namespace  Namespace to apply when loading the localization files ('*' by default, or the vendor package name if not)
     *  @param  string  $group      When loading from a subdirectory, the subdirectory's name must be prepended. For example: trans('subdir/file.entry').
     *  @return void
     */
    public function loadDirectory($path, $locale, $namespace = '*', $group = '')
    {
        // Load all files inside subdirectories:
        $directories = $this->files->directories($path);
        foreach ($directories as $directory) {
            $directoryName = str_replace($path . '/', '', $directory);
            $dirGroup      = $group . basename($directory) . '/';
            $this->loadDirectory($directory, $locale, $namespace, $dirGroup);
        }

        // Load all files in root:
        $files = $this->files->files($path);
        foreach ($files as $file) {
            $this->loadFile($file, $locale, $namespace, $group);
        }
    }

    /**
     *  Loads the given file into the database
     *
     *  @param  string  $path           Full path to the localization file. For example: /path/to/laravel/resources/lang/en/auth.php
     *  @param  string  $locale
     *  @param  string  $namespace
     *  @param  string  $group          Relative from the locale directory's root. For example subdirectory/subdir2/
     *  @return void
     */
    public function loadFile($file, $locale, $namespace = '*', $group = '')
    {
        $group        = $group . basename($file, '.php');
        $translations = $this->files->getRequire($file);
        $this->translationRepository->loadArray($translations, $locale, $group, $namespace, $locale == $this->defaultLocale);
    }
}
