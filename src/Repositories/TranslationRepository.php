<?php namespace Waavi\Translation\Repositories;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Application;
use Illuminate\Support\NamespacedItemResolver;
use Waavi\Translation\Models\Translation;

class TranslationRepository extends Repository
{
    /**
     * @var \Illuminate\Database\Connection
     */
    protected $database;

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
        $this->database      = $app['db'];
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
     *  Insert or Update entry by translation code for the default locale.
     *
     *  @param  string  $code
     *  @param  string  $text
     *  @return boolean
     */
    public function updateDefaultByCode($code, $text)
    {
        list($namespace, $group, $item) = $this->parseCode($code);
        $locale                         = $this->defaultLocale;
        $translation                    = $this->model->whereLocale($locale)->whereNamespace($namespace)->whereGroup($group)->whereItem($item)->first();
        if (!$translation) {
            return $this->create(compact('locale', 'namespace', 'group', 'item', 'text'));
        }
        return $this->update($translation->id, $text);
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
     *  Delete all entries by code
     *
     *  @param  string  $code
     *  @return boolean
     */
    public function deleteByCode($code)
    {
        list($namespace, $group, $item) = $this->parseCode($code);
        $this->model->whereNamespace($namespace)->whereGroup($group)->whereItem($item)->delete();
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
            if (is_string($text)) {
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
     *  Return all items for a given locale, namespace and group
     *
     *  @param  string $locale
     *  @param  string $namespace
     *  @param  string $group
     *  @return array
     */
    public function getItems($locale, $namespace, $group)
    {
        return $this->model
            ->whereLocale($locale)
            ->whereNamespace($namespace)
            ->whereGroup($group)
            ->get()
            ->toArray();
    }

    /**
     *  Return all items formatted as if coming from a PHP language file.
     *
     *  @param  string $locale
     *  @param  string $namespace
     *  @param  string $group
     *  @return array
     */
    public function loadSource($locale, $namespace, $group)
    {
        return $this->model
            ->whereLocale($locale)
            ->whereNamespace($namespace)
            ->whereGroup($group)
            ->get()
            ->keyBy('item')
            ->map(function ($translation) {
                return $translation['text'];
            })
            ->toArray();
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
     *  List all entries in the default locale that do not exist for the target locale.
     *
     *  @param      string    $locale     Language to translate to.
     *  @param      integer   $perPage    If greater than zero, return a paginated list with $perPage items per page.
     *  @param      string    $text       [optional] Show only entries with the given text in them in the reference language.
     *  @return     Collection
     */
    public function untranslated($locale, $perPage = 0, $text = null)
    {
        $ids = $this->untranslatedQuery($locale)->pluck('id');

        $untranslated = $text ? $this->model->whereIn('id', $ids)->where('text', 'like', "%$text%") : $this->model->whereIn('id', $ids);

        return $perPage ? $untranslated->paginate($perPage) : $untranslated->get();
    }

    /**
     *  Find a random entry that is present in the default locale but not in the given one.
     *
     *  @param  string $locale       Locale to translate to.
     *  @return Translation
     */
    public function randomUntranslated($locale)
    {
        return $this->untranslatedQuery($locale)->inRandomOrder()->take(1)->pluck('id');
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
    public function findByLangCode($locale, $code)
    {
        list($namespace, $group, $item) = $this->parseCode($code);
        return $this->model->whereLocale($locale)->whereNamespace($namespace)->whereGroup($group)->whereItem($item)->first();
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

        return $this->model
            ->newQuery()
            ->select($table . '.text')
            ->from($table)
            ->leftJoin("{$table} as e", function ($join) use ($table, $text, $textLocale) {
                $join->on('e.namespace', '=', "{$table}.namespace")
                    ->on('e.group', '=', "{$table}.group")
                    ->on('e.item', '=', "{$table}.item");
            })
            ->where("{$table}.locale", $targetLocale)
            ->where('e.locale', $textLocale)
            ->where('e.text', $text)
            ->get()
            ->pluck('text')
            ->unique()
            ->toArray();
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
        $this->model->where('id', $id)->update(['unstable' => '0']);
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

    /**
     *  Parse a translation code into its components
     *
     *  @param  string $code
     *  @return boolean
     */
    public function parseCode($code)
    {
        $segments = (new NamespacedItemResolver)->parseKey($code);

        if (is_null($segments[0])) {
            $segments[0] = '*';
        }

        return $segments;
    }

    /**
     * Create and return a new query to identify untranslated records.
     *
     * @param string $locale
     * @return \Illuminate\Database\Query\Builder
     */
    protected function untranslatedQuery($locale)
    {
        $table = $this->model->getTable();

        return $this->database->table("$table as $table")
            ->select("$table.id")
            ->leftJoin("$table as e", function (JoinClause $query) use ($table, $locale) {
                $query->on('e.namespace', '=', "$table.namespace")
                    ->on('e.group', '=', "$table.group")
                    ->on('e.item', '=', "$table.item")
                    ->where('e.locale', '=', $locale);
            })
            ->where("$table.locale", $this->defaultLocale)
            ->whereNull("e.id");
    }
}
