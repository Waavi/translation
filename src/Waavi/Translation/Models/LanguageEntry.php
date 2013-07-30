<?php namespace Waavi\Translation\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageEntry extends Model {

	protected $table = 'language_entries';

	/**
   * Validation rules
   */
  protected $rules = array(
    'language_id' => 'required',
    'namespace'   => '',
    'group'       => 'required',
    'item'        => 'required',
    'text'        => 'required',
    'unstable'    => '',
  );

  /**
   *	Each language entry belongs to a language.
   */
  public function language()
  {
  	return $this->belongsTo('Waavi\Translation\Models\Language');
  }
}