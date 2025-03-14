<?php

return [
    /*
     * The type of documentation output to generate.
     * - "static" will generate a static HTMl page in the /public/docs folder,
     * - "laravel" will generate the documentation as a Blade view, so you can add routing and authentication.
     */
    'type' => 'static',

    /*
     * The routes for which documentation should be generated.
     */
    'routes' => [
        /*
         * Route patterns to include in the documentation.
         */
        'include' => [
            // Match all routes
            '*',
        ],

        /*
         * Route patterns to exclude from the documentation.
         */
        'exclude' => [
            // Exclude routes containing 'CategoryProductController'
            '*CategoryProductController*',
        ],
    ],

    /*
     * Generate a Postman collection in addition to HTML docs.
     */
    'postman' => [
        'enabled' => true,
    ],

    /*
     * The name for the group of routes which do not have a @group specified.
     */
    'default_group' => 'Endpoints',

    /*
     * Custom logo path. This will be used as the value of the src attribute for the <img> tag,
     * so make sure it points to a public URL or path accessible from your web server.
     * Set this to false to not use a logo.
     */
    'logo' => false,
];