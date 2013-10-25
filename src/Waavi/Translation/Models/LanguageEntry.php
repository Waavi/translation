<?php namespace Waavi\Translation\Models;

use Waavi\Model\WaaviModel;

class LanguageEntry extends WaaviModel {

  /**
   *  Table name in the database.
   *  @var string
   */
	protected $table = 'language_entries';

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
    'language_id' => 'required',  // Language FK
    'namespace'   => '',          // Language Entry namespace. Default is *
    'group'       => 'required',  // Entry group, references the name of the file the translation was originally stored in.
    'item'        => 'required',  // Entry code.
    'text'        => 'required',  // Translation text.
    'unstable'    => '',          // If this flag is set to true, the text in the default language has changed since this entry was last updated.
    'locked'      => '',          // If this flag is set to true, then this entry's text may not be edited.
  );

  /**
   *	Each language entry belongs to a language.
   */
  public function language()
  {
  	return $this->belongsTo('Waavi\Translation\Models\Language');
  }

  /**
   *  Return the language entry in the default language that corresponds to this entry.
   *  @param Waavi\Translation\Models\Language  $defaultLanguage
   *  @return Waavi\Translation\Models\LanguageEntry
   */
  public function original($defaultLanguage)
  {
    if ($this->exists && $defaultLanguage && $defaultLanguage->exists) {
      return $defaultLanguage->entries()->where('namespace', '=', $this->namespace)->where('group', '=', $this->group)->where('item', '=', $this->item)->first();
    } else {
      return NULL;
    }
  }

  /**
   *  Update the text. In case the second argument is true, then all translations for this entry will be flagged as unstable.
   *  @param  string   $text
   *  @param  boolean  $isDefault
   *  @return boolean
   */
  public function updateText($text, $isDefault = false, $lock = false, $force = false)
  {
    $saved            = false;

    // If the text is locked, do not allow editing:
    if (!$this->locked || $force) {
      $this->text   = $text;
      $this->locked = $lock;
      $saved        = $this->save();
      if ($saved && $isDefault) {
        $this->flagSiblingsUnstable();
      }
    }
    return $saved;
  }

  /**
   *  Flag all siblings as unstable.
   *
   */
  public function flagSiblingsUnstable()
  {
    if ($this->id) {
      LanguageEntry::where('namespace', '=', $this->namespace)
        ->where('group', '=', $this->group)
        ->where('item', '=', $this->item)
        ->where('language_id', '!=', $this->language_id)
        ->update(array('unstable' => '1'));
    }
  }

  /**
   *  Returns a list of entries that contain a translation for this item in the given language.
   *
   *  @param Waavi\Translation\Models\Language
   *  @return Waavi\Translation\Models\LanguageEntry
   */
  public function getSuggestedTranslations($language)
  {
    $self = $this;
    return $language->entries()
        ->select("{$this->table}.*")
        ->join("{$this->table} as e", function($join) use ($self) {
          $join
            ->on('e.group', '=', "{$self->table}.group")
            ->on('e.item', '=', "{$self->table}.item");
        })
        ->where('e.language_id', '=', $this->language_id)
        ->where('e.text', '=', "{$this->text}")
        ->groupBy("{$this->table}.text")
        ->get();
  }
}