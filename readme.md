# Better localization management for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/waavi/translation.svg?style=flat-square)](https://packagist.org/packages/waavi/translation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/Waavi/translation/master.svg?style=flat-square)](https://travis-ci.org/Waavi/translation)
[![Total Downloads](https://img.shields.io/packagist/dt/waavi/translation.svg?style=flat-square)](https://packagist.org/packages/waavi/translation)

## Upgrading Laravel's localization module

Keeping a project's translations properly updated is cumbersome. Usually translators do not have access to the codebase, and even when they do it's hard to keep track of which translations are missing for each language or when updates to the original text require that translations be revised.

This package allows developers to leverage their database and cache to manage multilanguage sites, while still working on language files during development and benefiting from all the features Laravel's Translation bundle has, like pluralization or replacement.

## Laravel compatibility

 Laravel  | translation
:---------|:----------
 4.x  	  | 1.0.x
 5.0.x    | 2.0.x
 5.1.x    | 2.1.x

## Features

 - Allow dynamic changes to the site's text and translations.
 - Cache your localization entries.
 - Load your translation files into the database.
 - Force your urls to be localized (ex: /home -> /es/home) and set the locale automatically through the browser's config.
 - Localize your model attributes.

## Installation

Require through composer

	composer require waavi/translation 2.0.x

Or manually edit your composer.json file:

	"require": {
		"waavi/translation": "2.0.x"
	}

Publish both the configuration file and the migrations:

	php artisan vendor:publish

Once installed, in your project's config/app.php file replace the following entry from the providers array:

	Illuminate\Translation\TranslationServiceProvider::class

with:

	Waavi\Translation\TranslationServiceProvider::class

Execute the database migrations:

	php artisan migrate

You may check the package's configuration file at:

	config/translator.php

## Use

### Modes of operation

You may configure the mode of operation through the config.php file. You may choose the one most appropiate for your workflow and environment.

#### File based

If you do not wish to leverage your database for translations, you may choose to load language lines exclusively through language files. This mode differs from Laravel in that, in case a line is not found in the specified locale, instead of returning the key right away, we first check the default language for an entry.

Example:

	The content in en/validations.php is:
		array(
			'missing_name'			=>	'Name is missing',
			'missing_surname'		=>	'Surname is missing',
		);

	The content in es/validations.php is:
		array(
			'missing_name'			=>	'Falta el nombre',
		);

	Output for different keys with 'es' locale:

		trans('validations.missing_name') 		-> 		'Falta el nombre'
		trans('validations.missing_surname') 	-> 		'Surname is missing'
		trans('validations.missing_email') 		-> 		'missing_email'

#### Database

You may choose to load translations exclusively from the database. If you leverage your cache, this might be the best option when the site is live and you allow translators to add and update language entries through the database. In order to use the database mode of operation you must:

* Run the migrations detailed in the installation instructions.
* Add your languages of choice to the database (see Managing Database Languages)
* Load your language files into the database using ` php artisan translator:load `

Example:

	The content in the languages table is:
		| id | locale | name    |
		-------------------------
		| 1  | en     | english |
		| 2  | es     | spanish |

	The relevant content in the language_entries table is:
		| id | language_id | namespace | group       | item	           | text                       |
		---------------------------------------------------------------------------------------------
		| 1  | 1           | NULL      | validations | missing.name    | Name is missing            |
		| 2  | 2           | NULL      | validations | missing.surname | Surname is missing         |
		| 3  | 1           | NULL      | validations | min_number      | Number is too small        |
		| 4  | 2           | NULL      | validations | missing.name    | No se ha indicado nombre   |
		| 5  | 2           | NULL      | validations | missing.surname | No se ha indicado apellido |

	Output for different keys with es locale:

		trans('validations.missing.name')   ->    'No se ha indicado nombre'
		trans('validations.min_number')     ->    'Number is too small'
		trans('validations.missing.email')  ->    'missing_email'

#### Mixed mode

In mixed mode, both the language files and the database are queried when looking for a group of language lines. Entries found in the filesystem take precedence over the database.

Example:

	When the content of the language files and the database is the same as in the previous two examples, this is the output for Lang::get:

		trans('validations.missing_name')     ->    'Falta el nombre'
		trans('validations.missing_surname')  ->    'No se ha indicado apellido'
		trans('validations.min_number')       ->    'Number is too small'
		trans('validations.missing_email')    ->    'missing_email'

### Loading your files into the database

When uploading your code to the live site, you must load your file contents into the database if you're using either the auto, database or mixed modes of operations. To refresh your database you may use the following artisan command:

	php artisan translator:load

When loading the contents of the language files, non-existing entries will be added and existing ones will be updated (nothing is erased by default, you'll see why). In case the text on a default locale entry is updated, its current translations are flagged as 'unstable' so as to signal that a translator should check them to see if they're still valid. This is useful when small changes in the text shouldn't be reflected in the translations, or these are still somewhat valid despite the fact of not having been updated.

Both vendor and subdirectories are supported. Please keep in mind that when loading an entry inside a subdirectory, Laravel 5 has changed the syntax to:

	trans('subdir/file.entry')
	trans('package::subdir/file.entry')

## Caching results

Since querying the database everytime a language group must be loaded is grossly inefficient, you may choose to leverage Laravel's cache system. This module will use the same cache configuration as defined by you in app/config/cache.php.

Entries in the cache will be prefixed by default with 'translation' suffix. You may change this through your environment variables or the config/translator.php config file.

## Managing languages and translations in the Database

The recommended way of managing both languages and translations is through the provided repositories. You may circumvent this by saving changes directly through the Language and Translation models, however validation is no longer executed automatically on model save and could lead to instability and errors.

Both the Language and the Translation repository provide the following methods:

	all($related = [], $perPage = 0);		// Retrieve all records from the DB. Pagination is optional
	trashed($related = [], $perPage = 0);	// Retrieve all trashed records from the DB. Pagination is optional
	find($id, $related = []);				// Find a record by id
	findTrashed($id, $related = []);		// Find a trashed record by id
	delete($id);							// Delete a record by id
	restore($id);							// Restore a record by id
	count();								// Return the total number of entries

### Managing Languages

Language management should be done through the Waavi\Translation\Repositories\LanguageRepository to ensure proper data validation before inserts and updates. It is recommended that you instantiate this class through Dependency Injection. The provided methods are:

	create($attributes);					// Creates a Language entry. Returns false if errors.
	update(array $attributes);				// Updates a Language entry (id, name, locale)
	findByLocale($locale);					// Find a record by locale
	findTrashedByLocale($locale);			// Finds a trashed record by locale
	allExcept($locale);						// Returns a list of all languages excluding the given locale
	availableLocales();						// Returns a list of all available locales
	isValidLocale($locale);					// Checks if a language exists with the given locale
	percentTranslated($locale);				// Returns the percent translated for the given locale
	validate(array $attributes);			// Both locale and name must be unique
	validationErrors();						// Get validation errors for create and update

### Managing Translations

Translation management should be done through the Waavi\Translation\Repositories\TranslationRepository to ensure proper data validation before inserts and updates. It is recommended that you instantiate this class through Dependency Injection. The provided methods are:

	create(array $attributes);							// Create a Translation
	update($id, $text);									// Update an unlocked entry
	updateAndLock($id, $text);							// Update and lock an entry
	allByLocale($locale, $perPage = 0);					// Get all by locale
	untranslated($locale, $perPage = 0, $text = null);	// Get all untranslated entries
	pendingReview($locale, $perPage = 0);				// List all entries pending review
	search($locale, $partialCode, $perPage = 0);		// Search by partial code.
	randomUntranslated($locale);						// Get a random untranslated entry
	translateText($text, $textLocale, $targetLocale);	// Translate text to another locale
	flagAsReviewed($id);								// Flag entry as reviewed
	validate(array $attributes);						// No conflicting entries
	validationErrors();									// Get validation errors for create and update

Several things to consider are:

 - You may lock translations so that they can only be updated through updateAndLock. The language file loader uses the update method and will not be able to override locked translations.
 - When a text entry belonging to the default locale is updated, all of its siblings are marked as pending review.
 - When deleting an entry, if it belongs to the default locale its translations will also be deleted.

## Model attributes translation

You can also use the translation management system to manage your model attributes translations. To do this, you only need to:

 - Make sure your models use the Waavi\Translation\Translatable\Trait
 - In your model, add a translatableAttributes array with the names of the attributes you wish to be available for translation.
 - For every field you wish to translate, make sure there is a corresponding attributeName_translation field in your database.

Example:

	\Schema::create('examples', function ($table) {
        $table->increments('id');
        $table->string('slug')->nullable();
        $table->string('title')->nullable();
        $table->string('title_translation')->nullable();
        $table->string('text')->nullable();
        $table->string('text_translation')->nullable();
        $table->timestamps();
    });

    class Example extends Model
	{
	    use Translatable;
	    protected $translatableAttributes = ['title', 'text'];
	}


## Uri localization

You may use Waavi\Translation\Middleware\TranslationMiddleware to make sure all of your urls are properly localized. The TranslationMiddleware will only redirect GET requests that do not have a locale in them.

For example, if a user visits the url /home, the following would happen:

 - The middleware will check if a locale is present.
 - If a valid locale is present:
 	- it will globally set the language for that locale
 	- the following data will be available in your views:
 		- currentLanguage: current selected Language instance.
 		- selectableLanguages: list of all languages the visitor can switch to (except the current one)
 		- altLocalizedUrls: a list of all localized urls for the current resource except this one, formatted as ['locale' => 'en', 'url' => '/en/home']
 - If no locale is present:
 	- Check the first two letters of the browers locale (for example 'en-us' => 'en')
 	- If this is a valid locale, redirect the visitor to that locale => /en/home
 	- If not, redirect to default locale => /es/home
 	- Redirects will keep input data in the url if any

You may choose to activate this Middleware globally by adding the middleware to your App\Http\Kernel file:

	protected $middleware = [
		/* ... */
        \Waavi\Translation\Middleware\TranslationMiddleware::class,
    ]

Or to apply it selectively through the 'localize' route middleware, which is already registered when installing the package.

You will also need to add the following to your config/app.php aliases:

	'aliases'         => [
		'UriLocalizer'	=> Waavi\Translation\Facades\UriLocalizer::class,
    ];

For every route where you apply the localization middleware, you must prepend the current locale, for example:

	Route::group(['prefix' => \UriLocalizer::localeFromRequest(), 'middleware' => 'localize')], function () {
	    /* ... */
	});