<?php

namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\LogMenu;

/**
 * Class     LogMenuTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogMenuTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LogViewer\Utilities\LogMenu */
    private $menu;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->menu = $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\LogMenu::class);
    }

    protected function tearDown(): void
    {
        unset($this->menu);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogMenu::class, $this->menu);
    }

    /** @test */
    public function it_can_make_menu_with_helper()
    {
        $log = $this->getLog('laravel', '2015-01-01');

        $expected = [
            'all' => [
                'name' => 'All',
                'count' => 8,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/all',
                'icon' => '<i class="bi bi-list"></i>',
            ],
            'emergency' => [
                'name' => 'Emergency',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/emergency',
                'icon' => '<i class="bi bi-bug"></i>',
            ],
            'alert' => [
                'name' => 'Alert',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/alert',
                'icon' => '<i class="bi bi-bullhorn"></i>',
            ],
            'critical' => [
                'name' => 'Critical',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/critical',
                'icon' => '<i class="bi bi-heartbeat"></i>',
            ],
            'error' => [
                'name' => 'Error',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/error',
                'icon' => '<i class="bi bi-times-circle"></i>',
            ],
            'warning' => [
                'name' => 'Warning',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/warning',
                'icon' => '<i class="bi bi-exclamation-triangle"></i>',
            ],
            'notice' => [
                'name' => 'Notice',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/notice',
                'icon' => '<i class="bi bi-exclamation-circle"></i>',
            ],
            'info' => [
                'name' => 'Info',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/info',
                'icon' => '<i class="bi bi-info-circle"></i>',
            ],
            'debug' => [
                'name' => 'Debug',
                'count' => 1,
                'url' => 'http://localhost/log-viewer/logs/laravel/2015-01-01/debug',
                'icon' => '<i class="bi bi-life-ring"></i>',
            ],
        ];

        static::assertSame($expected, log_menu()->make($log));
    }
}
