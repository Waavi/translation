<?php namespace Waavi\Translation\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model {

	protected $table = 'languages';

	/**
   * Validation rules
   */
  protected $rules = array(
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

}