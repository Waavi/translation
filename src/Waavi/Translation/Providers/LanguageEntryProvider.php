<?php namespace Waavi\Translation\Providers;

class LanguageEntryProvider {

	/**
	 *	The Eloquent language entry model.
	 *	@var string
	 */
	protected $model = 'Waavi\Translation\Models\LanguageEntry';

	/**
	 * Create a new Eloquent LangEntry provider.
	 *
	 * @param  string  $model
	 * @return void
	 */
	public function __construct($model = null)
	{
		$this->setModel($model);
	}

	/**
	 * Find the language entry by ID.
	 *
	 * @param  int  $id
	 * @return Eloquent NULL in case no language entry was found.
	 */
	public function findById($id)
	{
		return $this->createModel()->newQuery()->find($id);
	}

	/**
	 * Find the entries with a key that starts with the provided key.
	 *
	 * @param  string  	$key
	 * @return Eloquent List.
	 */
	public function findByKey($language, $key)
	{
		return $this->createModel()->newQuery()->where('key', 'LIKE', "$key%")->get();
	}

	/**
	 * Find all entries for a given language.
	 *
	 * @param  Eloquent  	$language
	 * @return Eloquent
	 */
	public function findByLanguage($name)
	{
		return $this->createModel()->newQuery()->where('name', '=', $name)->first();
	}

	/**
	 * Returns all languages.
	 *
	 * @return array  $languages
	 */
	public function findAll()
	{
		return $this->createModel()->newQuery()->get()->all();
	}

	/**
	 *	Returns a language entry that is untranslated in the specified language.
	 *	@param Waavi\Translation\Models\Language 				$reference
	 *	@param Waavi\Translation\Models\Language 				$target
	 *	@return Waavi\Translation\Models\LanguageEntry
	 */
	public function findUntranslated($reference, $target)
	{
		$model = $this->createModel();
		return $model
			->newQuery()
			->where('language_id', '=', $reference->id)
			->whereNotExists(function($query) use ($model, $reference, $target){
				$table = $model->getTable();
				$query
					->select(DB::raw(1))
					->from("$table as e")
					->where('language_id', '=', $target->id)
					->whereRaw("e.namespace = $table.namespace")
					->whereRaw("e.group = $table.group")
					->whereRaw("e.item = $table.item")
					;
			})->first();
	}

	/**
	 * Creates a language.
	 *
	 * @param  array  $attributes
	 * @return Cartalyst\Sentry\languages\GroupInterface
	 */
	public function create(array $attributes)
	{
		$language = $this->createModel();
		$language->fill($attributes)->save();
		return $language;
	}

	/**
	 *	Loads messages into the database
	 *	@param array 			$lines
	 *	@param Language 	$language
	 *	@param string 		$group
	 *	@param string 		$namespace
	 *	@param boolean 		$isDefault
	 *	@return void
	 */
	public function loadArray(array $lines, $language, $group, $namespace = null, $isDefault = false)
	{
		// Transform the lines into a flat dot array:
		$lines = array_dot($lines);
		foreach ($lines as $item => $text) {
			// Check if the entry exists in the database:
			$entry = $this
				->createModel()
				->newQuery()
				->where('namespace', '=', $namespace)
	      ->where('group', '=', $group)
	      ->where('item', '=', $item)
	      ->where('language_id', '=', $language->id)
	      ->first();

	    // If the entry already exists and its text is different from the parameters:
	    if ($entry) {
	      if ($entry->text != $text) {
	        $entry->text = $text;
	        if($entry->save() && $isDefault) {
	        	// If we just updated a line from the default language, flag all translations as unstable.
	        	$this
							->createModel()
							->newQuery()
							->where('namespace', '=', $namespace)
				      ->where('group', '=', $group)
				      ->where('item', '=', $item)
				      ->where('language_id', '!=', $language->id)
				      ->update(array('unstable' => '1'));
	        }
	      }
	    }
	    // The entry doesn't exist:
	    else {
	    	$entry = $this->createModel();
	    	$entry->namespace = $namespace;
		    $entry->group = $group;
		    $entry->item = $item;
		    $entry->text = $text;
		    $language->entries()->save($entry);
	    }
		}
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

	/**
	 * Sets a new model class name to be used at
	 * runtime.
	 *
	 * @param  string  $model
	 */
	public function setModel($model = null)
	{
		$this->model = $model ?: $this->model;
	}
}