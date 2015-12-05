<?php namespace Waavi\Translation\Traits;

use Waavi\Translation\Models\Translation;
use Waavi\Translation\Repositories\TranslationRepository;

class TranslatableObserver
{
    /**
     *  Save translations when model is saved.
     *
     *  @param  Model $model
     *  @return void
     */
    public function saved($model)
    {
        foreach ($model->languageEntries as $pendingUpdate) {
            extract($pendingUpdate);
            $languageEntry->setText($value);
        }
    }

    /**
     *  Delete translations when model is deleted.
     *
     *  @param  Model $model
     *  @return void
     */
    public function deleted($model)
    {
        $repository = new TranslationRepository(new Translation);
        foreach ($model->getTranslatableAttributes() as $attribute) {
            $transAttribute = $attribute . '_translation';
            $repository->deleteByCode($model->$transAttribute);
        }
    }
}
