## Upgrading Laravel's localization module

Keeping a project's translations properly updated is cumbersome. Usually translators do not have access to the codebase, and even when they do it's hard to keep track of which translations are missing for each language or when updates to the original text require that translations be revised.

This package allows developers to leverage their database and cache to manage multilanguage sites, while still working on language files during development and benefiting from all the features Laravel's Translation bundle has, like pluralization or replacement.

## Installation

In app/config/app.php, replace the following entry from the providers array:

	'Illuminate\Translation\TranslationServiceProvider'

with:

	'Waavi\Translation\TranslationServiceProvider'

Execute the database migrations:

	php artisan migrate --package=waavi/translation

You may publish the package's configuration if you so choose:

	php artisan config:publish vendor/package

## Usage

This translations bundle is designed to adapt to your workflow. Translations are accessed just like in Laravel, with replacements and pluralization working as expected. The only difference is how your translations are managed.

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

	Output for different keys with es locale:

		Lang::get('validations.missing_name') 		-> 		'Falta el nombre'
		Lang::get('validations.missing_surname') 	-> 		'Surname is missing'
		Lang::get('validations.missing_email') 		-> 		'missing_email'

#### Database

You may choose to load translations exclusively from the database. If you leverage your cache, this might be the best option when the site is live and you allow translators to add and update language entries through the database. In order to use the database mode of operation you must:

* Run the migrations detailed in the installation instructions.
* Add your languages of choice to the database.
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
		| 1  | 1           | NULL      | validations | missing_name    | Name is missing            |
		| 2  | 2           | NULL      | validations | missing_surname | Surname is missing         |
		| 3  | 1           | NULL      | validations | min_number      | Number is too small        |
		| 4  | 2           | NULL      | validations | missing_name    | No se ha indicado nombre   |
		| 5  | 2           | NULL      | validations | missing_surname | No se ha indicado apellido |

	Output for different keys with es locale:

		Lang::get('validations.missing_name')   ->    'No se ha indicado nombre'
		Lang::get('validations.min_number')     ->    'Number is too small'
		Lang::get('validations.missing_email')  ->    'missing_email'

#### Mixed mode

In mixed mode, both the language files and the database are queried when looking for a group of language lines. Entries found in the filesystem take precedence over the database.

Example:

	When the content of the language files and the database is the same as in the previous two examples, this is the output for Lang::get:

		Lang::get('validations.missing_name')     ->    'Falta el nombre'
		Lang::get('validations.missing_surname')  ->    'No se ha indicado apellido'
		Lang::get('validations.min_number')       ->    'Number is too small'
		Lang::get('validations.missing_email')    ->    'missing_email'

#### Auto mode (default)

When in auto mode, the mode of operation is set by the value of 'debug' in app/config/app.php. When true, mixed mode is selected. When false, database mode is selected.

### Loading your files into the database

When uploading your code to the live site, you must load your file contents into the database if you're using either the auto, database or mixed modes of operations. To refresh your database you may use the following artisan command:

	php artisan translator:load

When loading the contents of the language files, non-existing entries will be added and existing ones will be updated (nothing is erased by default, you'll see why). In case the text on a default locale entry is updated, its current translations are flagged as 'unstable' so as to signal that a translator should check them to see if they're still valid. This is useful when small changes in the text shouldn't be reflected in the translations, or these are still somewhat valid despite the fact of not having been updated.

## The models

### The Language model

### The LanguageEntry model

### The 