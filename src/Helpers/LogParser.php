<?php

namespace Arcanedev\LogViewer\Helpers;

/**
 * Class     LogParser
 *
 * @package  Arcanedev\LogViewer\Helpers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogParser
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * Parsed data.
     *
     * @var array
     */
    protected static $parsed = [];

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Parse file content.
     *
     * @param  string  $raw
     *
     * @return array
     */
    public static function parse($raw)
    {
        static::$parsed          = [];
        list($headings, $data) = self::parseRawData($raw);

        // @codeCoverageIgnoreStart
        if (! is_array($headings)) {
            return self::$parsed;
        }
        // @codeCoverageIgnoreEnd

        foreach ($data as $index => $stack) {
            static::$parsed[$index][1] = $stack;
        }
        foreach ($headings as $groupIndex => $heading) {
            foreach ($heading as $matchIndex => $match) {
                static::$parsed[$matchIndex][0][$groupIndex] = $match;
            }
        }

        unset($headings, $data);

        return config('log-viewer.reversed_order') ? array_reverse(static::$parsed) : static::$parsed;
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Parse raw data.
     *
     * @param  string  $raw
     *
     * @return array
     */
    private static function parseRawData($raw)
    {
        $pattern = '/' . REGEX_DATETIME_PATTERN . '/';
        preg_match_all($pattern, $raw, $headings);
        $data    = preg_split($pattern, $raw);

        if ($data[0] < 1) {
            $trash = array_shift($data);
            unset($trash);
        }

        return [$headings, $data];
    }
}
