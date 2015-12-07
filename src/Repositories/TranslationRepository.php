<?php namespace Waavi\Translation\Repositories;

use Illuminate\Foundation\Application;
use Illuminate\Validation\Factory as Validator;
use Waavi\Translation\Models\Language;
use Waavi\Translation\Models\Translation;

class TranslationRepository extends Repository
{
    /**
     * The model being queried.
     *
     * @var \Waavi\Translation\Models\Translation
     */
    protected $model;

    /**
     *  Validator
     *
     *  @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     *  Validation errors.
     *
     *  @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    public $rules = [
        'locale'    => 'required',
        'namespace' => '',         // Language Entry namespace. Default is *
        'group'     => 'required', // Entry group, references the name of the file the translation was originally stored in.
        'item'      => 'required', // Entry code.
        'text'      => 'required', // Translation text.
        'unstable'  => '',         // If this flag is set to true, the text in the default language has changed since this entry was last updated.
        'locked'    => '',         // If this flag is set to true, then this entry's text may not be edited.
    ];

    /**
     *  Constructor
     *  @param  \Waavi\Translation\Models\Translation   $model  Bade model for queries.
     *  @param  \Illuminate\Validation\Validator        $validator  Validator factory
     *  @return void
     */
    public function __construct(Translation $model, Application $app)
    {
        $this->model         = $model;
        $this->app           = $app;
        $this->defaultLocale = $app['config']->get('app.locale');
    }

    /**
     *  Insert a new translation into the database.
     *  If the attributes are not valid, a null response is given and the errors can be retrieved through validationErrors()
     *
     *  @param  array   $attributes     Model attributes
     *  @return boolean
     */
    public function create(array $attributes)
    {
        return $this->validate($attributes) ? Translation::create($attributes) : null;
    }

    /**
     *  Update a translation.
     *  If the translation is locked, no update will be made.
     *
     *  @param  array   $attributes     Model attributes
     *  @return boolean
     */
    public function update($id, $text)
    {
        $translation = $this->find($id);
        if (!$translation || $translation->isLocked()) {
            return false;
        }
        $translation->text = $text;
        $saved             = $translation->save();
        if ($saved && $translation->locale === $this->defaultLocale) {
            $this->flagAsUnstable($translation->namespace, $translation->group, $translation->item);
        }
        return $saved;
    }

    /**
     *  Update and lock translation. Locked translations will not be ovewritten when loading translation files into the database.
     *  This will force and update if the translation is locked.
     *  If the attributes are not valid, a null response is given and the errors can be retrieved through validationErrors()
     *
     *  @param  array   $attributes     Model attributes
     *  @return boolean
     */
    public function updateAndLock($id, $text)
    {
        $translation = $this->find($id);
        if (!$translation) {
            return false;
        }
        $translation->text = $text;
        $translation->lock();
        $saved = $translation->save();
        if ($saved && $translation->locale === $this->defaultLocale) {
            $this->flagAsUnstable($translation->namespace, $translation->group, $translation->item);
        }
        return $saved;
    }

    /**
     *  Delete a translation. If the translation is of the default language, delete all translations with the same namespace, group and item
     *
     *  @param  integer $id
     *  @return boolean
     */
    public function delete($id)
    {
        $translation = $this->find($id);
        if (!$translation) {
            return false;
        }

        if ($translation->locale === $this->defaultLocale) {
            return $this->model->whereNamespace($translation->namespace)->whereGroup($translation->group)->whereItem($translation->item)->delete();
        } else {
            return $translation->delete();
        }
    }

    /**
     *  Loads a localization array from a localization file into the databas.
     *
     *  @param  array   $lines
     *  @param  string  $locale
     *  @param  string  $group
     *  @param  string  $namespace
     *  @return void
     */
    public function loadArray(array $lines, $locale, $group, $namespace = '*')
    {
        // Transform the lines into a flat dot array:
        $lines = array_dot($lines);
        foreach ($lines as $item => $text) {
            // Check if the entry exists in the database:
            $translation = Translation::whereLocale($locale)
                ->whereNamespace($namespace)
                ->whereGroup($group)
                ->whereItem($item)
                ->first();

            // If the translation already exists, we update the text:
            if ($translation && !$translation->isLocked()) {
                $translation->text = $text;
                $saved             = $translation->save();
                if ($saved && $translation->locale === $this->defaultLocale) {
                    $this->flagAsUnstable($namespace, $group, $item);
                }
            }
            // If no entry was found, create it:
            else {
                $this->create(compact('locale', 'namespace', 'group', 'item', 'text'));
            }
        }
    }

    /**
     *  Return a list of translations for the given language. If perPage is > 0 a paginated list is returned with perPage items per page.
     *
     *  @param  string $locale
     *  @return Translation
     */
    public function allByLocale($locale, $perPage = 0)
    {
        $translations = $this->model->where('locale', $locale);
        return $perPage ? $translations->paginate($perPage) : $translations->get();
    }

    /**
     *  Find a random entry that is present in the default locale but not in the given one.
     *
     *  @param  string $locale       Locale to translate to.
     *  @return Translation
     */
    public function randomUntranslated($locale)
    {
        $table = $this->model->getTable();
        return $this->model
            ->newQuery()
            ->from($table)
            ->whereNotExists(function ($query) use ($table, $locale) {
                $query
                    ->from("$table as e")
                    ->where('e.locale', $locale)
                    ->where('e.namespace', "$table.namespace")
                    ->where('e.group', "$table.group")
                    ->where('e.item', "$table.item");
            })
            ->where("$table.locale", $this->defaultLocale)
            ->orderByRaw("RANDOM()")
            ->first();
    }

    /**
     *  List all entries in the default locale that do not exist for the target locale.
     *
     *  @param      string    $target     Language to translate to.
     *  @param      integer   $perPage    If greater than zero, return a paginated list with $perPage items per page.
     *  @param      string    $text       [optional] Show only entries with the given text in them in the reference language.
     *  @return     Collection
     */
    public function untranslated($locale, $perPage = 0, $text = null)
    {
        $untranslated = $text ? $this->model->where('text', 'like', "%$text%") : $this->model;
        $table        = $this->model->getTable();
        $untranslated = $untranslated
            ->newQuery()
            ->from($table)
            ->where("$table.locale", $this->defaultLocale)
            ->whereNotExists(function ($query) use ($table, $locale) {
                $query
                    ->from("$table as e")
                    ->where('e.locale', $locale)
                    ->where('e.namespace', "$table.namespace")
                    ->where('e.group', "$table.group")
                    ->where('e.item', "$table.item");
            });

        return $perPage ? $untranslated->paginate($perPage) : $untranslated->get();
    }

    /**
     *  Find a translation per namespace, group and item values
     *
     *  @param  string  $locale
     *  @param  string  $namespace
     *  @param  string  $group
     *  @param  string  $item
     *  @return Translation
     */
    public function findByCode($locale, $namespace, $group, $item)
    {
        return $this->model->whereLocale($locale)->whereNamespace($namespace)->whereGroup($group)->whereItem($item)->first();
    }

    /**
     *  Return all items for a given locale, namespace and group
     *
     *  @param  string $code
     *  @return Collection
     */
    public function getItems($locale, $namespace, $group)
    {
        return $this->model->whereLocale($locale)->whereNamespace($namespace)->whereGroup($group)->get();
    }

    /**
     *  Retrieve translations pending review for the given locale.
     *
     *  @param  string  $locale
     *  @param  int     $perPage    Number of elements per page. 0 if all are wanted.
     *  @return Translation
     */
    public function pendingReview($locale, $perPage = 0)
    {
        $underReview = $this->model->whereLocale($locale)->whereUnstable(1);
        return $perPage ? $underReview->paginate($perPage) : $underReview->get();
    }

    /**
     *  Search for entries given a partial code and a locale
     *
     *  @param  string  $locale
     *  @param  string  $partialCode
     *  @param  integer $perPage        0 if all, > 0 if paginated list with that number of elements per page.
     *  @return Translation
     */
    public function search($locale, $partialCode, $perPage = 0)
    {
        // Get the namespace, if any:
        $colonIndex = stripos($partialCode, '::');
        $query      = $this->model->whereLocale($locale);
        if ($colonIndex === 0) {
            $query = $query->where('namespace', '!=', '*');
        } elseif ($colonIndex > 0) {
            $namespace   = substr($partialCode, 0, $colonIndex);
            $query       = $query->where('namespace', 'like', "%{$namespace}%");
            $partialCode = substr($partialCode, $colonIndex + 2);
        }

        // Divide the code in segments by .
        $elements = explode('.', $partialCode);
        foreach ($elements as $element) {
            if ($element) {
                $query = $query->where(function ($query) use ($element) {
                    $query->where('group', 'like', "%{$element}%")->orWhere('item', 'like', "%{$element}%")->orWhere('text', 'like', "%{$element}%");
                });
            }
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     *  Check if there are existing translations for the given text in the given locale for the target locale.
     *
     *  @param  string  $text
     *  @param  string  $textLocale
     *  @param  string  $targetLocale
     *  @return array
     */
    public function translateText($text, $textLocale, $targetLocale)
    {
        $table = $this->model->getTable();

        $results = $this->model
            ->join("{$table} as e", function ($join) use ($table, $text, $textLocale) {
                $join->on('e.namespace', '=', "{$table}.namespace")
                    ->on('e.group', '=', "{$table}.group")
                    ->on('e.item', '=', "{$table}.item")
                    ->on('e.locale', '=', $textLocale)
                    ->on('e.text', '=', $text);
            })
            ->whereRaw($targetLocale)
            ->groupBy("{$table}.text")
            ->get()
            ->toArray();

        return array_pluck($results, 'text');
    }

    /**
     *  Flag all entries with the given namespace, group and item and locale other than default as pending review.
     *  This is used when an entry for the default locale is updated.
     *
     *  @param Translation $entry
     *  @return boolean
     */
    public function flagAsUnstable($namespace, $group, $item)
    {
        $this->model->whereNamespace($namespace)->whereGroup($group)->whereItem($item)->where('locale', '!=', $this->defaultLocale)->update(['unstable' => '1']);
    }

    /**
     *  Flag the entry with the given id as reviewed.
     *
     *  @param  integer $id
     *  @return boolean
     */
    public function flagAsReviewed($id)
    {
        $translation = $this->find($id);
        if (!$translation) {
            return false;
        }
        $translation->flagAsReviewed();
        return $translation->save();
    }

    /**
     *  Validate the given attributes
     *
     *  @param  array    $attributes
     *  @return boolean
     */
    public function validate(array $attributes)
    {
        $table     = $this->model->getTable();
        $locale    = array_get($attributes, 'locale', '');
        $namespace = array_get($attributes, 'namespace', '');
        $group     = array_get($attributes, 'group', '');
        $rules     = [
            'locale'    => 'required',
            'namespace' => 'required',
            'group'     => 'required',
            'item'      => "required|unique:{$table},item,NULL,id,locale,{$locale},namespace,{$namespace},group,{$group}",
            'text'      => '', // Translations may be empty
        ];
        $validator = $this->app['validator']->make($attributes, $rules);
        if ($validator->fails()) {
            $this->errors = $validator->errors();
            return false;
        }
        return true;
    }

    /**
     *  Returns the validations errors of the last action executed.
     *
     *  @return \Illuminate\Support\MessageBag
     */
    public function validationErrors()
    {
        return $this->errors;
    }
}
