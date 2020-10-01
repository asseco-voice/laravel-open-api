<?php

use Illuminate\Support\Facades\Config;

return [

    /**
     * List of namespaces to search by for possible external models which are not in
     * standard Laravel namespace.
     */
    'namespaces' => [
        'App\\',
        'Voice\\Containers\\App\\',
        'Voice\\CustomFields\\App\\',
    ],

    /**
     * Force re-caching. Used as a part of the command flag, not to be used manually.
     */
    'bust_cache' => false,

    /**
     * Name of generated file
     */
    'file_name'  => 'open-api.yaml',

    /**
     * General OpenApi properties to generate
     */
    'general'    => [
        'openapi' => '3.0.0',

        'info' => [
            'title'       => ucfirst(Config::get('app.name')) . ' API',
            'description' => 'Generated with this [awesome package](https://github.com/asseco-voice/laravel-open-api)!',
            'version'     => '0.0.1',
        ],

        'servers' => [
            [
                'url'         => Config::get('app.url'),
                'description' => 'Default server',
            ],
        ],
    ],

    'routes' => [
        'exclude' => [
//            'random.*', 'route.*', 'name.*'
        ],

        'global_headers' => '',
    ],


];
