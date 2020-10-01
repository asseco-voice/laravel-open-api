<?php

return [

    /**
     * List of namespaces to search by for possible external models which are not in
     * standard Laravel namespace.
     */
    'namespaces'      => [
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
    'file_name' => 'open-api.yaml',

    /**
     * General OpenApi properties to generate
     */
    'general' => [
        'openapi' => '3.0.0',

        'info' => [
            'title'       => 'Sample API',
            'description' => 'Optional multiline or single-line description in [CommonMark](http://commonmark.org/help/) or HTML.',
            'version'     => '0.0.1',
        ],

        'servers' => [
            [
                'url'         => env('APP_URL'),
                'description' => 'Optional server description, e.g. Main (production) server',
            ],
            [
                'url'         => 'http://api.example.com/v1',
                'description' => 'Optional server description, e.g. Main (production) server',
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
