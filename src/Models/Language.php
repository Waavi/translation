<?php namespace Waavi\Translation\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     *  Table name in the database.
     *  @var string
     */
    protected $table = 'translator_languages';

    /**
     *  List of variables that cannot be mass assigned
     *  @var array
     */
    protected $fillable = ['locale', 'name'];

    /**
     *  Each language may have several translations.
     */
    public function translations()
    {
        return $this->hasMany(Translation::class, 'locale', 'locale');
    }

    /**
     *  Returns the name of this language in the current selected language.
     *
     *  @return string
     */
    public function getLanguageCodeAttribute()
    {
        return "languages.{$this->locale}";
    }

}
