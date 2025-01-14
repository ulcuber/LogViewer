<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Helpers\LogParser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\LazyCollection;

/**
 * Class     LogEntryCollection
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollection extends LazyCollection
{
    public static $lastCount = null;

    public static function load(string|LazyCollection $raw): static
    {
        return new static(function () use (&$raw) {
            foreach (LogParser::parse($raw) as $entry) {
                [$header, $stack] = $entry;

                yield new LogEntry($header, $stack);
            }
        });
    }

    public function paginate(int $perPage = 20): Paginator|LengthAwarePaginator
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

    public function filterByLevel(string $level): static
    {
        return $this->filter(function (LogEntry $entry) use ($level) {
            return $entry->isSameLevel($level);
        });
    }

    public function filterBySimilarity(string $text, float $similarity): static
    {
        return $this->filter(function (LogEntry $entry) use ($text, $similarity) {
            return $entry->isSimilar($text, $similarity);
        });
    }

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

    public function tree(bool $trans = false): array
    {
        $tree = $this->stats();

        array_walk($tree, function (&$count, $level) use ($trans) {
            $count = [
                'name' => $trans ? log_levels()->get($level) : $level,
                'count' => $count,
            ];
        });

        return $tree;
    }

    public function filter(?callable $callback = null)
    {
        static::$lastCount = null;

        return parent::filter($callback);
    }
}
