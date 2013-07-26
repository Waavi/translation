<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Default Translation Driver
	|--------------------------------------------------------------------------
	|
	| This option controls the translation driver that will be utilized.
	|
	| Supported:
	| 	'database' 	Use the database as a source for translations, in addition to files.
	|   'file'			Use files exclusively as a source for translations [Laravel default].
	|
	*/
	'driver'					=>	'database',

	/*
	|--------------------------------------------------------------------------
	| Default Translation Cache
	|--------------------------------------------------------------------------
	|
	| Choose whether to leverage laravel's cache module and how to do so.
	|
	*/
	'cache'					=>	array(
		'enabled' =>	true,
	),

	/*
	|--------------------------------------------------------------------------
	| Language
	|--------------------------------------------------------------------------
	|
	| Configuration specific to the language management component. You may extend
	| the default models or implement their corresponding interfaces if you need to.
	|
	*/
	'language'				=>	array(
		'model' 	=>	'Waavi\Translation\Models\Language',
	),

	'language_entry'	=>	array(
		'model' 	=>	'Waavi\Translation\Models\LanguageEntry',
	),

);