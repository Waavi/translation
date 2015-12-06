<?php namespace Waavi\Translation\Repositories;

use Waavi\Translation\Models\Language;
use Waavi\Translation\Models\Translation;

class TranslationRepository extends Repository
{
    /**
     * The model being queried.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public $rules = [
        'locale'    => 'required',
        'namespace' => '',         // Language Entry namespace. Default is *
        'group'     => 'required', // Entry group, references the name of the file the translation was originally stored in.
        'item'      => 'required', // Entry code.
        'text'      => 'required', // Translation text.
        'unstable'  => '',         // If this flag is set to true, the text in the default language has changed since this entry was last updated.
        'locked'    => '',         // If this flag is set to true, then this entry's text may not be edited.
    ];

    public function __construct(Translation $model)
    {
        $this->model = $model;
    }

    protected function createModel()
    {
        return new Translation;
    }

    /**
     *  Loads messages into the database
     *  @param array            $lines
     *  @param string     $locale
     *  @param string       $group
     *  @param string       $namespace
     *  @param boolean      $isDefault
     *  @return void
     */
    public function loadArray(array $lines, $locale, $group, $namespace = '*', $isDefault = false)
    {
        // Transform the lines into a flat dot array:
        $lines = array_dot($lines);
        foreach ($lines as $item => $text) {
            // Check if the entry exists in the database:
            $translation = $this
                ->createModel()
                ->newQuery()
                ->where('namespace', $namespace)
                ->where('group', $group)
                ->where('item', $item)
                ->where('locale', $locale)
                ->first();

            // If the translation already exists, we update the text:
            if ($translation) {
                $translation->updateText($text, $isDefault);
            }
            // The entry doesn't exist:
            else {
                $this->create(compact('locale', 'namespace', 'group', 'item', 'text'));
            }
        }
    }

    /**
     *  Return a paginated list of translation for the given language.
     *
     *  @param  Language $language
     *  @return Translation
     */
    public function getByLanguage(Language $language, $perPage = 0)
    {
        $translations = $this->model->where('language_id', $language->id);
        return $perPage ? $translations->paginate($perPage) : $translations->get();
    }

    /**
     *  Find a random unstranslated snippet in the reference language in the target language.
     *
     *  @param    Language $reference    Language to translate from.
     *  @param    Language $target       Language to translate to.
     *  @return   Translation
     */
    public function findUntranslated(Language $reference, Language $target)
    {
        return $this->model
            ->where('language_id', '=', $reference->id)
            ->whereNotExists(function ($query) use ($untranslated, $reference, $target) {
                $table = $untranslated->getTable();
                $query
                    ->from("$table as e")
                    ->where('language_id', '=', $target->id)
                    ->whereRaw("(e.namespace = $table.namespace OR (e.namespace IS NULL AND $table.namespace IS NULL))")
                    ->whereRaw("e.group = $table.group")
                    ->whereRaw("e.item = $table.item");
            })
            ->orderByRaw("RAND()")->first();
    }

    /**
     *  List all unstranslated entries in the reference language for the target language.
     *
     *  @param      Language    $reference  Language to translate from.
     *  @param      Language    $target     Language to translate to.
     *  @param      integer     $perPage    If greater than zero, return a paginated list with $perPage items per page.
     *  @param      string      $text       [optional] Show only entries with the given text in them in the reference language.
     *  @return     Translation
     */
    public function filterUntranslated(Language $reference, Language $target, $perPage = 0, $text = null)
    {
        $untranslated = $this->model
            ->where('language_id', '=', $reference->id)
            ->whereNotExists(function ($query) use ($untranslated, $reference, $target) {
                $table = $untranslated->getTable();
                $query
                    ->from("$table as e")
                    ->where('language_id', '=', $target->id)
                    ->whereRaw("(e.namespace = $table.namespace OR (e.namespace IS NULL AND $table.namespace IS NULL))")
                    ->whereRaw("e.group = $table.group")
                    ->whereRaw("e.item = $table.item")
                ;
            });
        if ($text) {
            $untranslated = $untranslated->where('text', 'like', "%$text%");
        }
        return $perPage ? $untranslated->paginate($perPage) : $untranslated->get();
    }

    /**
     *  Find the given entry's translation in the given language.
     *
     *  @param  Translation   $entry
     *  @param  Language        $language
     *  @return Translation
     */
    public function findTranslation(Translation $entry, Language $language)
    {
        return $this->model->where('language_id', $language->id)->where('namespace', $entry->namespace)->where('group', $entry->group)->where('item', $entry->item)->first();
    }

    /**
     *  Retrieve entries pending review for the given language.
     *
     *  @param  Language  $language
     *  @param  int       $perPage    Number of elements per page. 0 if all are wanted.
     *  @return Translation
     */
    public function getUnderReview(Language $language, $perPage = 0)
    {
        $underReview = $this->model->where('language_id', $language->id)->where('unstable', 1);
        return $perPage ? $underReview->paginate($perPage) : $underReview->get();
    }

    /**
     *  Get translation suggestions in the given language for the given translation
     *
     *  @param      Language       $language Language in which the suggestions must be presented
     *  @param      Translation    $entry
     *  @return     Translation
     */
    public function getSuggestions(Language $language, Translation $entry)
    {
        $table = $this->model->getTable();
        return $language->entries()
            ->select("{$table}.*")
            ->join("{$table} as e", function ($join) use ($table) {
                $join
                    ->on('e.group', '=', "{$table}.group")
                    ->on('e.item', '=', "{$table}.item");
            })
            ->where('e.language_id', '=', $entry->language_id)
            ->where('e.text', '=', "{$entry->text}")
            ->groupBy("{$table}.text")
            ->get();
    }

    /**
     *  Update a new record.
     *
     *  @param  Translation $entry
     *  @param  string $text
     *  @return boolean
     */
    public function updateTranslation(Translation $entry, $text)
    {
        return $entry->setText($text);
    }

    /**
     *  Updates a text source.
     *
     *  @param Translation  $entry
     *  @return boolean
     */
    public function updateSource(Translation $entry, $text)
    {
        $saved = $entry->setTextAndLock($text);
        if ($saved) {
            $this->model->where('namespace', $entry->namespace)
                ->where('group', $entry->group)
                ->where('item', $entry->item)
                ->where('language_id', '!=', $entry->language_id)
                ->update(['unstable' => '1']);
        }
        return $saved;
    }

    /**
     *  Flag siblings as unstable
     *
     *  @param Translation $entry
     *  @return boolean
     */
    public function flagTranslationsAsUnstable(Translation $entry)
    {
        $this->model->where('namespace', '=', $entry->namespace)
            ->where('group', '=', $entry->group)
            ->where('item', '=', $entry->item)
            ->where('language_id', '!=', $entry->language_id)
            ->update(['unstable' => '1']);
    }

    /**
     *  Return all translations for the given locale, group and namespace.
     *
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return Translation
     */
    public function getGroup($locale, $group, $namespace)
    {
        return $this->model->where('locale', $locale)->where('group', $group)->where('namespace', $namespace)->get();
    }

    /**
     *  Return all entries with the given code.
     *
     *  @param  string $code
     *  @return Collection
     */
    public function getByCode($code)
    {
        list($namespace, $group, $item) = Lang::parseKey($code);
        $results                        = $this->model->where('namespace', $namespace)->where('group', $group)->where('item', $item)->get();
        return $results;
    }

    /**
     *  Delete all language entries with the given code:
     *
     *  @param  string $code
     *  @return void
     */
    public function deleteByCode($code)
    {
        list($namespace, $group, $item) = Lang::parseKey($code);
        $this->model->where('namespace', $namespace)->where('group', $group)->where('item', $item)->delete();
    }

    /**
     *  Return an entry with the given code and locale.
     *
     *  @param  string $code
     *  @param  Language $language
     *  @return Translation
     */
    public function findByCodeAndLanguage($code, $language)
    {
        list($namespace, $group, $item) = Lang::parseKey($code);
        return $this->model->where('language_id', $language->id)->where('namespace', $namespace)->where('group', $group)->where('item', $item)->first();
    }

    public function lock(Translation $translation)
    {
        $translation->lock();
        $translation->save();
    }

    public function isValid($data)
    {
        $validator = \Validator::make($data, $this->rules);
        return $validator->passes();
        $clone          = new LanguageEntry;
        $duplicatedCode = $clone
            ->where('language_id', $this->language_id)
            ->where('namespace', $this->namespace)
            ->where('group', $this->group)
            ->where('item', $this->item)
            ->count() > 0;
        if (!$this->exists && $duplicatedCode) {
            $this->errors()->add('code', Lang::get('validation.unique', ['attribute' => 'code']));
            throw new ValidatorException($this->errors());
        }
        return true;
    }
}
