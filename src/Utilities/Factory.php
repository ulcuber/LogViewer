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
     */
    public function getFilesystem(): FilesystemContract
    {
        return $this->filesystem;
    }

    /**
     * Set the filesystem instance.
     *
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
     */
    public function getLevels(): LogLevelsContract
    {
        return $this->levels;
    }

    /**
     * Set the log levels instance.
     *
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
     * @return self
     */
    public function setPattern(
        $prefix = FilesystemContract::PATTERN_PREFIX,
        $date = FilesystemContract::PATTERN_DATE,
        $extension = FilesystemContract::PATTERN_EXTENSION
    ) {
        $this->filesystem->setPattern($prefix, $date, $extension);

        return $this;
    }

    /**
     * Get all logs.
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
     */
    public function all(): LogCollection
    {
        return $this->logs();
    }

    /**
     * Paginate all logs.
     */
    public function paginate(int $perPage = 30): LengthAwarePaginator
    {
        return $this->logs()->paginate($perPage);
    }

    /**
     * Get a log by date.
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
     */
    public function get(string $prefix, string $date): Log
    {
        return $this->log($prefix, $date);
    }

    /**
     * Get log entries.
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection
    {
        return $this->logs()->entries($prefix, $date, $level);
    }

    /**
     * Get logs statistics.
     */
    public function stats(): array
    {
        return $this->logs()->stats();
    }

    /**
     * Get logs statistics for date.
     */
    public function statsForDate(string $date): array
    {
        return $this->logs()->statsForDate($date);
    }

    /**
     * Get logs statistics table.
     */
    public function statsTable(?string $locale = null): StatsTable
    {
        return StatsTable::make($this->stats(), $this->levels, $locale);
    }

    /**
     * Get logs statistics table for stats.
     */
    public function statsTableFor(array $stats, ?string $locale = null): StatsTable
    {
        return StatsTable::make($stats, $this->levels, $locale);
    }

    /**
     * Get logs statistics table for log.
     */
    public function statsTableForDate(string $date, ?string $locale = null): StatsTable
    {
        return StatsTable::make($this->statsForDate($date), $this->levels, $locale);
    }

    /**
     * List the log files (paths).
     */
    public function paths(): array
    {
        return $this->logs()->paths();
    }

    /**
     * Get logs count.
     */
    public function count(): int
    {
        return $this->logs()->count();
    }

    /**
     * Get total log entries.
     */
    public function total(string $level = 'all'): int
    {
        return $this->logs()->total($level);
    }

    /**
     * Get tree menu.
     */
    public function tree(bool $trans = false): array
    {
        return $this->logs()->tree($trans);
    }

    /**
     * Get tree menu.
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
     */
    public function isEmpty(): bool
    {
        return $this->logs()->isEmpty();
    }
}
