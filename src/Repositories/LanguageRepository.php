<?php namespace Waavi\Translation\Repositories;

use Waavi\Translation\Models\Language;

class LanguageRepository extends Repository
{
    /**
     * The model being queried.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     *  Validation rules
     *  @var array
     */
    public $rules = [
        'locale' => 'required|unique:languages',
        'name'   => 'required|unique:languages',
    ];

    public function __construct(Language $model)
    {
        $this->model = $model;
    }

    public function create(array $fields)
    {
        return Language::create($fields);
    }

    public function availableLocales()
    {
        return $this->model->all()->lists('locale')->toArray();
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
     *  Find a Language by its locale
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
}
