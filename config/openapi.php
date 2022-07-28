<?php

return [

    'collections' => [

        'default' => [

            'info' => [
                'title' => config('app.name'),
                'description' => null,
                'version' => '1.0.0',
                'license' => [
                    "name" => "GPL-3.0",
                    "url" => "https://www.gnu.org/licenses/gpl-3.0.de.html"
                ],
                'contact' => [
                    "email" => "strategie-navigator@jade-hs.de",
                ],
            ],

            'servers' => [
                [
                    'url' => env('APP_URL'),
                    'description' => "Lokaler Entwicklungsserver",
                    'variables' => [],
                ],
                [
                    'url' => 'https://strategie-navigator.jade-hs.de',
                    'description' => 'Production Server',
                    'variables' => []
                ]
            ],

            'tags' => [
                [
                    "name" => "users",
                    "description" => "User"
                ]
            ],

            'security' => [
                GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement::create()->securityScheme('BearerToken'),
            ],

            // Route for exposing specification.
            // Leave uri null to disable.
            'route' => [
                'uri' => '/openapi',
                'middleware' => [],
            ],

            // Register custom middlewares for different objects.
            'middlewares' => [
                'paths' => [
                    //
                ],
                'components' => [
                    //
                ],
            ],

        ],

    ],

    // Directories to use for locating OpenAPI object definitions.
    'locations' => [
        'callbacks' => [
            app_path('OpenApi/Callbacks'),
        ],

        'request_bodies' => [
            app_path('OpenApi/RequestBodies'),
        ],

        'responses' => [
            app_path('OpenApi/Responses'),
        ],

        'schemas' => [
            app_path('OpenApi/Schemas'),
        ],

        'security_schemes' => [
            app_path('OpenApi/SecuritySchemes'),
        ],
    ],

];
