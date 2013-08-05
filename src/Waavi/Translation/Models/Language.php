<?php namespace Waavi\Translation\Models;

class Language extends \Waavi\Translation\Models\Model {

  /**
   *  Table name in the database.
   *  @var string
   */
	protected $table = 'languages';

  /**
   *  Allow for languages soft delete.
   *  @var boolean
   */
  protected $softDelete = true;

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
   *  List of variables that cannot be mass assigned
   *  @var array
   */
  protected $guarded = array('id');

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