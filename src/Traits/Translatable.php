<?php namespace Waavi\Translation\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait Translatable
{
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
        // Return the raw value of a translatable attribute if requested
        if ($this->rawValueRequested($attribute)) {
            $rawAttribute = snake_case(str_replace('raw', '', $attribute));
            return $this->attributes[$rawAttribute];
        }
        // Return the translation for the given attribute if available
        if ($this->isTranslated($attribute)) {
            return $this->translate($attribute);
        }
        // Return parent
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
        if ($this->isTranslatable($attribute) && !empty($value)) {
            // If a translation code has not yet been set, generate one:
            if (!$this->translationCodeFor($attribute)) {
                $reflected                                    = new \ReflectionClass($this);
                $group                                        = 'translatable';
                $item                                         = strtolower($reflected->getShortName()) . '.' . strtolower($attribute) . '.' . Str::random();
                $this->attributes["{$attribute}_translation"] = "$group.$item";
            }
        }
        return parent::setAttribute($attribute, $value);
    }

    /**
     *  Extend parent's attributesToArray so that _translation attributes do not appear in array, and translatable attributes are translated.
     *
     *  @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->translatableAttributes as $translatableAttribute) {
            if (isset($attributes[$translatableAttribute])) {
                $attributes[$translatableAttribute] = $this->translate($translatableAttribute);
            }
            unset($attributes["{$translatableAttribute}_translation"]);
        }

        return $attributes;
    }

    /**
     *  Get the set translation code for the give attribute
     *
     *  @param string $attribute
     *  @return string
     */
    public function translationCodeFor($attribute)
    {
        return array_get($this->attributes, "{$attribute}_translation", false);
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
            return $this->isTranslatable($rawAttribute);
        }
        return false;
    }

    /**
     * @param $attribute
     */
    public function getRawAttribute($attribute)
    {
        return array_get($this->attributes, $attribute, '');
    }

    /**
     *  Return the translation related to a translatable attribute.
     *
     *  @param  string $attribute
     *  @return Translation
     */
    public function translate($attribute)
    {
        $translationCode = $this->translationCodeFor($attribute);
        $translation     = $translationCode ? trans($translationCode) : false;
        return $translation ?: parent::getAttribute($attribute);
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
        return $this->isTranslatable($attribute) && isset($this->attributes["{$attribute}_translation"]);
    }

    /**
     *  Return the translatable attributes array
     *
     *  @return  array
     */
    public function translatableAttributes()
    {
        return $this->translatableAttributes;
    }
}
