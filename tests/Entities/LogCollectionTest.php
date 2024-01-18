<?php

namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Exceptions\FilesystemException;
use Arcanedev\LogViewer\Exceptions\LogNotFoundException;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogCollectionTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogCollectionTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LogViewer\Entities\LogCollection */
    private $logs;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->logs = new LogCollection();
    }

    protected function tearDown(): void
    {
        unset($this->logs);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogCollection::class, $this->logs);
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        static::assertCount(3, $this->logs);
        static::assertSame(3, $this->logs->count());
        static::assertSame(24, $this->logs->total());

        foreach ($this->logs as $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            static::assertLog($log);
            static::assertCount(8, $log->entries());
            static::assertSame(8, $log->entries()->count());
        }
    }

    /** @test */
    public function it_can_get_a_log_by_prefix_and_date()
    {
        $log = $this->logs->log('laravel', '2015-01-01');

        static::assertLog($log);
        static::assertCount(8, $log->entries());
        static::assertSame(8, $log->entries()->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_prefix_and_date()
    {
        $date = '2015-01-01';
        $entries = $this->logs->entries('laravel', $date);

        static::assertLogEntries($date, $entries);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_prefix_and_date_and_level()
    {
        $prefix = 'laravel';
        $date = '2015-01-01';

        foreach (self::$logLevels as $level) {
            $entries = $this->logs->entries($prefix, $date, $level);

            static::assertLogEntries($date, $entries);
            static::assertCount(1, $entries);
            static::assertSame(1, $entries->count());
        }

        $entries = $this->logs->entries($prefix, $date, 'all');

        static::assertLogEntries($date, $entries);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
    }

    /** @test */
    public function it_can_get_logs_paths()
    {
        foreach ($this->getPaths() as $path) {
            static::assertContains($path, $this->logs->paths());
        }
    }

    /** @test */
    public function it_can_get_logs_stats()
    {
        $stats = $this->logs->stats();

        foreach ($stats as $dates) {
            foreach ($dates as $date => $counters) {
                static::assertDate($date);

                foreach ($counters as $level => $counter) {
                    if ($level === 'all') {
                        static::assertSame(8, $counter);
                    } else {
                        static::assertEquals(1, $counter);
                    }
                }
            }
        }
    }

    /** @test */
    public function it_can_get_log_tree()
    {
        $tree = $this->logs->tree();

        static::assertCount(1, $tree);

        foreach ($tree as $dates) {
            static::assertCount(3, $dates);
            foreach ($dates as $date => $levels) {
                static::assertDate($date);

                foreach ($levels as $level => $item) {
                    static::assertEquals($level, $item['name']);
                    static::assertSame($level === 'all' ? 8 : 1, $item['count']);
                }
            }
        }
    }

    /** @test */
    public function it_can_get_log_menu()
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);
            $menu = $this->logs->menu();

            foreach ($menu as $date => $levels) {
                static::assertDate($date);

                foreach ($levels as $level => $item) {
                    static::assertNotEquals($level, $item['name']);
                    static::assertTranslatedLevel($locale, $level, $item['name']);
                    static::assertSame($level == 'all' ? 8 : 1, $item['count']);
                }
            }
        }
    }

    /** @test */
    public function it_must_throw_a_log_not_found_on_get_method()
    {
        $this->expectException(LogNotFoundException::class);
        $this->expectExceptionMessage('Log not found in this path [laravel-2222-01-01.log]');

        $this->logs->get('laravel-2222-01-01.log');
    }

    /** @test */
    public function it_must_throw_a_log_not_found_on_log_method()
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('The log(s) could not be located at: [] via [laravel][2222-01-01]');

        $this->logs->log('laravel', '2222-01-01');
    }
}
