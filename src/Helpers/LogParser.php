<?php

namespace Arcanedev\LogViewer\Helpers;

use Arcanedev\LogViewer\Entities\LogEntry;
use Illuminate\Support\LazyCollection;

/**
 * Class     LogParser
 *
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
     * @param  string|LazyCollection  $raw
     * @return array|LazyCollection
     */
    public static function parse(&$raw)
    {
        static::$parsed = [];

        if ($raw instanceof LazyCollection) {
            $pattern = '/^'.REGEX_DATE_PATTERN.REGEX_DATETIME_SEPARATOR
                .REGEX_TIME_PATTERN.REGEX_MS_PATTERN.REGEX_TIMEZONE_PATTERN.'/';

            return LazyCollection::make(function () use (&$pattern, &$raw) {
                $stack = '';
                $prevHeader = null;
                $reminder = '';
                foreach ($raw as $chunk) {
                    $lines = explode("\n", $reminder.$chunk);
                    $reminder = array_pop($lines);
                    foreach ($lines as $line) {
                        /**
                         * explode string before preg_match
                         * because preg_match is memory consuming
                         * and there is nothing to match yet in header reminder
                         */
                        $headings = explode(']', $line, 2);
                        if (
                            count($headings) === 2 &&
                            preg_match($pattern, mb_strcut($headings[0], 1), $header)
                        ) {
                            // shift whole match
                            array_shift($header);
                            array_push($header, $headings[1]);
                            if ($stack) {
                                yield [$prevHeader, $stack];
                            }
                            $prevHeader = $header;
                            $stack = '';
                        } else {
                            $stack .= $line.PHP_EOL;
                        }
                        unset($headings);
                    }
                }
                if ($stack) {
                    yield [$prevHeader, $stack];
                }
            });
        } else {
            $pattern = '/(?:\r?\n|^)'.REGEX_DATETIME_PATTERN.'/';

            preg_match_all($pattern, $raw, $headings);
            // shift whole match
            array_shift($headings);
            foreach ($headings as $groupIndex => &$heading) {
                foreach ($heading as $matchIndex => &$match) {
                    static::$parsed[$matchIndex][0][$groupIndex] = $match;
                }
            }
            unset($headings);

            $data = preg_split($pattern, $raw);
            if ($data[0] < 1) {
                $trash = array_shift($data);
                unset($trash);
            }
            foreach ($data as $index => &$stack) {
                static::$parsed[$index][1] = $stack;
            }
            unset($data);
        }

        return config('log-viewer.reversed_order') ? array_reverse(static::$parsed) : static::$parsed;
    }

    public static function count($raw): int
    {
        if ($raw instanceof LazyCollection) {
            $pattern = '/^'.REGEX_DATE_PATTERN.REGEX_DATETIME_SEPARATOR
                .REGEX_TIME_PATTERN.REGEX_MS_PATTERN.REGEX_TIMEZONE_PATTERN.'/';
            $count = 0;
            $reminder = '';
            foreach ($raw as $chunk) {
                $lines = explode("\n", $reminder.$chunk);
                $reminder = array_pop($lines);
                foreach ($lines as $line) {
                    if (
                        ($pos = mb_strpos($line, ']')) &&
                        preg_match($pattern, mb_strcut($line, 1, $pos), $header)
                    ) {
                        $count++;
                    }
                }
            }

            return $count;
        } else {
            $pattern = '/(?:\r?\n|^)'.REGEX_DATETIME_PATTERN.'/';

            return preg_match_all($pattern, $raw, $headings) ?: 0;
        }
    }

    public static function stats($raw): array
    {
        $counters = static::initStats();

        $entryRegex = LogEntry::$regex;
        $levelGroup = LogEntry::levelGroup();

        if ($raw instanceof LazyCollection) {
            $pattern = '/^'.REGEX_DATE_PATTERN.REGEX_DATETIME_SEPARATOR
                .REGEX_TIME_PATTERN.REGEX_MS_PATTERN.REGEX_TIMEZONE_PATTERN.'/';
            $reminder = '';
            foreach ($raw as $chunk) {
                $lines = explode("\n", $reminder.$chunk);
                $reminder = array_pop($lines);
                foreach ($lines as $line) {
                    $headings = explode(']', $line, 2);
                    if (
                        count($headings) === 2 &&
                        preg_match($pattern, mb_strcut($headings[0], 1), $header)
                    ) {
                        preg_match($entryRegex, $headings[1], $matches);
                        $level = strtolower($matches[$levelGroup]);
                        $counters[$level]++;
                        $counters['all']++;
                    }
                }
            }
        } else {
            $pattern = '/(?:\r?\n|^)'.REGEX_DATETIME_PATTERN.'/';
            preg_match_all($pattern, $raw, $headings);
            // shift whole match
            array_shift($headings);
            $heading = array_pop($headings);
            foreach ($heading as $match) {
                preg_match($entryRegex, $match, $matches);
                $level = strtolower($matches[$levelGroup]);
                $counters[$level]++;
                $counters['all']++;
            }
        }

        return $counters;
    }

    /**
     * Init stats counters.
     */
    public static function initStats(): array
    {
        $levels = array_merge_recursive(
            ['all'],
            array_keys(log_viewer()->levels(true))
        );

        return array_map(function () {
            return 0;
        }, array_flip($levels));
    }
}
