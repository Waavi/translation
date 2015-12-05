<?php namespace Waavi\Translation\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use \App;

trait Translatable
{
    /**
     *  List of language entries related to translatable attributes.
     *
     *  @var array
     */
    public $translations = [];

    /**
     *  Register Model observer.
     *
     *  @return void
     */
    public static function bootTranslatable()
    {
        static::observe(new TranslatableObserver);
    }

    /**
     *  Hijack parent's getAttribute to get the translation of the given field instead of its value.
     *
     *  @param  string  $key  Attribute name
     *  @return mixed
     */
    public function getAttribute($attribute)
    {
        // Check if we've been request for the raw value of a translatable attribute
        if ($this->rawValueRequested($attribute)) {
            return $this->getRawValue($attribute);
        }
        // Check if a translation exists for the given attribute:
        if ($this->isTranslated($attribute)) {
            return $this->getTranslation($attribute);
        }
        // Return parent implementation
        return parent::getAttribute($attribute);
    }

    /**
     *  Hijack Eloquent's setAttribute to create a Language Entry, or update the existing one, when setting the value of this attribute.
     *
     *  @param  string  $attribute    Attribute name
     *  @param  string  $value  Text value in default locale.
     *  @return void
     */
    public function setAttribute($attribute, $value)
    {
        if ($this->isTranslatable($attribute) and !empty($value)) {
            // Flag language entry to be saved:
            $translation                     = $this->getTranslation($attribute, $value);
            $this->translations[$attribute] = compact('translation', 'value');
            // Set translation attribute:
            $translationAttribute                    = $this->getTranslationAttribute($attribute);
            $translationCode                         = $translation->group . '.' . $translation->item;
            $this->attributes[$translationAttribute] = $translationCode;
        }
        return parent::setAttribute($attribute, $value);
    }

    /**
     *  Get the language entry related to an attribute. If none exists, then created one.
     *
     *  @param  string $attribute
     *  @return Translation
     */
    public function getTranslation($attribute)
    {
        // Get the translation code related to this attribute:
        $translationAttribute  = $this->getTranslationAttribute($attribute);
        $translationCode       = array_get($this->attributes, $translationAttribute, false);
        $translationRepository = App::make('App\Translator\Repositories\TranslationRepository');
        $defaultLocale         = App::make('config')->get('app.locale');
        $defaultLanguage       = App::make('App\Translator\Repositories\LanguageRepository')->findByLocale($defaultLocale);
        $translation         = $translationRepository->getModel();
        // If a translation code is set, query the database:
        if ($translationCode) {
            $translation = $translationRepository->findByCodeAndLanguage($translationCode, $defaultLanguage) ?: $translationRepository->getModel();
        }
        // If no language entry was found, then set a new one:
        if (!$translation->exists) {
            $reflected                  = new \ReflectionClass($this);
            $translation->language_id = $defaultLanguage->id;
            $translation->namespace   = '*';
            $translation->group       = 'translatable';
            $translation->item        = strtolower($reflected->getShortName()) . '-' . strtolower($attribute) . '-' . Str::quickRandom();
        }
        return $translation;
    }

    /**
     *  Allow to query $model->rawAttribute to get the raw field value instead of the translation.
     *
     *  @param  string  $attribute
     *  @return string
     */
    public function getRawValue($attribute)
    {
        $rawAttribute = snake_case(str_replace('raw', '', $attribute));
        return $this->attributes[$rawAttribute];
    }

    /**
     *  Check if the attribute being queried is the raw value of a translatable attribute.
     *
     *  @param  string $attribute
     *  @return boolean
     */
    public function rawValueRequested($attribute)
    {
        if (strrpos($attribute, 'raw') === 0) {
            $rawAttribute = snake_case(str_replace('raw', '', $attribute));
            return isset($this->attributes[$rawAttribute]);
        }
        return false;
    }

    /**
     *  Return the translation related to a translatable attribute.
     *  If a translation does not exist yet, one will be created.
     *
     *  @param  string $attribute
     *  @return Translation
     */
    public function getTranslation($attribute)
    {
        $translationCode = array_get($this->attributes, $this->getTranslationAttribute($attribute), $attribute);
        $translation     = Lang::get($translationCode);
        return $translation != $translationCode ? $translation : parent::getAttribute($attribute);
    }

    /**
     *  Check if an attribute is translatable.
     *
     *  @return boolean
     */
    public function isTranslatable($attribute)
    {
        return in_array($attribute, $this->translatableAttributes);
    }

    /**
     *  Check if a translation exists for the given attribute.
     *
     *  @param  string $attribute
     *  @return boolean
     */
    public function isTranslated($attribute)
    {
        return $this->isTranslatable($attribute) && isset($this->attributes[$this->getTranslationAttribute($attribute)]);
    }

    /**
     *  Return the translation attribute name given the translatable attribute.
     *
     *  @param  string $attribute
     *  @return string
     */
    public function getTranslationAttribute($attribute)
    {
        return "{$attribute}_translation";
    }

    /**
     *  Return the translatable attributes array
     *
     *  @return  array
     */
    public function getTranslatableAttributes()
    {
        return $this->translatableAttributes;
    }
}
