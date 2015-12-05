<?php namespace Waavi\Translation\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageEntry extends Model
{

    /**
     *  Table name in the database.
     *  @var string
     */
    protected $table = 'translator_translations';

    /**
     *  List of variables that can be mass assigned
     *  @var array
     */
    protected $fillable = ['language_id', 'namespace', 'group', 'item', 'text', 'unstable'];

    /**
     *  Validation rules
     *  @var array
     */
    public $rules = [
        'language_id' => 'required', // Language FK
        'namespace'   => '',         // Language Entry namespace. Default is *
        'group'       => 'required', // Entry group, references the name of the file the translation was originally stored in.
        'item'        => 'required', // Entry code.
        'text'        => 'required', // Translation text.
        'unstable'    => '',         // If this flag is set to true, the text in the default language has changed since this entry was last updated.
        'locked'      => '',         // If this flag is set to true, then this entry's text may not be edited.
    ];

    /**
     *    Each language entry belongs to a language.
     */
    public function language()
    {
        return $this->belongsTo(Waavi\Translation\Models\Language::class);
    }
    /**
     *  Returns the full translation code for an entry: namespace.group.item
     *  @return string
     */
    public function getCodeAttribute()
    {
        return "{$this->namespace}.{$this->group}.{$this->item}";
    }

    /**
     * Validate the model instance
     *
     * @return bool
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            throw new ValidatorException($this->errors());
        }

        // Check if an entry with the same language id and code, and different id, exits:
        $clone          = new LanguageEntry;
        $duplicatedCode = $clone
            ->where('language_id', $this->language_id)
            ->where('namespace', $this->namespace)
            ->where('group', $this->group)
            ->where('item', $this->item)
            ->count() > 0;
        if (!$this->exists && $duplicatedCode) {
            $this->errors()->add('code', Lang::get('validation.unique', ['attribute' => 'code']));
            throw new ValidatorException($this->errors());
        }
        return true;
    }

    /**
     *  Flag this entry as Reviewed
     *  @return boolean
     */
    public function flagAsReviewed()
    {
        $this->unstable = 0;
        return $this->save();
    }

    /**
     *  Update the text in this entry
     *
     *  @param  string   $text
     *  @return boolean
     */
    public function setText($text)
    {
        if (!$this->locked) {
            $this->text = $text;
            $this->save();
        } else {
            $this->errors()->add('text', Lang::get('validation.custom.text-locked'));
            throw new ValidatorException($this->errors());
        }
        Event::fire('translation.updated', [$this]);
    }

    /**
     *  Update this entry and lock it, preventing it from being modified through the translator command.
     *
     *  @param  string $text
     *  @return boolean
     */
    public function setTextAndLock($text)
    {
        $this->text   = $text;
        $this->locked = 1;
        $this->save();
        Event::fire('translation.updated', [$this]);
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
            return null;
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
        $saved = false;

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
                ->update(['unstable' => '1']);
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
            ->join("{$this->table} as e", function ($join) use ($self) {
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
