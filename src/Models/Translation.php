<?php namespace Waavi\Translation\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

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
     *    Each language entry belongs to a language.
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'locale', 'locale');
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
