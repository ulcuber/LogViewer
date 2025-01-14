<?php

namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\Factory;

/**
 * Class     FactoryTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class FactoryTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LogViewer\Contracts\Utilities\Factory */
    private $logFactory;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->logFactory = $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\Factory::class);
    }

    protected function tearDown(): void
    {
        unset($this->logFactory);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(Factory::class, $this->logFactory);
    }

    /** @test */
    public function it_can_get_filesystem_object()
    {
        $expectations = [
            \Arcanedev\LogViewer\Contracts\Utilities\Filesystem::class,
            \Arcanedev\LogViewer\Utilities\Filesystem::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->logFactory->getFilesystem());
        }
    }

    /** @test */
    public function it_can_get_levels_object()
    {
        $expectations = [
            \Arcanedev\LogViewer\Contracts\Utilities\LogLevels::class,
            \Arcanedev\LogViewer\Utilities\LogLevels::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->logFactory->getLevels());
        }
    }

    /** @test */
    public function it_can_get_log_entries()
    {
        $prefix = 'laravel';
        $date = '2015-01-01';
        $logEntries = $this->logFactory->entries($prefix, $date);

        foreach ($logEntries as $logEntry) {
            static::assertLogEntry($date, $logEntry);
        }
    }

    /** @test */
    public function it_can_get_paths()
    {
        $paths = $this->logFactory->paths();

        static::assertCount(3, $paths);
        foreach ($paths as $path) {
            static::assertFileExists($path);
        }
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $logs = $this->logFactory->all();

        static::assertInstanceOf(\Arcanedev\LogViewer\Entities\LogCollection::class, $logs);
        static::assertCount(3, $logs);
        static::assertSame(3, $logs->count());
    }

    /** @test */
    public function it_can_paginate_all_logs()
    {
        $logs = $this->logFactory->paginate();

        static::assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $logs);
        static::assertSame(30, $logs->perPage());
        static::assertSame(3, $logs->total());
        static::assertSame(1, $logs->lastPage());
        static::assertSame(1, $logs->currentPage());
    }

    /** @test */
    public function it_can_get_count()
    {
        static::assertSame(3, $this->logFactory->count());
    }

    /** @test */
    public function it_can_can_set_custom_path()
    {
        $prefix = 'laravel';
        $date = '2015-01-03';

        $this->logFactory->setPath(static::fixturePath('custom-path-logs'));

        static::assertSame(1, $this->logFactory->count());

        $logEntries = $this->logFactory->entries($prefix, $date);

        foreach ($logEntries as $logEntry) {
            static::assertLogEntry($date, $logEntry);
        }
    }

    /** @test */
    public function it_can_get_total()
    {
        static::assertSame(24, $this->logFactory->total());
    }

    /** @test */
    public function it_can_get_total_by_level()
    {
        foreach (self::$logLevels as $level) {
            static::assertSame(3, $this->logFactory->total($level));
        }
    }

    /** @test */
    public function it_can_get_tree()
    {
        $tree = $this->logFactory->tree();

        foreach ($tree as $prefix => $dates) {
            static::assertSame('laravel', $prefix);
            foreach ($dates as $date => $levels) {
                static::assertDate($date);

                // TODO: Complete the assertions
            }
        }
    }

    /** @test */
    public function it_can_get_translated_tree()
    {
        $this->app->setLocale('fr');

        $expected = [
            'laravel' => [
                '2015-01-03' => [
                    'all' => ['name' => 'Tous', 'count' => 8],
                    'emergency' => ['name' => 'Urgence', 'count' => 1],
                    'alert' => ['name' => 'Alerte', 'count' => 1],
                    'critical' => ['name' => 'Critique', 'count' => 1],
                    'error' => ['name' => 'Erreur', 'count' => 1],
                    'warning' => ['name' => 'Avertissement', 'count' => 1],
                    'notice' => ['name' => 'Notice', 'count' => 1],
                    'info' => ['name' => 'Info', 'count' => 1],
                    'debug' => ['name' => 'Debug', 'count' => 1],
                ],
                '2015-01-02' => [
                    'all' => ['name' => 'Tous', 'count' => 8],
                    'emergency' => ['name' => 'Urgence', 'count' => 1],
                    'alert' => ['name' => 'Alerte', 'count' => 1],
                    'critical' => ['name' => 'Critique', 'count' => 1],
                    'error' => ['name' => 'Erreur', 'count' => 1],
                    'warning' => ['name' => 'Avertissement', 'count' => 1],
                    'notice' => ['name' => 'Notice', 'count' => 1],
                    'info' => ['name' => 'Info', 'count' => 1],
                    'debug' => ['name' => 'Debug', 'count' => 1],
                ],
                '2015-01-01' => [
                    'all' => ['name' => 'Tous', 'count' => 8],
                    'emergency' => ['name' => 'Urgence', 'count' => 1],
                    'alert' => ['name' => 'Alerte', 'count' => 1],
                    'critical' => ['name' => 'Critique', 'count' => 1],
                    'error' => ['name' => 'Erreur', 'count' => 1],
                    'warning' => ['name' => 'Avertissement', 'count' => 1],
                    'notice' => ['name' => 'Notice', 'count' => 1],
                    'info' => ['name' => 'Info', 'count' => 1],
                    'debug' => ['name' => 'Debug', 'count' => 1],
                ],
            ],
        ];

        static::assertSame($expected, $this->logFactory->tree(true));
    }

    /** @test */
    public function it_can_get_menu()
    {
        $menu = $this->logFactory->menu();

        foreach ($menu as $date => $item) {
            static::assertDate($date);

            // TODO: Complete the assertions
        }
    }

    /** @test */
    public function it_can_get_untranslated_menu()
    {
        $menu = $this->logFactory->menu(false);

        foreach ($menu as $date => $item) {
            static::assertDate($date);

            // TODO: Complete the assertions
        }
    }

    /** @test */
    public function it_can_check_is_not_empty()
    {
        static::assertFalse($this->logFactory->isEmpty());
    }

    /** @test */
    public function it_must_throw_a_filesystem_exception()
    {
        $this->expectException(\Arcanedev\LogViewer\Exceptions\LogNotFoundException::class);

        $this->logFactory->get('laravel', '2222-11-11'); // Future FTW
    }

    /** @test */
    public function it_can_set_and_get_pattern()
    {
        $prefix = 'laravel-';
        $date = '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]';
        $extension = '.log';

        static::assertSame(
            $prefix.$date.$extension,
            $this->logFactory->getPattern()
        );

        $this->logFactory->setPattern($prefix, $date, $extension = '');

        static::assertSame(
            $prefix.$date.$extension,
            $this->logFactory->getPattern()
        );

        $this->logFactory->setPattern($prefix = 'laravel-cli-', $date, $extension);

        static::assertSame(
            $prefix.$date.$extension,
            $this->logFactory->getPattern()
        );

        $this->logFactory->setPattern($prefix, $date = '[0-9][0-9][0-9][0-9]', $extension);

        static::assertSame(
            $prefix.$date.$extension,
            $this->logFactory->getPattern()
        );

        $this->logFactory->setPattern();

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->logFactory->getPattern()
        );

        $this->logFactory->setPattern(
            'laravel-',
            '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            '.log'
        );

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->logFactory->getPattern()
        );
    }
}
