# Better localization management for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/waavi/translation.svg?style=flat-square)](https://packagist.org/packages/waavi/translation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/Waavi/translation/master.svg?style=flat-square)](https://travis-ci.org/Waavi/translation)
[![Total Downloads](https://img.shields.io/packagist/dt/waavi/translation.svg?style=flat-square)](https://packagist.org/packages/waavi/translation)

## Introduction

Keeping a project's translations properly updated is cumbersome. Usually translators do not have access to the codebase, and even when they do it's hard to keep track of which translations are missing for each language or when updates to the original text require that translations be revised.

This package allows developers to leverage their database and cache to manage multilanguage sites, while still working on language files during development and benefiting from all the features Laravel's Translation bundle has, like pluralization or replacement.

WAAVI is a web development studio based in Madrid, Spain. You can learn more about us at [waavi.com](http://waavi.com)

## Table of contents

- [Laravel compatibility](#laravel-compatibility)
- [Features overview](#features-overview)
- [Installation](#installation)
- [Set source for translations](#translations-source)
  - [Load translations from files](#load-translations-from-files)
  - [Load translations from the database](#load-translations-from-the-database)
  - [Mixed mode](#mixed-mode)
  - [Loading your files into the database](#loading-your-files-into-the-database)
- [Cache translations](#cache-translations)
- [Managing languages and translations in the Database](#managing-languages-and-translations-in-the-database)
  - [Managing Languages](#managing-languages)
  - [Managing Translations](#managing-translations)
- [Model attributes translation](#model-attributes-translation)
- [Uri localization](#uri-localization)

## Laravel compatibility

 Laravel  | translation
:---------|:----------
 4.x  	  | 1.0.x
 5.0.x    | 2.0.x
 5.1.x|5.3.x    | 2.1.x
 5.4.x    | 2.2.x
 5.5.x    | 2.3.x and higher

## Features overview

 - Allow dynamic changes to the site's text and translations.
 - Cache your localization entries.
 - Load your translation files into the database.
 - Force your urls to be localized (ex: /home -> /es/home) and set the locale automatically through the browser's config.
 - Localize your model attributes.

## Installation

Require through composer


	composer require waavi/translation 2.3.x

Or manually edit your composer.json file:

	"require": {
		"waavi/translation": "2.3.x"
	}

Once installed, in your project's config/app.php file replace the following entry from the providers array:

	Illuminate\Translation\TranslationServiceProvider::class

with:

	Waavi\Translation\TranslationServiceProvider::class

Remove your config cache:

	php artisan config:cache

Publish both the configuration file and the migrations:

	php artisan vendor:publish --provider="Waavi\Translation\TranslationServiceProvider"

Execute the database migrations:

	php artisan migrate

You may check the package's configuration file at:

	config/translator.php

## Translations source

This package allows you to load translation from the regular Laravel localization files (in /resources/lang), from the database, from cache or in a mix of the previous for development. You may configure the desired mode of operation through the translator.php config file and/or the TRANSLATION_SOURCE environment variable. Accepted values are:

 - 'files'		To load translations from Laravel's language files (default)
 - 'database'	To load translations from the database
 - 'mixed'		To load translations both from the filesystem and the database, with the filesystem having priority.
 - 'mixed_db'   To load translations both from the filesystem and the database, with the database having priority. [v2.1.5.3]

For cache configuration, please go to [cache configuration](#cache-translations)

### Load translations from files

If you do not wish to leverage your database for translations, you may choose to load language lines exclusively through language files. This mode differs from Laravel in that, in case a line is not found in the specified locale, instead of returning the key right away, we first check the default language for an entry. In case you wish to use this mode exclusively, you will need to set the 'available_locales' config file:

	config/translator.php
		'available_locales' => ['en', 'es', 'fr'],

Example:

The content in en/validations.php, where 'en' is the default locale, is:
```php
		[
			'missing_name'			=>	'Name is missing',
			'missing_surname'		=>	'Surname is missing',
		];
```
The content in es/validations.php is:
```php
		[
			'missing_name'			=>	'Falta el nombre',
		];
```
Output for different keys with 'es' locale:
```php
		trans('validations.missing_name'); 		// 		'Falta el nombre'
		trans('validations.missing_surname'); 	// 		'Surname is missing'
		trans('validations.missing_email'); 	// 		'validations.missing_email'
```

### Load translations from the database

You may choose to load translations exclusively from the database. This is very useful if you intend to allow users or administrators to live edit the site's text and translations. In a live production environment, you will usually want this source mode to be activated with the translation's cache. Please see [Loading your files into the database](#loading-your-files-into-the-database) for details on the steps required to use this source mode.

Example:

The content in the languages table is:

		| id | locale | name    |
		-------------------------
		| 1  | en     | english |
		| 2  | es     | spanish |

The relevant content in the language_entries table is:

		| id | locale | namespace | group       | item	          | text                    |
		-------------------------------------------------------------------------------------
		| 1  | en     | *         | validations | missing.name    | Name is missing         |
		| 2  | en     | *         | validations | missing.surname | Surname is missing      |
		| 3  | en     | *         | validations | min_number      | Number is too small     |
		| 4  | es     | *         | validations | missing.name    | Falta nombre   			|
		| 5  | es     | *         | validations | missing.surname | Falta apellido 			|

Output for different keys with es locale:

```php
		trans('validations.missing.name');   //    'Falta nombre'
		trans('validations.min_number');     //    'Number is too small'
		trans('validations.missing.email');  //    'missing_email'
```

### Mixed mode

In mixed mode, both the language files and the database are queried when looking for a group of language lines. Entries found in the filesystem take precedence over the database. This source mode is useful when in development, so that both the filesystem and the user entries are taken into consideration.

Example:

	When files and database are set like in the previous examples:
```php
		trans('validations.missing_name');     //    'Falta el nombre'
		trans('validations.missing_surname');  //    'Falta apellido'
		trans('validations.min_number');       //    'Number is too small'
		trans('validations.missing_email');    //    'missing_email'
```

### Loading your files into the database

When using either the database or mixed translation sources, you will need to first load your translations into the database. To do so, follow these steps:

* Run the migrations detailed in the installation instructions.
* Add your languages of choice to the database (see [Managing Database Languages](#managing-database-languages))
* Load your language files into the database using the provided Artisan command:

	` php artisan translator:load `

When executing the artisan command, the following will happen:

- Non existing entries will be created.
- Existing entries will be updated **except if they're locked**. When allowing users to live edit the translations, it is recommended you do it throught the updateAndLock method provided in the [Translations repository](#managing-translations). This prevents entries being overwritten when reloading translations from files.
- When an entry in the default locale is edited, all of its translations will be flagged as **pending review**. This gives translators the oportunity to review translations that might not be correct, but doesn't delete them so as to avoid minor errata changes in the source text from erasing all translations. See [Managing translations](#managing-translations) for details on how to work with unstable translations.

Both vendor files and subdirectories are supported. Please keep in mind that when loading an entry inside a subdirectory, Laravel 5 has changed the syntax to:
```php
	trans('subdir/file.entry')
	trans('package::subdir/file.entry')
```

## Cache translations

Since querying the database everytime a language group must be loaded is grossly inefficient, you may choose to leverage Laravel's cache system. This module will use the same cache configuration as defined by you in app/config/cache.php.

You may enable or disable the cache through the translator.php config file or the 'TRANSLATION_CACHE_ENABLED' environment variable. Config options are:

 Env key  | type	|description
:---------|:--------|:-----------
 TRANSLATION_CACHE_ENABLED 	| boolean| Enable / disable the translations cache
 TRANSLATION_CACHE_TIMEOUT  | integer| Minutes translation items should be kept in the cache.
 TRANSLATION_CACHE_SUFFIX   | string | Default is 'translation'. This will be the cache suffix applied to all translation cache entries.

### Cache tags

Available since version 2.1.3.8, if the cache store in use allows for tags, the TRANSLATION_CACHE_SUFFIX will be used as the common tag to all cache entries. This is recommended to be able to invalidate only the translation cache, or even just a given locale, namespace and group configuration.

### Clearing the cache

Available since version 2.1.3.8, you may clear the translation cache through both an Artisan Command and a Facade. If cache tags are in use, only the translation cache will be cleared. All of your application cache will however be cleared if you cache tags are not available.

Cache flush command:

    php artisan translator:flush

In order to access the translation cache, add to your config/app.php files, the following alias:
```php
    'aliases'         => [
        /* ... */
        'TranslationCache' => \Waavi\Translation\Facades\TranslationCache::class,
    ]
```
Once done, you may clear the whole translation cache by calling:
```php
    \TranslationCache::flushAll();
```

You may also choose to invalidate only a given locale, namespace and group combination.
```php
    \TranslationCache::flush($locale, $group, $namespace);
```

- The locale is the language locale you wish to clear.
- The namespace is either '*' for your application translation files, or 'package' for vendor translation files.
- The group variable is the path to the translation file you wish to clear.

For example, say we have the following file in our resources/lang directory: en/auth.php, en/auth/login.php and en/vendor/waavi/login.php. To clear the cache entries for each of them you would call:
```php
    \TranslationCache::flush('en', 'auth', '*');
    \TranslationCache::flush('en', 'auth/login', '*');
    \TranslationCache::flush('en', 'login', 'waavi');
```

## Managing languages and translations in the Database

The recommended way of managing both languages and translations is through the provided repositories. You may circumvent this by saving changes directly through the Language and Translation models, however validation is no longer executed automatically on model save and could lead to instability and errors.

Both the Language and the Translation repositories provide the following methods:

 Method   | Description
:---------|:--------
hasTable();									| Returns true if the corresponding table exists in the database, false otherwise
all($related = [], $perPage = 0); 			| Retrieve all records from the DB. A paginated record will be return if the second argument is > 0, with $perPage items returned per page
find($id);									| Find a record by id
create($attributes);						| Validates the given attributes and inserts a new record. Returns false if validation errors occured
delete($id);								| Delete a record by id
restore($id);								| Restore a record by id
count();									| Return the total number of entries
validate(array $attributes);				| Checks if the given attributes are valid
validationErrors();							| Get validation errors for create and update methods

### Managing Languages

Language management should be done through the **\Waavi\Translation\Repositories\LanguageRepository** to ensure proper data validation before inserts and updates. It is recommended that you instantiate this class through Dependency Injection.

A valid Language record requires both its name and locale to be unique. It is recommended you use the native name for each language (Ex: English, Español, Français)

The provided methods are:

 Method   | Description
:---------|:--------
update(array $attributes);				| Updates a Language entry [id, name, locale]
trashed($related = [], $perPage = 0);	| Retrieve all trashed records from the DB.
findTrashed($id, $related = []);		| Find a trashed record by id
findByLocale($locale);					| Find a record by locale
findTrashedByLocale($locale);			| Finds a trashed record by locale
allExcept($locale);						| Returns a list of all languages excluding the given locale
availableLocales();						| Returns a list of all available locales
isValidLocale($locale);					| Checks if a language exists with the given locale
percentTranslated($locale);				| Returns the percent translated for the given locale


### Managing Translations

Translation management should be done through the **\Waavi\Translation\Repositories\TranslationRepository** to ensure proper data validation before inserts and updates. It is recommended that you instantiate this class through Dependency Injection.

A valid translation entry cannot have the same locale and language code than another.

The provided methods are:

 Method   | Description
:---------|:--------
update($id, $text);									| Update an unlocked entry
updateAndLock($id, $text);							| Update and lock an entry (locked or not)
allByLocale($locale, $perPage = 0);					| Get all by locale
untranslated($locale, $perPage = 0, $text = null);	| Get all untranslated entries. If $text is set, entries will be filtered by partial matches to translation value.
pendingReview($locale, $perPage = 0);				| List all entries pending review
search($locale, $term, $perPage = 0);				| Search by all entries by locale and a partial match to both the text value and the translation code.
randomUntranslated($locale);						| Get a random untranslated entry
translateText($text, $textLocale, $targetLocale);	| Translate text to another locale
flagAsReviewed($id);								| Flag entry as reviewed

Things to consider:

 - You may lock translations so that they can only be updated through updateAndLock. The language file loader uses the update method and will not be able to override locked translations.
 - When a text entry belonging to the default locale is updated, all of its siblings are marked as pending review.
 - When deleting an entry, if it belongs to the default locale its translations will also be deleted.

## Model attributes translation

You can also use the translation management system to manage your model attributes translations. To do this, you only need to:

 - Make sure either the database or mixed source are set.
 - Make sure your models use the Waavi\Translation\Translatable\Trait
 - In your model, add a translatableAttributes array with the names of the attributes you wish to be available for translation.
 - For every field you wish to translate, make sure there is a corresponding attributeName_translation field in your database.

Example:
```php
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
	    use \Waavi\Translation\Translatable\Trait;
	    protected $translatableAttributes = ['title', 'text'];
	}
```

## Uri localization

You may use Waavi\Translation\Middleware\TranslationMiddleware to make sure all of your urls are properly localized. The TranslationMiddleware will only redirect GET requests that do not have a locale in them.

For example, if a user visits the url /home, the following would happen:

 - The middleware will check if a locale is present.
 - If a valid locale is present:
 	- it will globally set the language for that locale
 	- the following data will be available in your views:
 		- currentLanguage: current selected Language instance.
 		- selectableLanguages: list of all languages the visitor can switch to (except the current one)
 		- altLocalizedUrls: a list of all localized urls for the current resource except this one, formatted as ['locale' => 'en', 'name' => 'English', 'url' => '/en/home']
 - If no locale is present:
 	- Check the first two letters of the brower's accepted locale HTTP_ACCEPT_LANGUAGE (for example 'en-us' => 'en')
 	- If this is a valid locale, redirect the visitor to that locale => /es/home
 	- If not, redirect to default locale => /en/home
 	- Redirects will keep input data in the url, if any

You may choose to activate this Middleware globally by adding the middleware to your App\Http\Kernel file:
```php
	protected $middleware = [
		/* ... */
        \Waavi\Translation\Middleware\TranslationMiddleware::class,
    ]
```
Or to apply it selectively through the **'localize'** route middleware, which is already registered when installing the package through the ServiceProvider.

It is recommended you add the following alias to your config/app.php aliases:

```php
	'aliases'         => [
		/* ... */
		'UriLocalizer'	=> Waavi\Translation\Facades\UriLocalizer::class,
    ];
```

Every localized route must be prefixed with the current locale:

```php
	// If the middleware is globally applied:
	Route::group(['prefix' => \UriLocalizer::localeFromRequest()], function(){
		/* Your routes here */
	});

	// For selectively chosen routes:
	Route::group(['prefix' => \UriLocalizer::localeFromRequest(), 'middleware' => 'localize')], function () {
	    /* Your routes here */
	});
```

Starting on v2.1.6, you may also specify a custom position for the locale segment in your url. For example, if the locale info is the third segment in a URL (/api/v1/es/my_resource), you may use:

```php
    // For selectively chosen routes:
    Route::group(['prefix' => 'api/v1'], function() {
        /** ... Non localized urls here **/

        Route::group(['prefix' => \UriLocalizer::localeFromRequest(2), 'middleware' => 'localize:2')], function () {
            /* Your localized routes here */
        });
    });
```

In your views, for routes where the Middleware is active, you may present the user with a menu to switch from the current language to another by using the shared variables. For example:

```php
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $currentLanguage->name }} <b class="caret"></b></a>
    <ul class="dropdown-menu">
        @foreach ($altLocalizedUrls as $alt)
            <li><a href="{{ $alt['url'] }}" hreflang="{{ $alt['locale'] }}">{{ $alt['name'] }}</a></li>
        @endforeach
    </ul>
</li>
```
