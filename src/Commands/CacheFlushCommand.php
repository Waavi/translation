<?php namespace Waavi\Translation\Commands;

use Illuminate\Console\Command;
use Waavi\Translation\Cache\CacheRepositoryInterface as CacheRepository;

class CacheFlushCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translator:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Flush the translation cache.";

    /**
     *  Create the cache flushed command
     *
     *  @param  \Waavi\Lang\Providers\LanguageProvider        $languageRepository
     *  @param  \Waavi\Lang\Providers\LanguageEntryProvider   $translationRepository
     *  @param  \Illuminate\Foundation\Application            $app
     */
    public function __construct(CacheRepository $cacheRepository, $cacheEnabled)
    {
        parent::__construct();
        $this->cacheRepository = $cacheRepository;
        $this->cacheEnabled    = $cacheEnabled;
    }

    /**
     *  Execute the console command.
     *
     *  @return void
     */
    public function handle()
    {
        if (!$this->cacheEnabled) {
            $this->info('The translation cache is disabled.');
        } else {
            $this->cacheRepository->flushAll();
            $this->info('Translation cache cleared.');
        }
    }
}
