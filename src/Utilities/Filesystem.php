<?php

namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Exceptions\FilesystemException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use Illuminate\Support\LazyCollection;

/**
 * Class     Filesystem
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Filesystem implements FilesystemContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The base storage path.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * The log files prefix pattern.
     *
     * @var string
     */
    protected $prefixPattern;

    /**
     * The log files date pattern.
     *
     * @var string
     */
    protected $datePattern;

    /**
     * The log files extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * Regex to match parts of filename.
     *
     * @var string
     */
    protected $regex;

    /**
     * Logs cache to use glob once
     *
     * @var array
     */
    protected $logsCache;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Filesystem constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string                             $storagePath
     */
    public function __construct(IlluminateFilesystem $files, $storagePath)
    {
        $this->filesystem = $files;
        $this->setPath($storagePath);
        $this->setPattern();

        $this->regex = '/' . '([\w-]+)-(' . REGEX_DATE_PATTERN . ')' . '/';
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the files instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getInstance(): IlluminateFilesystem
    {
        return $this->filesystem;
    }

    /**
     * Set the log storage path.
     *
     * @param  string  $storagePath
     *
     * @return self
     */
    public function setPath($storagePath)
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->prefixPattern . $this->datePattern . $this->extension;
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
        $prefix    = self::PATTERN_PREFIX,
        $date      = self::PATTERN_DATE,
        $extension = self::PATTERN_EXTENSION
    ) {
        $this->setPrefixPattern($prefix);
        $this->setDatePattern($date);
        $this->setExtension($extension);

        return $this;
    }

    /**
     * Set the log date pattern.
     *
     * @param  string  $datePattern
     *
     * @return self
     */
    public function setDatePattern($datePattern)
    {
        $this->datePattern = $datePattern;

        return $this;
    }

    /**
     * Set the log prefix pattern.
     *
     * @param  string  $prefixPattern
     *
     * @return self
     */
    public function setPrefixPattern($prefixPattern)
    {
        $this->prefixPattern = $prefixPattern;

        return $this;
    }

    /**
     * Set the log extension.
     *
     * @param  string  $extension
     *
     * @return self
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all log files.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getFiles('*' . $this->extension);
    }

    /**
     * Get all valid log files.
     *
     * @return array
     */
    public function logs(): array
    {
        if (!$this->logsCache) {
            $this->logsCache = $this->getFiles($this->getPattern());
        }
        return $this->logsCache;
    }

    /**
     * List the log files (Only dates).
     *
     * @param  bool  $extract
     *
     * @return array
     */
    public function paths($extract = false): array
    {
        $paths = array_reverse($this->logs());

        if ($extract) {
            $dict = [];
            foreach ($paths as $path) {
                $filename = basename($path);
                preg_match($this->regex, $filename, $matches);
                $prefix = $matches[1];
                $date = $matches[2];
                $dict[$prefix][$date] = $path;
            }

            return $dict; // [prefix => [date => file]]
        }

        return $paths;
    }

    /**
     * Read the log.
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return string|LazyCollection
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function read(string $prefix, string $date)
    {
        try {
            $path = $this->path($prefix, $date);
            $maxSize = config('log-viewer.chunked_size_threshold');
            $size = $this->filesystem->size($path);
            if ($size && $size > $maxSize) {
                return $this->filesystem->lines($path);
            }
            return $this->filesystem->get($path);
        } catch (\Exception $e) {
            throw new FilesystemException($e->getMessage());
        }
    }

    /**
     * Read the log.
     *
     * @param  string  $path
     *
     * @return string|LazyCollection
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function readPath(string $path)
    {
        try {
            $maxSize = config('log-viewer.chunked_size_threshold');
            $size = $this->filesystem->size($path);
            if ($size && $size > $maxSize) {
                return $this->filesystem->lines($path);
            }
            return $this->filesystem->get($path);
        } catch (\Exception $e) {
            throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete the log.
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return bool
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function delete(string $prefix, string $date)
    {
        $path = $this->path($prefix, $date);

        // @codeCoverageIgnoreStart
        if (!$this->filesystem->delete($path)) {
            throw new FilesystemException('There was an error deleting the log.');
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * Clear the log files.
     *
     * @return bool
     */
    public function clear()
    {
        return $this->filesystem->delete(
            $this->logs()
        );
    }

    /**
     * Get the log file path.
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return string
     */
    public function path(string $prefix, string $date)
    {
        $dict = $this->paths(true);

        if (!($path = ($dict[$prefix][$date] ?? null)) || !$this->filesystem->exists($path)) {
            throw new FilesystemException("The log(s) could not be located at: [$path] via [$prefix][$date]");
        }

        return realpath($path);
    }

    /**
     * Get files matching pattern.
     *
     * @param  string  $pattern
     *
     * @return array
     */
    public function getFiles(string $pattern): array
    {
        $files = $this->filesystem->glob(
            $this->storagePath . DIRECTORY_SEPARATOR . $pattern,
            GLOB_BRACE
        );

        $maxSize = config('log-viewer.max_log_size');
        return array_filter(array_map('realpath', $files), function ($file) use ($maxSize) {
            if (!$file) {
                return false;
            }
            $size = $this->filesystem->size($file);
            if ($size && $size > $maxSize) {
                return false;
            }
            return true;
        });
    }

    /**
     * Clears path caches
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->logsCache = null;
    }
}
