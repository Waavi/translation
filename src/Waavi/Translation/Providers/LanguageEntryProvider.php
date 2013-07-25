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