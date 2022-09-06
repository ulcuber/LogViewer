<?php

namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\Utilities\Factory as FactoryContract;
use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Exceptions\FilesystemException;
use Arcanedev\LogViewer\Exceptions\LogNotFoundException;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class     Factory
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Factory implements FactoryContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The filesystem instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\Utilities\Filesystem
     */
    protected $filesystem;

    /**
     * The log levels instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\Utilities\LogLevels
     */
    private $levels;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels   $levels
     */
    public function __construct(FilesystemContract $filesystem, LogLevelsContract $levels)
    {
        $this->setFilesystem($filesystem);
        $this->setLevels($levels);
    }

    /* -----------------------------------------------------------------
     |  Getter & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the filesystem instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\Utilities\Filesystem
     */
    public function getFilesystem(): FilesystemContract
    {
        return $this->filesystem;
    }

    /**
     * Set the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     *
     * @return self
     */
    public function setFilesystem(FilesystemContract $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get the log levels instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\Utilities\LogLevels
     */
    public function getLevels(): LogLevelsContract
    {
        return $this->levels;
    }

    /**
     * Set the log levels instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels  $levels
     *
     * @return self
     */
    public function setLevels(LogLevelsContract $levels)
    {
        $this->levels = $levels;

        return $this;
    }

    /**
     * Set the log storage path.
     *
     * @param  string  $storagePath
     *
     * @return self
     */
    public function setPath(string $storagePath)
    {
        $this->filesystem->setPath($storagePath);

        return $this;
    }

    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->filesystem->getPattern();
    }

    /**
     * Set the log pattern.
     *
     * @param  string  $date
     * @param  string  $prefix
     * @param  string  $extension
     *
     * @return self
     */
    public function setPattern(
        $prefix    = FilesystemContract::PATTERN_PREFIX,
        $date      = FilesystemContract::PATTERN_DATE,
        $extension = FilesystemContract::PATTERN_EXTENSION
    ) {
        $this->filesystem->setPattern($prefix, $date, $extension);

        return $this;
    }

    /**
     * Get all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function logs(): LogCollection
    {
        return (new LogCollection())->setFilesystem($this->filesystem);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all logs (alias).
     *
     * @see logs
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function all(): LogCollection
    {
        return $this->logs();
    }

    /**
     * Paginate all logs.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 30): LengthAwarePaginator
    {
        return $this->logs()->paginate($perPage);
    }

    /**
     * Get a log by date.
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function log(string $prefix, string $date): Log
    {
        // NOTE: iterator reads file by file so not using this->logs()->log()
        try {
            $path = $this->filesystem->path($prefix, $date);
            return Log::make($prefix, $date, $path, $this->filesystem->readPath($path));
        } catch (FilesystemException $th) {
            throw new LogNotFoundException("Log not found for [$prefix][$date]", 1, $th);
        }
    }

    /**
     * Get a log by date (alias).
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get(string $prefix, string $date): Log
    {
        return $this->log($prefix, $date);
    }

    /**
     * Get log entries.
     *
     * @param  string  $prefix
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection
    {
        return $this->logs()->entries($prefix, $date, $level);
    }

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats(): array
    {
        return $this->logs()->stats();
    }

    /**
     * Get logs statistics table.
     *
     * @param  string|null  $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTable(?string $locale = null): StatsTable
    {
        return StatsTable::make($this->stats(), $this->levels, $locale);
    }

    /**
     * List the log files (paths).
     *
     * @return array
     */
    public function paths(): array
    {
        return $this->logs()->paths();
    }

    /**
     * Get logs count.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->logs()->count();
    }

    /**
     * Get total log entries.
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total(string $level = 'all'): int
    {
        return $this->logs()->total($level);
    }

    /**
     * Get tree menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree(bool $trans = false): array
    {
        return $this->logs()->tree($trans);
    }

    /**
     * Get tree menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function menu(bool $trans = true): array
    {
        return $this->logs()->menu($trans);
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->logs()->isEmpty();
    }
}
