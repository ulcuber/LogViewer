<?php

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class     TestCase
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var array */
    protected static $logLevels = [];

    /** @var array */
    protected static $locales = [
        'ar', 'bg', 'de', 'en', 'es', 'et', 'fa', 'fr', 'hu', 'hy', 'id', 'it', 'ja', 'ko', 'ms', 'nl', 'pl',
        'pt-BR', 'ro', 'ru', 'sv', 'th', 'tr', 'uk', 'zh-TW', 'zh',
    ];

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$logLevels = self::getLogLevels();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$logLevels = [];
    }

    protected static function fixturePath(?string $path = null): string
    {
        return is_null($path)
            ? __DIR__.'/fixtures'
            : __DIR__.'/fixtures/'.$path;
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Arcanedev\LogViewer\LogViewerServiceProvider::class,
            \Arcanedev\LogViewer\Providers\DeferredServicesProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.storage'] = realpath(__DIR__.'/fixtures');

        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        $config->set('log-viewer.storage-path', $app['path.storage'].DIRECTORY_SEPARATOR.'logs');
    }

    /* -----------------------------------------------------------------
     |  Custom assertions
     | -----------------------------------------------------------------
     */

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param  \Illuminate\Contracts\Support\Jsonable|mixed  $object
     * @param  string  $message
     */
    public static function assertJsonObject($object, $message = '')
    {
        self::assertInstanceOf(\Illuminate\Contracts\Support\Jsonable::class, $object);
        self::assertJson($object->toJson(JSON_PRETTY_PRINT), $message);

        self::assertInstanceOf('JsonSerializable', $object);
        self::assertJson(json_encode($object, JSON_PRETTY_PRINT), $message);
    }

    /**
     * Assert Log object.
     */
    protected static function assertLog(Log $log)
    {
        self::assertLogEntries($log->date, $log->entries());
    }

    /**
     * Assert Log entries object.
     *
     * @param  string  $date
     */
    protected static function assertLogEntries($date, LogEntryCollection $entries)
    {
        foreach ($entries as $entry) {
            self::assertLogEntry($date, $entry);
        }
    }

    /**
     * Assert log entry object.
     *
     * @param  string  $date
     */
    protected static function assertLogEntry($date, LogEntry $entry)
    {
        self::assertInLogLevels($entry->level);

        $dt = Carbon::createFromFormat('Y-m-d', $date);
        $datetime = $entry->getDatetime();
        self::assertInstanceOf(Carbon::class, $datetime);
        self::assertTrue($datetime->isSameDay($dt), "{$datetime} is not {$date} ({$dt})");

        self::assertNotEmpty($entry->header);
        self::assertNotEmpty($entry->stack);
    }

    /**
     * Assert in log levels.
     *
     * @param  string  $level
     * @param  string  $message
     */
    protected static function assertInLogLevels($level, $message = '')
    {
        self::assertContains($level, self::$logLevels, $message);
    }

    /**
     * Assert levels.
     */
    protected static function assertLevels(array $levels)
    {
        self::assertCount(8, $levels);

        foreach (static::getLogLevels() as $key => $value) {
            self::assertArrayHasKey($key, $levels);
            self::assertEquals($value, $levels[$key]);
        }
    }

    /**
     * Assert translated level.
     *
     * @param  string  $locale
     * @param  array  $levels
     */
    protected function assertTranslatedLevels($locale, $levels)
    {
        foreach ($levels as $level => $translatedLevel) {
            self::assertTranslatedLevel($locale, $level, $translatedLevel);
        }
    }

    /**
     * Assert translated level.
     *
     * @param  string  $locale
     * @param  string  $level
     * @param  string  $actualTrans
     */
    protected static function assertTranslatedLevel($locale, $level, $actualTrans)
    {
        $expected = static::getTranslatedLevel($locale, $level);

        self::assertEquals($expected, $actualTrans);
    }

    /**
     * Assert dates.
     *
     * @param  string  $message
     */
    public static function assertDates(array $dates, $message = '')
    {
        foreach ($dates as $date) {
            self::assertDate($date, $message);
        }
    }

    /**
     * Assert date [YYYY-MM-DD].
     *
     * @param  string  $date
     * @param  string  $message
     */
    public static function assertDate($date, $message = '')
    {
        self::assertMatchesRegularExpression('/'.REGEX_DATE_PATTERN.'/', $date, $message);
    }

    /**
     * Assert Menu item.
     *
     * @param  array  $item
     * @param  string  $name
     * @param  int  $count
     * @param  bool  $withIcons
     */
    protected static function assertMenuItem($item, $name, $count, $withIcons = true)
    {
        self::assertArrayHasKey('name', $item);
        self::assertEquals($name, $item['name']);
        self::assertArrayHasKey('count', $item);
        self::assertEquals($count, $item['count']);

        if ($withIcons) {
            self::assertArrayHasKey('icon', $item);
            self::assertStringStartsWith('bi bi-', $item['icon']);
        } else {
            self::assertArrayNotHasKey('icon', $item);
        }
    }

    /**
     * Assert HEX Color.
     *
     * @param  string  $color
     * @param  string  $message
     */
    protected static function assertHexColor($color, $message = '')
    {
        $pattern = '/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i';

        self::assertMatchesRegularExpression($pattern, $color, $message);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get Illuminate Filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function illuminateFile()
    {
        return $this->app->make('files');
    }

    /**
     * Get Filesystem Utility instance.
     *
     * @return \Arcanedev\LogViewer\Utilities\Filesystem
     */
    protected function filesystem()
    {
        return $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\Filesystem::class);
    }

    /**
     * Get Translator Repository.
     *
     * @return \Illuminate\Translation\Translator
     */
    protected static function trans()
    {
        return app('translator');
    }

    /**
     * Get Config Repository.
     *
     * @return \Illuminate\Config\Repository
     */
    protected function config()
    {
        return $this->app->make('config');
    }

    /**
     * Get log path.
     *
     *
     * @return string
     */
    public function getLogPath(string $prefix, string $date)
    {
        return $this->filesystem()->path($prefix, $date);
    }

    /**
     * Get log content.
     *
     * @param  string  $path
     * @return string
     */
    public function getLogContent($path)
    {
        return $this->filesystem()->readPath($path);
    }

    /**
     * Get logs paths.
     *
     * @return array
     */
    public function getPaths(bool $extract = false)
    {
        return $this->filesystem()->paths($extract);
    }

    /**
     * Get log object from fixture.
     *
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    protected function getLog(string $prefix, string $date)
    {
        $path = $this->getLogPath($prefix, $date);
        $raw = $this->getLogContent($path);

        return Log::make($prefix, $date, $path, $raw);
    }

    /**
     * Get random entry from a log file.
     *
     * @param  string  $date
     * @return mixed
     */
    protected function getRandomLogEntry($date)
    {
        return $this->getLog('laravel', $date)
            ->entries()
            ->random(1)
            ->first();
    }

    /**
     * Get log levels.
     *
     * @return array
     */
    protected static function getLogLevels()
    {
        return self::$logLevels = (new ReflectionClass(LogLevel::class))
            ->getConstants();
    }

    /**
     * Create dummy log.
     */
    protected static function createDummyLog(string $date, string $path = 'logs'): bool
    {
        return copy(
            static::fixturePath('dummy.log'), // Source
            "{$path}/laravel-{$date}.log"     // Destination
        );
    }

    /**
     * Get translated level.
     *
     * @param  string  $locale
     * @param  string  $key
     * @return mixed
     */
    private static function getTranslatedLevel($locale, $key)
    {
        return Arr::get(static::getTranslatedLevels(), "$locale.$key");
    }

    /**
     * Get translated levels.
     *
     * @return array
     */
    protected static function getTranslatedLevels()
    {
        return array_map(function ($locale) {
            return static::trans()->get('log-viewer::levels', [], $locale);
        }, array_combine(self::$locales, self::$locales));
    }

    /**
     * Get config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return realpath(config_path());
    }
}
