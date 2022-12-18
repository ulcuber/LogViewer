<?php

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Helpers\LogParser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\LazyCollection;

/**
 * Class     LogEntryCollection
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollection extends LazyCollection
{
    public static $lastCount = null;

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
        return new static(function () use (&$raw) {
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
     * @return Paginator|LengthAwarePaginator
     */
    public function paginate($perPage = 20)
    {
        $request = request();
        $page = $request->get('page', 1);
        $path = $request->url();

        if (static::$lastCount === null) {
            $offset = max(0, ($page - 1) * $perPage);
            // NOTE: + 1 for hasMore
            $items = $this->slice($offset, $perPage + 1);

            // No LengthAwarePaginator else it will iterate twice for count
            $paginator = new Paginator(
                $items,
                $perPage,
                $page,
                compact('path')
            );
        } else {
            $paginator = new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                static::$lastCount,
                $perPage,
                $page,
                compact('path')
            );
        }

        $paginator->appends($request->all());

        return $paginator;
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
    public function stats(): array
    {
        $counters = LogParser::initStats();

        /** @var LogEntry $entry */
        foreach ($this as $entry) {
            $counters[$entry->level]++;
            $counters['all']++;
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

    public function filter(callable $callback = null)
    {
        static::$lastCount = null;
        return parent::filter($callback);
    }
}
