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
     */
    public function getFilesystem(): Filesystem;

    /**
     * Set the filesystem instance.
     *
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem);

    /**
     * Get the log levels instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\Utilities\LogLevels $levels
     */
    public function getLevels(): LogLevels;

    /**
     * Set the log levels instance.
     *
     *
     * @return self
     */
    public function setLevels(LogLevels $levels);

    /**
     * Set the log storage path.
     *
     *
     * @return self
     */
    public function setPath(string $storagePath);

    /**
     * Get all logs.
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
     */
    public function all(): LogCollection;

    /**
     * Paginate all logs.
     */
    public function paginate(int $perPage = 30): LengthAwarePaginator;

    /**
     * Get a log by date.
     */
    public function log(string $prefix, string $date): Log;

    /**
     * Get a log by date (alias).
     */
    public function get(string $prefix, string $date): Log;

    /**
     * Get log entries.
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection;

    /**
     * List the log files (paths).
     */
    public function paths(): array;

    /**
     * Get logs count.
     */
    public function count(): int;

    /**
     * Get total log entries.
     */
    public function total(string $level = 'all'): int;

    /**
     * Get tree menu.
     */
    public function tree(bool $trans = false): array;

    /**
     * Get tree menu.
     */
    public function menu(bool $trans = true): array;

    /**
     * Get logs statistics.
     */
    public function stats(): array;

    /**
     * Get logs statistics for date.
     */
    public function statsForDate(string $date): array;

    /**
     * Get logs statistics table.
     */
    public function statsTable(?string $locale = null): StatsTable;

    /**
     * Get logs statistics table for stats.
     */
    public function statsTableFor(array $stats, ?string $locale = null): StatsTable;

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     */
    public function isEmpty(): bool;
}
