<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Default Translation Mode
	|--------------------------------------------------------------------------
	|
	| This option controls the translation's bundle mode of operation.
	|
	| Supported:
	| 	'auto'				Uses laravel's 'debug' configuration value to determine which mode operation to choose.
	| 								If debug is true, then the 'mixed' mode is selected.
	|									If debug is false, then the 'database' mode is selected.
	| 	'mixed'				Both files and the database are queried for language entries, with files taking priority.
	| 	'database' 		Use the database as the exclusive source for language entries.
	|   'filesystem'	Use files as the exclusive source for language entries [Laravel's default].
	*/
	'mode'					=>	'auto',

	/*
	|--------------------------------------------------------------------------
	| Default Translation Cache
	|--------------------------------------------------------------------------
	|
	| Choose whether to leverage Laravel's cache module and how to do so.
	|
	| Supported:
	| 	enabled:	'auto'	Uses laravel's 'debug' configuration value to determine whether to activate the cache or not.
	| 										If debug is true, then the cache is deactivated.
	| 										If debug is false, then the cache is active.
	| 						'on'		Use Laravel's cache for language entries.
	| 						'off'		Do not use Laravel's cache for language entries.
	|
	*/
	'cache'					=>	array(
		'enabled' 	=>	'auto',
		'timeout'		=>	60,					// minutes
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