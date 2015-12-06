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
}
