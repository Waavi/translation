<?php namespace Waavi\Translation\Repositories;

use Waavi\Translation\Models\Language;

class LanguageRepository
{
    /**
     * The model being queried.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function __construct(Language $model)
    {
        $this->model = $model;
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
