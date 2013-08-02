<?php namespace Waavi\Translation\Models;

use LaravelBook\Ardent\Ardent;

class Language extends Ardent {

  /**
   *  Table name in the database.
   *  @var string
   */
	protected $table = 'languages';

  /**
   *  Hydrate data on new entries' validation.
   *  @var boolean
   */
  public $autoHydrateEntityFromInput = false;

  /**
   *  Hydrate data whenever validation is called
   *  @var boolean
   */
  public $forceEntityHydrationFromInput = false;

	/**
   *  Validation rules
   *  @var array
   */
  public static $rules = array(
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