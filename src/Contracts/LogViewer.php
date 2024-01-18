<?php

namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

/**
 * Interface  LogViewer
 *
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
     * @return array
     */
    public function levels(bool $flip = false);

    /**
     * Get the translated log levels.
     *
     *
     * @return array
     */
    public function levelsNames(?string $locale = null);

    /**
     * Set the log storage path.
     *
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
     */
    public function all(): LogCollection;

    /**
     * Paginate all logs.
     *
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 30): LengthAwarePaginatorContract;

    /**
     * Get a log.
     *
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get(string $prefix, string $date);

    /**
     * Get the log entries.
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection;

    /**
     * Download a log file.
     *
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $prefix, string $date, ?string $filename = null, array $headers = []);

    /**
     * Get logs statistics.
     */
    public function stats(): array;

    /**
     * Get logs statistics table.
     */
    public function statsTable(?string $locale = null): StatsTable;

    /**
     * Get logs statistics table for stats.
     */
    public function statsTableFor(array $stats, ?string $locale = null): StatsTable;

    /**
     * Get logs statistics table for date.
     */
    public function statsTableForDate(string $date, ?string $locale = null): StatsTable;

    /**
     * Delete the log.
     *
     *
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function delete(string $prefix, string $date): bool;

    /**
     * Clear the log files.
     */
    public function clear(): bool;

    /**
     * List the log files.
     */
    public function files(): array;

    /**
     * Get all logs.
     */
    public function logs(): LogCollection;

    /**
     * List the log files (only paths).
     */
    public function paths(): array;

    /**
     * Get logs count.
     */
    public function count(): int;

    /**
     * Get entries total from all logs.
     */
    public function total(string $level = 'all'): int;

    /**
     * Get logs tree.
     *
     * @param  bool|false  $trans
     */
    public function tree(bool $trans = false): array;

    /**
     * Get logs menu.
     *
     * @param  bool|true  $trans
     */
    public function menu(bool $trans = true): array;

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     */
    public function isEmpty(): bool;

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the LogViewer version.
     */
    public function version(): string;

    /**
     * Get used memory values in human readable format
     * memory_limit, memory_get_usage, memory_get_peak_usage
     */
    public function memory(): array;

    /**
     * Get used memory string in human readable format
     * memory_get_peak_usage / memory_limit
     *
     * example: `7M / 128M`
     */
    public function memoryString(): string;
}
