<?php

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Exceptions\LogNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

/**
 * Class     LogCollection
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogCollection extends LazyCollection
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LogViewer\Contracts\Utilities\Filesystem */
    private $filesystem;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * LogCollection constructor.
     *
     * @param  mixed  $source
     */
    public function __construct($source = null)
    {
        $this->setFilesystem(app(FilesystemContract::class));

        if (is_null($source)) {
            $source = function () {
                foreach ($this->filesystem->paths(true) as $prefix => $paths) {
                    foreach ($paths as $date => $path) {
                        yield $path => Log::make($prefix, $date, $path, $this->filesystem->readPath($path), true);
                    }
                }
            };
        }

        parent::__construct($source);
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the filesystem instance.
     *
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function setFilesystem(FilesystemContract $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get a log.
     *
     * @param  string  $path
     * @param  mixed|null  $default
     * @return \Arcanedev\LogViewer\Entities\Log
     *
     * @throws \Arcanedev\LogViewer\Exceptions\LogNotFoundException
     */
    public function get($path, $default = null)
    {
        $value = parent::get($path, $default);

        if (! $value) {
            throw new LogNotFoundException("Log not found in this path [$path]");
        }

        return $value;
    }

    /**
     * Paginate logs.
     *
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 30)
    {
        $request = request();
        $page = $request->get('page', 1);
        $path = $request->url();

        return new LengthAwarePaginator(
            $this->forPage($page, $perPage),
            $this->count(),
            $perPage,
            $page,
            compact('path')
        );
    }

    /**
     * Get a log via prefix and date.
     *
     * @see get()
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function log(string $prefix, string $date)
    {
        $path = $this->filesystem->path($prefix, $date);

        return $this->get($path);
    }

    /**
     * Get log entries.
     *
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries(string $prefix, string $date, string $level = 'all')
    {
        return $this->log($prefix, $date)->entries($level);
    }

    /**
     * Get logs statistics.
     */
    public function stats(): array
    {
        $stats = [];

        foreach ($this as $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $stats[$log->prefix][$log->date] = $log->stats();
        }

        return $stats;
    }

    /**
     * Get logs statistics for date.
     */
    public function statsForDate(string $date): array
    {
        $stats = [];

        $logs = $this->filter(function (Log $log) use ($date) {
            return $log->date === $date;
        });

        foreach ($logs as $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $stats[$log->prefix][$log->date] = $log->stats();
        }

        return $stats;
    }

    /**
     * List the log files (paths).
     *
     * @return array
     */
    public function paths()
    {
        return $this->keys()->toArray();
    }

    /**
     * Get entries total.
     *
     *
     * @return int
     */
    public function total(string $level = 'all')
    {
        return (int) $this->sum(function (Log $log) use ($level) {
            return $log->entries($level)->count();
        });
    }

    /**
     * Get logs tree.
     *
     *
     * @return array
     */
    public function tree(bool $trans = false)
    {
        $tree = [];

        foreach ($this as $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $tree[$log->prefix][$log->date] = $log->tree($trans);
        }

        return $tree;
    }

    /**
     * Get logs menu.
     *
     *
     * @return array
     */
    public function menu(bool $trans = true)
    {
        $menu = [];

        foreach ($this as $path => $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $menu[$path] = $log->menu($trans);
        }

        return $menu;
    }
}
