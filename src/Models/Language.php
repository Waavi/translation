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
     *  Validation rules
     *  @var array
     */
    public $rules = [
        'locale' => 'required|unique:languages',
        'name'   => 'required|unique:languages',
    ];

    /**
     *  Each language may have several entries.
     */
    public function entries()
    {
        return $this->hasMany(Waavi\Translation\Models\Translation::class);
    }

    /**
     *  Transforms a uri into one containing the current locale slug.
     *  Examples: login/ => /es/login . / => /es
     *
     *  @param string $uri Current uri.
     *  @return string Target uri.
     */
    public function uri($uri)
    {
        // Delete the forward slash if any at the beginning of the uri:
        $uri      = substr($uri, 0, 1) == '/' ? substr($uri, 1) : $uri;
        $segments = explode('/', $uri);
        $newUri   = "/{$this->locale}/{$uri}";
        if (sizeof($segments) && strlen($segments[0]) == 2) {
            $newUri = "/{$this->locale}";
            for ($i = 1; $i < sizeof($segments); $i++) {
                $newUri .= "/{$segments[$i]}";
            }
        }
        return $newUri;
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
