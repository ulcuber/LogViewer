<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Helpers\LogParser;
use Closure;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use JsonSerializable;
use SplFileInfo;

/**
 * Class     Log
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Log implements Arrayable, Countable, Jsonable, JsonSerializable
{
    public string $prefix;

    public string $date;

    protected string $path;

    protected int $count = 0;

    protected array|Closure|null $stats = null;

    protected LogEntryCollection $entries;

    protected SplFileInfo $file;

    public function __construct(
        string $prefix, string $date, string $path, string|LazyCollection $raw, bool $stats = false
    ) {
        $this->prefix = $prefix;
        $this->date = $date;
        $this->path = $path;
        $this->file = new SplFileInfo($path);

        $this->entries = LogEntryCollection::load($raw);

        if ($stats) {
            $this->stats = function () use (&$raw) {
                $stats = LogParser::stats($raw);
                $this->count = $stats['all'];

                return $stats;
            };
        } else {
            $this->count = LogParser::count($raw);
            LogEntryCollection::$lastCount = $this->count;
        }
    }

    public static function make(
        string $prefix, string $date, string $path, string|LazyCollection $raw, bool $stats = false
    ): static {
        return new static($prefix, $date, $path, $raw, $stats);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function file(): SplFileInfo
    {
        return $this->file;
    }

    public function size(): string
    {
        return $this->formatSize($this->file->getSize());
    }

    public function createdAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->file()->getATime());
    }

    public function updatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->file()->getMTime());
    }

    public function entries(string $level = 'all'): LogEntryCollection
    {
        return $level === 'all'
            ? $this->entries
            : $this->getByLevel($level);
    }

    public function getByLevel(string $level): LogEntryCollection
    {
        return $this->entries->filterByLevel($level);
    }

    public function getSimilar(string $text, float $similarity): LogEntryCollection
    {
        return $this->entries->filterBySimilarity($text, $similarity);
    }

    public function stats(): array
    {
        if (is_callable($this->stats)) {
            $this->stats = ($this->stats)();
        }

        if ($this->stats) {
            return $this->stats;
        }

        return $this->entries->stats();
    }

    public function tree(bool $trans = false): array
    {
        return $this->entries->tree($trans);
    }

    public function menu(bool $trans = true): array
    {
        return log_menu()->make($this, $trans);
    }

    public function toArray(): array
    {
        return [
            'prefix' => $this->prefix,
            'date' => $this->date,
            'path' => $this->path,
            'entries' => $this->entries->toArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function formatSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
