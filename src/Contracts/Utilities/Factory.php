<?php

namespace Arcanedev\LogViewer\Contracts\Utilities;

use Arcanedev\LogViewer\Contracts\Patternable;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface  Factory
 *
 * @package   Arcanedev\LogViewer\Contracts\Utilities
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface Factory extends Patternable
{
    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the filesystem instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\Utilities\Filesystem
     */
    public function getFilesystem(): Filesystem;

    /**
     * Set the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem);

    /**
     * Get the log levels instance.
     *
     * @return  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels  $levels
     */
    public function getLevels(): LogLevels;

    /**
     * Set the log levels instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels  $levels
     *
     * @return self
     */
    public function setLevels(LogLevels $levels);

    /**
     * Set the log storage path.
     *
     * @param  string  $storagePath
     *
     * @return self
     */
    public function setPath(string $storagePath);

    /**
     * Get all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function logs(): LogCollection;

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
    public function all(): LogCollection;

    /**
     * Paginate all logs.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 30): LengthAwarePaginator;

    /**
     * Get a log by date.
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function log(string $prefix, string $date): Log;

    /**
     * Get a log by date (alias).
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get(string $prefix, string $date): Log;

    /**
     * Get log entries.
     *
     * @param  string  $prefix
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection;

    /**
     * List the log files (paths).
     *
     * @return array
     */
    public function paths(): array;

    /**
     * Get logs count.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get total log entries.
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total(string $level = 'all'): int;

    /**
     * Get tree menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree(bool $trans = false): array;

    /**
     * Get tree menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function menu(bool $trans = true): array;

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats(): array;

    /**
     * Get logs statistics for date.
     *
     * @param  string  $date
     *
     * @return array
     */
    public function statsForDate(string $date): array;

    /**
     * Get logs statistics table.
     *
     * @param  string|null  $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTable(?string $locale = null): StatsTable;

    /**
     * Get logs statistics table for stats.
     *
     * @param  array  $stats
     * @param  string|null  $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTableFor(array $stats, ?string $locale = null): StatsTable;

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
