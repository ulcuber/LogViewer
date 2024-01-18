<?php

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Helpers\LogParser;
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
class Log implements Arrayable, Jsonable, JsonSerializable
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    public $prefix;

    /** @var string */
    public $date;

    /** @var string */
    protected $path;

    /** @var array|null */
    protected $stats;

    /** @var \Arcanedev\LogViewer\Entities\LogEntryCollection */
    protected $entries;

    /** @var \SplFileInfo */
    protected $file;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Log constructor.
     *
     * @param  string|LazyCollection  $raw
     */
    public function __construct(string $prefix, string $date, string $path, $raw, bool $stats = false)
    {
        $this->prefix = $prefix;
        $this->date = $date;
        $this->path = $path;
        $this->file = new SplFileInfo($path);

        $this->entries = LogEntryCollection::load($raw);

        if ($stats) {
            $this->stats = function () use ($raw) {
                return LogParser::stats($raw);
            };
        } else {
            LogEntryCollection::$lastCount = LogParser::count($raw);
        }
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get log path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get file info.
     *
     * @return \SplFileInfo
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Get file size.
     *
     * @return string
     */
    public function size()
    {
        return $this->formatSize($this->file->getSize());
    }

    /**
     * Get file creation date.
     *
     * @return \Carbon\Carbon
     */
    public function createdAt()
    {
        return Carbon::createFromTimestamp($this->file()->getATime());
    }

    /**
     * Get file modification date.
     *
     * @return \Carbon\Carbon
     */
    public function updatedAt()
    {
        return Carbon::createFromTimestamp($this->file()->getMTime());
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make a log object.
     *
     * @param  string|LazyCollection  $raw
     * @return self
     */
    public static function make(string $prefix, string $date, string $path, $raw, bool $stats = false)
    {
        return new self($prefix, $date, $path, $raw, $stats);
    }

    /**
     * Get log entries.
     *
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries(string $level = 'all')
    {
        return $level === 'all'
            ? $this->entries
            : $this->getByLevel($level);
    }

    /**
     * Get filtered log entries by level.
     *
     * @param  string  $level
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function getByLevel($level)
    {
        return $this->entries->filterByLevel($level);
    }

    /**
     * Get filtered log entries by similarity.
     *
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function getSimilar(string $text, float $similarity)
    {
        return $this->entries->filterBySimilarity($text, $similarity);
    }

    /**
     * Get log stats.
     */
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

    /**
     * Get the log navigation tree.
     *
     * @param  bool  $trans
     * @return array
     */
    public function tree($trans = false)
    {
        return $this->entries->tree($trans);
    }

    /**
     * Get log entries menu.
     *
     * @param  bool  $trans
     * @return array
     */
    public function menu($trans = true)
    {
        return log_menu()->make($this, $trans);
    }

    /* -----------------------------------------------------------------
     |  Convert Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'prefix' => $this->prefix,
            'date' => $this->date,
            'path' => $this->path,
            'entries' => $this->entries->toArray(),
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serialize the log object to json data.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Format the file size.
     *
     * @param  int  $bytes
     * @param  int  $precision
     * @return string
     */
    private function formatSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision).' '.$units[$pow];
    }
}
