<?php

namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

/**
 * Interface  LogViewer
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogViewer extends Patternable
{
    /* -----------------------------------------------------------------
     |  Getters & Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log levels.
     *
     * @param  bool|false  $flip
     *
     * @return array
     */
    public function levels(bool $flip = false);

    /**
     * Get the translated log levels.
     *
     * @param  string|null  $locale
     *
     * @return array
     */
    public function levelsNames(?string $locale = null);

    /**
     * Set the log storage path.
     *
     * @param  string  $path
     *
     * @return self
     */
    public function setPath(string $path);

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all logs.
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
    public function paginate(int $perPage = 30): LengthAwarePaginatorContract;

    /**
     * Get a log.
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get(string $prefix, string $date);

    /**
     * Get the log entries.
     *
     * @param  string  $prefix
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection;

    /**
     * Download a log file.
     *
     * @param  string       $prefix
     * @param  string       $date
     * @param  string|null  $filename
     * @param  array        $headers
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $prefix, string $date, ?string $filename = null, array $headers = []);

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats(): array;

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
     * @param array $stats
     * @param string|null $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTableFor(array $stats, ?string $locale = null): StatsTable;

    /**
     * Get logs statistics table for date.
     *
     * @param  string  $date
     * @param  string|null  $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTableForDate(string $date, ?string $locale = null): StatsTable;

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function delete(string $prefix, string $date): bool;

    /**
     * Clear the log files.
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * List the log files.
     *
     * @return array
     */
    public function files(): array;

    /**
     * Get all logs.
     *
     * @return LogCollection
     */
    public function logs(): LogCollection;

    /**
     * List the log files (only paths).
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
     * Get entries total from all logs.
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total(string $level = 'all'): int;

    /**
     * Get logs tree.
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree(bool $trans = false): array;

    /**
     * Get logs menu.
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu(bool $trans = true): array;

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

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the LogViewer version.
     *
     * @return string
     */
    public function version(): string;
}
