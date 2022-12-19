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

            'middleware' => env('ARCANEDEV_LOGVIEWER_MIDDLEWARE')
                ? explode(',', env('ARCANEDEV_LOGVIEWER_MIDDLEWARE')) : null,
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
         * @see https://icons.getbootstrap.com
         */
        'all'       => 'bi bi-list',
        'emergency' => 'bi bi-bug',
        'alert'     => 'bi bi-bullhorn',
        'critical'  => 'bi bi-heartbeat',
        'error'     => 'bi bi-times-circle',
        'warning'   => 'bi bi-exclamation-triangle',
        'notice'    => 'bi bi-exclamation-circle',
        'info'      => 'bi bi-info-circle',
        'debug'     => 'bi bi-life-ring',
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
        '^\[stacktrace\]',
        'app\/',
    ],

    /* -----------------------------------------------------------------
     |  Similarity threshold to show/exclude similar entires
     | -----------------------------------------------------------------
     */

    'similarity' => env('ARCANEDEV_LOGVIEWER_SIMILARITY', 76),

    /* -----------------------------------------------------------------
     |  Max log file size
     | -----------------------------------------------------------------
     | Filters out large files
     | In bytes
     */

    'max_log_size' => env('ARCANEDEV_LOGVIEWER_MAX_SIZE', 1024 * 1024 * 500),

    /* -----------------------------------------------------------------
     |  Min log file size to process by chunks
     | -----------------------------------------------------------------
     | Prevents `Allowed memory size of n bytes exhausted`
     |
     | Used to find balance between preg_match_all memory consumption
     | and frequent SplFileObject::fread calls
     |
     | Could be increased with php.ini memory_limit too optimize CPU usage
     |
     | In bytes
     */

    'chunked_size_threshold' => env('ARCANEDEV_LOGVIEWER_THRESHOLD', 1024 * 1024 * 5),

    /* -----------------------------------------------------------------
     |  Chunk size when processing file by chunks
     | -----------------------------------------------------------------
     |
     | Controls SplFileObject::fread chunk size
     | to adjust between fread frequency / peak memory usage
     |
     | In bytes
     */

    'chunk_size' => env('ARCANEDEV_LOGVIEWER_CHUNK_SIZE', 1024 * 1024),

];
