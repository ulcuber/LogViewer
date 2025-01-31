<?php

namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     ClearCommandTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class ClearCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LogViewer\LogViewer */
    private $logViewer;

    /** @var string */
    private $path;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->logViewer = $this->app->make(\Arcanedev\LogViewer\Contracts\LogViewer::class);
        $this->path = storage_path('logs-to-clear');

        $this->setupForTests();
    }

    protected function tearDown(): void
    {
        rmdir($this->path);
        unset($this->path);
        unset($this->logViewer);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
    |  Tests
    | -----------------------------------------------------------------
    */

    /** @test */
    public function it_can_delete_all_log_files()
    {
        static::assertEquals(0, $this->logViewer->count());

        static::createDummyLog(date('Y-m-d'), storage_path('logs-to-clear'));

        $this->logViewer->clearCache();
        static::assertEquals(1, $this->logViewer->count());

        $this->artisan('log-viewer:clear')
            ->expectsQuestion('This will delete all the log files, Do you wish to continue?', 'yes')
            ->expectsOutput('Successfully cleared the logs!')
            ->assertExitCode(0);

        $this->logViewer->clearCache();
        static::assertEquals(0, $this->logViewer->count());
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Sets the log storage path temporarily to a new directory
     */
    private function setupForTests()
    {
        if (file_exists($this->path)) {
            static::rmDirRecursive($this->path);
        }

        mkdir($this->path, 0777, true);

        $this->logViewer->setPath($this->path);
        $this->app['config']->set(['log-viewer.storage-path' => $this->path]);
    }

    public static function rmDirRecursive(string $dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::rmDirRecursive("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
