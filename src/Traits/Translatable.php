<?php namespace Waavi\Translation\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait Translatable
{
    
    private $_translatedAttributes = [];
    
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
        if ($this->isTranslatable($attribute) && $value !== null) {
            // If a translation code has not yet been set, generate one:
            if (!$this->translationCodeFor($attribute)) {
                $reflected                                    = new \ReflectionClass($this);
                $group                                        = $this->getTranslatableAtrributeGroup($attribute);
                $item                                         = strtolower($reflected->getShortName()) . '.' . strtolower($attribute) . '.' . Str::quickRandom();
                $this->attributes["{$attribute}_translation"] = "$group.$item";
            }
            
            $this->_translatedAttributes[$attribute] = $value;
            if (\App::getLocale() === config('app.fallback_locale')) {
                return parent::setAttribute($attribute, $value);
            }
            
        } else {
            return parent::setAttribute($attribute, $value);
        }
    }
    
    public function getTranslatedAttributes() {
        return $this->_translatedAttributes;
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

    public function getRawAttribute($attribute)
    {
        return array_get($this->attributes, $attribute, '');
    }

    /**
     *  Return the translation related to a translatable attribute.
     *  If a translation does not exist yet, one will be created.
     *
     *  @param  string $attribute
     *  @return LanguageEntry
     */
    public function translate($attribute)
    {
        $translationCode = array_get($this->attributes, "{$attribute}_translation", false);
        $translation     = $translationCode ? \App::make('translator')->get($translationCode) : false;
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
     *  Get the group for a translatable attribute or the default if not defined
     *
     *  @return string
     */
    public function getTranslatableAtrributeGroup($attribute)
    {
        if (isset($this->translatableAttributeGroups)) {
            
            //if there is an array of attributes assigned to each translatable attribute
            if (is_array($this->translatableAttributeGroups) && array_key_exists($attribute, $this->translatableAttributeGroups)) {
                return $this->translatableAttributeGroups[$attribute];
            }
            
            //if there is one translatable group for this model
            if (is_string($this->translatableAttributeGroups)) {
                return $this->translatableAttributeGroups;
            }
            
        }
        
        return 'translatable';
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
