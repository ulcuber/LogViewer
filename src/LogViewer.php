<?php

namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Contracts\Utilities\Factory as FactoryContract;
use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

/**
 * Class     LogViewer
 *
 * @package  Arcanedev\LogViewer
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewer implements LogViewerContract
{
    /* -----------------------------------------------------------------
     |  Constants
     | -----------------------------------------------------------------
     */

    /**
     * LogViewer Version
     */
    public const VERSION = '5.2.0';

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The factory instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\Utilities\Factory
     */
    protected $factory;

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
    protected $levels;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new instance.
     *
     * @param \Arcanedev\LogViewer\Contracts\Utilities\Factory $factory
     * @param \Arcanedev\LogViewer\Contracts\Utilities\Filesystem $filesystem
     * @param \Arcanedev\LogViewer\Contracts\Utilities\LogLevels $levels
     */
    public function __construct(
        FactoryContract $factory,
        FilesystemContract $filesystem,
        LogLevelsContract $levels
    ) {
        $this->factory = $factory;
        $this->filesystem = $filesystem;
        $this->levels = $levels;

        LogEntry::configure();
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the log levels.
     *
     * @param bool $flip
     *
     * @return array
     */
    public function levels(bool $flip = false): array
    {
        return $this->levels->lists($flip);
    }

    /**
     * Get the translated log levels.
     *
     * @param string|null $locale
     *
     * @return array
     */
    public function levelsNames(?string $locale = null): array
    {
        return $this->levels->names($locale);
    }

    /**
     * Set the log storage path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path)
    {
        $this->factory->setPath($path);

        return $this;
    }

    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->factory->getPattern();
    }

    /**
     * Set the log pattern.
     *
     * @param string $prefix
     * @param string $date
     * @param string $extension
     *
     * @return self
     */
    public function setPattern(
        $prefix = FilesystemContract::PATTERN_PREFIX,
        $date = FilesystemContract::PATTERN_DATE,
        $extension = FilesystemContract::PATTERN_EXTENSION
    ) {
        $this->factory->setPattern($prefix, $date, $extension);

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function all(): LogCollection
    {
        return $this->factory->all();
    }

    /**
     * Paginate all logs.
     *
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 30): LengthAwarePaginatorContract
    {
        return $this->factory->paginate($perPage);
    }

    /**
     * Get a log.
     *
     * @param string $prefix
     * @param string $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get(string $prefix, string $date)
    {
        return $this->factory->log($prefix, $date);
    }

    /**
     * Get the log entries.
     *
     * @param string $prefix
     * @param string $date
     * @param string $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries(string $prefix, string $date, string $level = 'all'): LogEntryCollection
    {
        return $this->factory->entries($prefix, $date, $level);
    }

    /**
     * Download a log file.
     *
     * @param string $date
     * @param string|null $filename
     * @param array $headers
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $prefix, string $date, ?string $filename = null, array $headers = [])
    {
        if (is_null($filename)) {
            $filename = "{$prefix}-{$date}.log";
        }

        $path = $this->filesystem->path($prefix, $date);

        return response()->download($path, $filename, $headers);
    }

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats(): array
    {
        return $this->factory->stats();
    }

    /**
     * Get logs statistics table.
     *
     * @param string|null $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTable(?string $locale = null): StatsTable
    {
        return $this->factory->statsTable($locale);
    }

    /**
     * Get logs statistics table for stats.
     *
     * @param array $stats
     * @param string|null $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTableFor(array $stats, ?string $locale = null): StatsTable
    {
        return $this->factory->statsTableFor($stats, $locale);
    }

    /**
     * Get logs statistics table for date.
     *
     * @param string $date
     * @param string|null $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTableForDate(string $date, ?string $locale = null): StatsTable
    {
        return $this->factory->statsTableForDate($date, $locale);
    }

    /**
     * Delete the log.
     *
     * @param string $prefix
     * @param string $date
     *
     * @return bool
     */
    public function delete(string $prefix, string $date): bool
    {
        return $this->filesystem->delete($prefix, $date);
    }

    /**
     * Clear the log files.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->filesystem->clear();
    }

    /**
     * Clear path cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->filesystem->clearCache();
    }

    /**
     * Get all valid log files.
     *
     * @return array
     */
    public function files(): array
    {
        return $this->filesystem->logs();
    }

    /**
     * Get all logs.
     *
     * @return LogCollection
     */
    public function logs(): LogCollection
    {
        return $this->factory->logs();
    }

    /**
     * List the log files (only paths).
     *
     * @return array
     */
    public function paths(): array
    {
        return $this->factory->paths();
    }

    /**
     * Get logs count.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->factory->count();
    }

    /**
     * Get entries total from all logs.
     *
     * @param string $level
     *
     * @return int
     */
    public function total(string $level = 'all'): int
    {
        return $this->factory->total($level);
    }

    /**
     * Get logs tree.
     *
     * @param bool $trans
     *
     * @return array
     */
    public function tree(bool $trans = false): array
    {
        return $this->factory->tree($trans);
    }

    /**
     * Get logs menu.
     *
     * @param bool $trans
     *
     * @return array
     */
    public function menu(bool $trans = true): array
    {
        return $this->factory->menu($trans);
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
        return $this->factory->isEmpty();
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the LogViewer version.
     *
     * @return string
     */
    public function version(): string
    {
        try {
            return \Composer\InstalledVersions::getPrettyVersion('arcanedev/log-viewer');
        } catch (\Throwable $th) {
            return self::VERSION;
        }
    }

    public function memory(): array
    {
        $human = function (int $value): string {
            $index = 0;
            $prefixes = ['bytes', 'K', 'M', 'G', 'T'];
            while ($value > 1024) {
                $value /= 1024;
                $index++;
            }
            return round($value) . ($prefixes[$index] ?? '');
        };
        $memory = [
            'memory_limit' => ini_get('memory_limit'),
            'memory_get_usage' => $human(memory_get_usage()),
            'memory_get_peak_usage' => $human(memory_get_peak_usage()),
        ];

        return $memory;
    }

    public function memoryString(): string
    {
        $memory = $this->memory();
        return $memory['memory_get_peak_usage'] . ' / ' . $memory['memory_limit'];
    }
}
