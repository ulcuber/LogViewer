<?php

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Helpers\LogParser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

/**
 * Class     LogEntryCollection
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollection extends LazyCollection
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Load raw log entries.
     *
     * @param  string  $raw
     *
     * @return self
     */
    public static function load($raw)
    {
        return new static(function () use ($raw) {
            foreach (LogParser::parse($raw) as $entry) {
                list($header, $stack) = $entry;

                yield new LogEntry($header, $stack);
            }
        });
    }

    /**
     * Paginate log entries.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 20)
    {
        $request = request();
        $page = $request->get('page', 1);
        $path = $request->url();

        /**
         * NOTE: count iterates through all
         * and forPage skips offset then takes perPage items
         * so just taking all items to iterate only once
        */
        $items = $this->all();
        $total = count($items);
        $offset = max(0, ($page - 1) * $perPage);
        $items = array_slice($items, $offset, $perPage, true);

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            compact('path')
        );
    }

    /**
     * Get filtered log entries by level.
     *
     * @param  string  $level
     *
     * @return self
     */
    public function filterByLevel($level)
    {
        return $this->filter(function (LogEntry $entry) use ($level) {
            return $entry->isSameLevel($level);
        });
    }

    /**
     * Get filtered log entries by level.
     *
     * @param  string  $text
     * @param  float  $similarity
     *
     * @return self
     */
    public function filterBySimilarity(string $text, float $similarity)
    {
        return $this->filter(function (LogEntry $entry) use ($text, $similarity) {
            return $entry->isSimilar($text, $similarity);
        });
    }

    /**
     * Get log entries stats.
     *
     * @return array
     */
    public function stats()
    {
        $counters = $this->initStats();

        foreach ($this->groupBy('level') as $level => $entries) {
            $counters[$level] = $count = count($entries);
            $counters['all'] += $count;
        }

        return $counters;
    }

    /**
     * Get the log entries navigation tree.
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        $tree = $this->stats();

        array_walk($tree, function (&$count, $level) use ($trans) {
            $count = [
                'name'  => $trans ? log_levels()->get($level) : $level,
                'count' => $count,
            ];
        });

        return $tree;
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Init stats counters.
     *
     * @return array
     */
    private function initStats()
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
