<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Translation Mode
    |--------------------------------------------------------------------------
    |
    | This option controls the translation's bundle mode of operation.
    |
    | Supported:
    |
    |   'mixed'         Both files and the database are queried for language entries, with files taking priority.
    |   'mixed_db'      Both files and the database are queried for language entries, with database taking priority.
    |   'database'      Use the database as the exclusive source for language entries.
    |   'files'         Use files as the exclusive source for language entries [Laravel's default].
     */
    'source'            => env('TRANSLATION_SOURCE', 'mixed'),

    // In case the files source is selected, please enter here the supported locales for your app.
    // Ex: ['en', 'es', 'fr']
    'available_locales' => [],

    /*
    |--------------------------------------------------------------------------
    | Default Translation Cache
    |--------------------------------------------------------------------------
    |
    | Choose whether to leverage Laravel's cache module and how to do so.
    |
    |   'enabled'       Boolean value.
    |   'timeout'       In minutes.
    |
     */
    'cache'             => [
        'enabled' => env('TRANSLATION_CACHE_ENABLED', true),
        'timeout' => env('TRANSLATION_CACHE_TIMEOUT', 60),
        'suffix'  => env('TRANSLATION_CACHE_SUFFIX', 'translation'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Override Translation Models
    |--------------------------------------------------------------------------
    |
    | This option makes possible to override Language and/or Translation
    | models with your own and to use custom table structure.
    | Make sure to extend package models.
    |
    |   'language'      \Waavi\Translation\Models\Language
    |   'translation'   \Waavi\Translation\Models\Translation
    |
     */
    'models'            => [
        'Language'    => null,
        'Translation' => null,
    ],

];
