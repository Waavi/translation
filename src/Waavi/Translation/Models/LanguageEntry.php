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

  /**
   *  Update the text. In case the second argument is true, then all translations for this entry will be flagged as unstable.
   *  @param  string   $text
   *  @param  boolean  $isDefault
   *  @return boolean
   */
  public function updateText($text, $isDefault = false)
  {
    $this->text = $text;
    if ($this->save()) {
      if ($isDefault) {
        LanguageEntry::where('namespace', '=', $this->namespace)
          ->where('group', '=', $this->group)
          ->where('item', '=', $this->item)
          ->where('language_id', '!=', $this->language_id)
          ->update(array('unstable' => '1'));
      }
      return true;
    } else {
      return false;
    }
  }
}