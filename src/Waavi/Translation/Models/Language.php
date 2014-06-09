<?php namespace Waavi\Translation\Models;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Waavi\Model\WaaviModel;

class Language extends WaaviModel {
  use SoftDeletingTrait; //http://laravel.com/docs/upgrade#upgrade-4.2
  
  protected $dates = ['deleted_at'];
  
  /**
   *  Table name in the database.
   *  @var string
   */
	protected $table = 'languages';

  /**
   *  Allow for languages soft delete.
   *  @var boolean
   */
  //protected $softDelete = true; Commented for Laravel 4.2 Update

  /**
   *  List of variables that cannot be mass assigned
   *  @var array
   */
  protected $guarded = array('id');

	/**
   *  Validation rules
   *  @var array
   */
  public $rules = array(
    'locale'  => 'required|unique:languages',
    'name'    => 'required|unique:languages',
  );

  /**
   *	Each language may have several entries.
   */
  public function entries()
  {
  	return $this->hasMany('Waavi\Translation\Models\LanguageEntry');
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
    $uri = substr($uri, 0, 1) == '/' ? substr($uri, 1) : $uri;
    $segments = explode('/', $uri);
    $newUri = "/{$this->locale}/{$uri}";
    if (sizeof($segments) && strlen($segments[0]) == 2) {
      $newUri = "/{$this->locale}";
      for($i = 1; $i < sizeof($segments); $i++) {
        $newUri .= "/{$segments[$i]}";
      }
    }
    return $newUri;
  }

}
