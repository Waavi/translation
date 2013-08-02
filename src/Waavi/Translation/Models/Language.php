<?php namespace Waavi\Translation\Models;

use LaravelBook\Ardent\Ardent;

class Language extends Ardent {

	protected $table = 'languages';

	/**
   * Validation rules
   */
  public static $rules = array(
    'locale'  => 'required|unique:languages',
    'name'    => 'required|unique:languages',
  );

  public $autoHydrateEntityFromInput = false;    // hydrates on new entries' validation
  public $forceEntityHydrationFromInput = false; // hydrates whenever validation is called

  /**
   *	Each language may have several entries.
   */
  public function entries()
  {
  	return $this->hasMany('Waavi\Translation\Models\LanguageEntry');
  }

}