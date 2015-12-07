# Better localization management for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/waavi/translation.svg?style=flat-square)](https://packagist.org/packages/waavi/translation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/waavi/translation/master.svg?style=flat-square)](https://travis-ci.org/waavi/translation)
[![Total Downloads](https://img.shields.io/packagist/dt/waavi/translation.svg?style=flat-square)](https://packagist.org/packages/waavi/translation)

## Upgrading Laravel's localization module

Keeping a project's translations properly updated is cumbersome. Usually translators do not have access to the codebase, and even when they do it's hard to keep track of which translations are missing for each language or when updates to the original text require that translations be revised.

This package allows developers to leverage their database and cache to manage multilanguage sites, while still working on language files during development and benefiting from all the features Laravel's Translation bundle has, like pluralization or replacement.

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

		Lang::get('validations.missing_name') 		-> 		'Falta el nombre'
		Lang::get('validations.missing_surname') 	-> 		'Surname is missing'
		Lang::get('validations.missing_email') 		-> 		'missing_email'

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

		Lang::get('validations.missing.name')   ->    'No se ha indicado nombre'
		Lang::get('validations.min_number')     ->    'Number is too small'
		Lang::get('validations.missing.email')  ->    'missing_email'

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

## Caching results

Since querying the database everytime a language group must be loaded is grossly inefficient, you may choose to leverage Laravel's cache system. This module will use the same cache configuration as defined by you in app/config/cache.php.

Entries in the cache will be prefixed by default with 'translation-'. You may change this through your environment variables or the config/translator.php config file.

## Managing languages and translations in the Database


