<?php namespace Waavi\Translation\Repositories;

use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Application;
use Illuminate\Validation\Factory as Validator;
use Waavi\Translation\Models\Language;

class LanguageRepository extends Repository
{
    /**
     * The model being queried.
     *
     * @var \Waavi\Translation\Models\Language
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
     *  Default locale.
     *
     *  @var string
     */
    protected $defaultLocale;

    /**
     *  Default available locales in case of filesystem source.
     *
     *  @var string
     */
    protected $defaultAvailableLocales;

    /**
     *  Config repository.
     *
     *  @var Config
     */
    protected $config;

    /**
     *  Constructor
     *  @param  \Waavi\Translation\Models\Language      $model  Bade model for queries.
     *  @param  \Illuminate\Validation\Validator        $validator  Validator factory
     *  @return void
     */
    public function __construct(Language $model, Application $app)
    {
        $this->model                   = $model;
        $this->validator               = $app['validator'];
        $config                        = $app['config'];
        $this->defaultLocale           = $config->get('app.locale');
        $this->defaultAvailableLocales = $config->get('translator.available_locales', []);
        $this->config                  = $config;
    }

    /**
     *  Insert a new language entry into the database.
     *  If the attributes are not valid, a null response is given and the errors can be retrieved through validationErrors()
     *
     *  @param  array   $attributes     Model attributes
     *  @return boolean
     */
    public function create(array $attributes)
    {
        return $this->validate($attributes) ? Language::create($attributes) : null;
    }

    /**
     *  Insert a new language entry into the database.
     *  If the attributes are not valid, a null response is given and the errors can be retrieved through validationErrors()
     *
     *  @param  array   $attributes     Model attributes
     *  @return boolean
     */
    public function update(array $attributes)
    {
        return $this->validate($attributes) ? (boolean) Language::where('id', $attributes['id'])->update($attributes) : false;
    }

    /**
     *    Find a Language by its locale
     *
     *    @return Language | null
     */
    public function findByLocale($locale)
    {
        return $this->model->where('locale', $locale)->first();
    }

    /**
     *  Find a deleted Language by its locale
     *
     *  @return Language | null
     */
    public function findTrashedByLocale($locale)
    {
        return $this->model->onlyTrashed()->where('locale', $locale)->first();
    }

    /**
     *    Find all Languages except the one with the specified locale.
     *
     *    @return Language | null
     */
    public function allExcept($locale)
    {
        return $this->model->where('locale', '!=', $locale)->get();
    }

    /**
     *  Returns a list of all available locales.
     *
     *  @return array
     */
    public function availableLocales()
    {
        if ($this->config->has('translator.locales')) {
            return $this->config->get('translator.locales');
        }

        if ($this->config->get('translator.source') !== 'files') {
            if ($this->tableExists()) {
                $locales = $this->model->distinct()->get()->pluck('locale')->toArray();
                $this->config->set('translator.locales', $locales);
                return $locales;
            }
        }

        return $this->defaultAvailableLocales;
    }

    /**
     *  Checks if a language with the given locale exists.
     *
     *  @return boolean
     */
    public function isValidLocale($locale)
    {
        return $this->model->whereLocale($locale)->count() > 0;
    }

    /**
     *  Compute percentage translate of the given language.
     *
     *  @param  string   $locale
     *  @param  string   $referenceLocale
     *  @return int
     */
    public function percentTranslated($locale)
    {
        $lang          = $this->findByLocale($locale);
        $referenceLang = $this->findByLocale($this->defaultLocale);

        $langEntries      = $lang->translations()->count();
        $referenceEntries = $referenceLang->translations()->count();

        return $referenceEntries > 0 ? (int) round($langEntries * 100 / $referenceEntries) : 0;
    }

    /**
     *  Validate the given attributes
     *
     *  @param  array    $attributes
     *  @return boolean
     */
    public function validate(array $attributes)
    {
        $id    = array_get($attributes, 'id', 'NULL');
        $table = $this->model->getTable();
        $rules = [
            'locale' => "required|unique:{$table},locale,{$id}",
            'name'   => "required|unique:{$table},name,{$id}",
        ];
        $validator = $this->validator->make($attributes, $rules);
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
