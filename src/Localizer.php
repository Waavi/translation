<?php namespace Waavi\Translation;

use Illuminate\Support\Facades\Event;
use Waavi\Translation\Models\Language;
use Waavi\Translation\Models\LanguageEntry;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Repositories\TranslationRepository;

class Localizer
{
    /**
     *  Language finder (repository)
     *  @var LanguageRepositoryInterface
     */
    protected $languageRepository;

    /**
     *  Language entry finder (repository)
     *  @var LanguageEntryRepositoryInterface
     */
    protected $translationRepository;

    public function __construct(LanguageRepository $languageRepository, TranslationRepository $translationRepository)
    {
        $this->languageRepository    = $languageRepository;
        $this->translationRepository = $translationRepository;
    }

    /**
     *  Transforms a uri into one containing the current locale slug.
     *  Examples: login/ => /es/login . / => /es
     *
     *  @param string $uri Current uri.
     *  @return string Target uri.
     */
    public function localizeUri($locale, $uri)
    {
        $frontDomain = \Config::get('app.domains.front');
        $adminDomain = \Config::get('app.domains.admin');
        $uri         = str_replace('http://', "", $uri);
        $uri         = str_replace($frontDomain, "", $uri);
        $uri         = str_replace($adminDomain, "", $uri);
        // Delete the forward slash if any at the beginning of the uri:
        $uri      = substr($uri, 0, 1) == '/' ? substr($uri, 1) : $uri;
        $segments = array_filter(explode('/', $uri));
        $newUri   = "/{$locale}";
        // Start on the second segment if the first is a locale:
        $index = count($segments) > 0 && strlen($segments[0]) == '2' ? 1 : 0;
        for ($i = $index; $i < count($segments); $i++) {
            $newUri .= "/{$segments[$i]}";
        }
        return $newUri;
    }

    /**
     *  Extract the first valid locale from an array
     *  @param  array $candidates
     *  @return string|null $locale
     */
    public function extractFirstValidLocale(array $candidates)
    {
        $locales = $this->languageRepository->all()->lists('locale')->toArray();
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $locales)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     *  Compute percentage translate of the given language with respect to the reference language.
     *
     *  @param  Language   $language
     *  @param  Language   $reference
     *  @return int
     */
    public function percentTranslated(Language $language, Language $reference)
    {
        $referenceNumEntries = $reference->entries()->count();
        $languageNumEntries  = $language->entries()->count();

        if (!$referenceNumEntries) {
            return 0;
        }

        return round($languageNumEntries * 100 / $referenceNumEntries);
    }

    /**
     *  Get entries pending translation.
     *
     *  @param  string $referenceLocale   Locale for the language considered the default.
     *  @param  string $targetLocale      Locale for the language for which we wish to get the pending translations.
     *  @return LanguageEntry             Collection of LanguageEntry objects belonging to the reference language that do not have a translation in the target language.
     */
    public function pendingTranslation($referenceLocale, $targetLocale, $perPage = 0)
    {
        $reference = $this->languageRepository->findByLocale($referenceLocale);
        $target    = $this->languageRepository->findByLocale($targetLocale);
        return $this->languageRepository->getUntranslated($reference, $target, $perPage);
    }

    /**
     *  Get translations that have to be reviewed after an update to the original text.
     *
     *  @param  string $locale
     *  @return LanguageEntry    Collection of LanguageEntry objects that have been flagged as unstable.
     */
    public function pendingReview($locale, $perPage = 0)
    {
        $language = $this->languageRepository->findByLocale($locale);
        $entries  = $this->translationRepository->getUnderReview($language, $perPage);
        return $entries;
    }

    /**
     *  Get entries for the given language
     *
     *  @param  string $locale
     *  @return LanguageEntry
     */
    public function listEntries($locale, $perPage = 0)
    {
        $language = $this->languageRepository->findByLocale($locale);
        $entries  = $this->translationRepository->getByLanguage($language, $perPage);
        return $entries;
    }

    /**
     *  Get the translation for a given entry in a given language.
     *
     *  @param  LanguageEntry   $entry
     *  @param  string          $locale
     *  @return string
     */
    public function translate(LanguageEntry $entry, $locale)
    {
        $language = $this->languageRepository->findByLocale($locale);
        if (!$language) {
            return $entry;
        }
        $translation = $this->translationRepository->findTranslation($entry, $language);
        if (!$translation) {
            return $entry;
        }
        return $translation;
    }

    /**
     *  Update an entry even if its locked.
     *
     *  @param  AppRequest Request with language_entry_id, language_id, namespace (optional), group, item, text
     *  @return AppResponse
     */
    public function updateDefault(LanguageEntry $entry, $text)
    {
        $entry->setTextAndLock($text);
        $this->languageEntryRepository->flagTranslationsAsUnstable($entry);
        return true;
    }

    /**
     *  Insert a new translation.
     *
     *  @param  integer   $language_id
     *  @param  string    $group
     *  @param  string    $item
     *  @param  string    $text
     *  @param  string    $namespace    Optional. Default '*'
     *  @throws ValidatorException
     *  @return LanguageEntry
     */
    public function insert($language_id, $group, $item, $text, $namespace = '*')
    {
        $entry = $this->translationRepository->create(compact('language_id', 'group', 'item', 'text', 'namespace'));
        Event::fire('translation.new', [$entry]);
        return $entry;
    }
}
