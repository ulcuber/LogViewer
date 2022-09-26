<?php

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem;

return [

    /* -----------------------------------------------------------------
     |  Parser regex after date and its groups
     | -----------------------------------------------------------------
     */

    'parser'  => [
        // regex should be without trailing slashes and end with (.*)
        'regex' => '(?:\[([^\[\]]*?)\])?(?:\[([^\[\]]*?)\])? ([a-z]+)\.([A-Z]+): (.*)',
        'property_groups' => [
            3 => 'env',
            4 => 'level',
        ],
        'extra_groups' => [
            1 => 'uuid',
            2 => 'parentUuid',
        ],
    ],

    /* -----------------------------------------------------------------
     |  Log files storage path
     | -----------------------------------------------------------------
     */

    'storage-path'  => storage_path('logs'),

    /* -----------------------------------------------------------------
     |  Log files pattern (glob pattern, not regex)
     | -----------------------------------------------------------------
     */

    'pattern'       => [
        'prefix'    => Filesystem::PATTERN_PREFIX,    // 'laravel-'
        'date'      => Filesystem::PATTERN_DATE,      // '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'
        'extension' => Filesystem::PATTERN_EXTENSION, // '.log'
    ],

    /* -----------------------------------------------------------------
     |  Default prefix for dashboard links
     | -----------------------------------------------------------------
     */

    'default_prefix' => 'laravel',

    /* -----------------------------------------------------------------
     |  Locale
     | -----------------------------------------------------------------
     |  Supported locales :
     |    'auto', 'ar', 'bg', 'de', 'en', 'es', 'et', 'fa', 'fr', 'hu', 'hy', 'id', 'it', 'ja', 'ko', 'nl',
     |    'pl', 'pt-BR', 'ro', 'ru', 'sv', 'th', 'tr', 'zh-TW', 'zh'
     */

    'locale'        => 'auto',

    /* -----------------------------------------------------------------
     |  Theme
     | -----------------------------------------------------------------
     |  Supported themes :
     |    'bootstrap-4'
     |  Make your own theme by adding a folder to the views directory and specifying it here.
     */

    'theme'         => 'bootstrap-4',

    /* -----------------------------------------------------------------
     |  Route settings
     | -----------------------------------------------------------------
     */

    'route'         => [
        'enabled'    => true,

        'attributes' => [
            'prefix'     => 'log-viewer',

            'middleware' => env('ARCANEDEV_LOGVIEWER_MIDDLEWARE') ? explode(',', env('ARCANEDEV_LOGVIEWER_MIDDLEWARE')) : null,
        ],
    ],

    /* -----------------------------------------------------------------
     |  Log entries per page
     | -----------------------------------------------------------------
     |  This defines how many logs & entries are displayed per page.
     */

    'per-page'      => 30,

    /* -----------------------------------------------------------------
     |  Log entries in reversed order
     | -----------------------------------------------------------------
     |  Shows latest entries first if enabled
     */

    'reversed_order'      => true,

    /* -----------------------------------------------------------------
     |  Menu settings
     | -----------------------------------------------------------------
     */

    'menu'  => [
        'filter-route'  => 'log-viewer::logs.filter',

        'icons-enabled' => true,
    ],

    /* -----------------------------------------------------------------
     |  Icons
     | -----------------------------------------------------------------
     */

    'icons' =>  [
        /**
         * Font awesome >= 4.3
         * http://fontawesome.io/icons/
         */
        'all'       => 'fa fa-fw fa-list',                 // http://fontawesome.io/icon/list/
        'emergency' => 'fa fa-fw fa-bug',                  // http://fontawesome.io/icon/bug/
        'alert'     => 'fa fa-fw fa-bullhorn',             // http://fontawesome.io/icon/bullhorn/
        'critical'  => 'fa fa-fw fa-heartbeat',            // http://fontawesome.io/icon/heartbeat/
        'error'     => 'fa fa-fw fa-times-circle',         // http://fontawesome.io/icon/times-circle/
        'warning'   => 'fa fa-fw fa-exclamation-triangle', // http://fontawesome.io/icon/exclamation-triangle/
        'notice'    => 'fa fa-fw fa-exclamation-circle',   // http://fontawesome.io/icon/exclamation-circle/
        'info'      => 'fa fa-fw fa-info-circle',          // http://fontawesome.io/icon/info-circle/
        'debug'     => 'fa fa-fw fa-life-ring',            // http://fontawesome.io/icon/life-ring/
    ],

    /* -----------------------------------------------------------------
     |  Colors
     | -----------------------------------------------------------------
     */

    'colors' =>  [
        'levels'    => [
            'empty'     => '#D1D1D1',
            'all'       => '#8A8A8A',
            'emergency' => '#B71C1C',
            'alert'     => '#D32F2F',
            'critical'  => '#F44336',
            'error'     => '#FF5722',
            'warning'   => '#FF9100',
            'notice'    => '#4CAF50',
            'info'      => '#1976D2',
            'debug'     => '#90CAF9',
        ],
        'extras' => [
            'uuid' => '#1976D2',
            'parentUuid' => '#4CAF50',
        ],
    ],

    /* -----------------------------------------------------------------
     |  Strings to highlight in stack trace
     | -----------------------------------------------------------------
     */

    'highlight' => [
        '^#\d+',
        '^Stack trace:',
    ],

    /* -----------------------------------------------------------------
     |  Similarity threshold to show/exclude similar entires
     | -----------------------------------------------------------------
     */

    'similarity' => env('ARCANEDEV_LOGVIEWER_SIMILARITY', 76),

    /* -----------------------------------------------------------------
     |  Max log file size
     | -----------------------------------------------------------------
     | Prevents `Allowed memory size of n bytes exhausted`
     | In bytes
     */

    'max_log_size' => env('ARCANEDEV_LOGVIEWER_MAX_SIZE', 1024 * 1024 * 5),

];
