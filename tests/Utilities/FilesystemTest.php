<?php

namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\Filesystem;

/**
 * Class     FilesystemTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class FilesystemTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Utilities\Filesystem */
    private $filesystem;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->filesystem();
    }

    protected function tearDown(): void
    {
        unset($this->filesystem);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(Filesystem::class, $this->filesystem);
    }

    /** @test */
    public function it_can_get_filesystem_instance()
    {
        static::assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->filesystem->getInstance()
        );
    }

    /** @test */
    public function it_can_get_all_valid_log_files()
    {
        static::assertCount(3, $this->filesystem->logs());
    }

    /** @test */
    public function it_can_get_all_custom_log_files()
    {
        $files = $this->filesystem
            ->setPrefixPattern('laravel-cli-')
            ->logs();

        static::assertCount(1, $files);
    }

    /** @test */
    public function it_can_get_all_log_files()
    {
        $files = $this->filesystem->all();

        static::assertCount(6, $files);

        foreach ($files as $file) {
            static::assertStringEndsWith('.log', $file);
        }
    }

    /** @test */
    public function it_can_read_file()
    {
        $prefix = 'laravel';
        $date = '2015-01-01';

        $file = $this->filesystem->read($prefix, $date);

        static::assertNotEmpty($file);
        static::assertStringStartsWith('[' . $date, $file);
    }

    /** @test */
    public function it_can_delete_file()
    {
        $prefix = 'laravel';

        static::createDummyLog(
            $date = date('Y-m-d'),
            $path = storage_path('logs')
        );

        $this->filesystem->setPath($path);

        // Assert log exists
        $file = $this->filesystem->read($prefix, $date);

        static::assertNotEmpty($file);

        // Assert log deletion
        try {
            $deleted = $this->filesystem->delete($prefix, $date);
            $message = '';
        } catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        static::assertTrue($deleted, $message);
    }

    /** @test */
    public function it_can_get_files()
    {
        $files = $this->filesystem->logs();

        static::assertCount(3, $files);

        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }

    /** @test */
    public function it_can_set_a_custom_path()
    {
        $this->filesystem->setPath(static::fixturePath('custom-path-logs'));

        $files = $this->filesystem->logs();

        static::assertCount(1, $files);

        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }


    /** @test */
    public function it_can_get_file_path_by_prefix_and_date()
    {
        static::assertFileExists(
            $this->filesystem->path('laravel', '2015-01-01')
        );
    }

    /** @test */
    public function it_can_get_paths_from_log_files()
    {
        foreach ($this->filesystem->paths() as $path) {
            $this->assertFileExists($path);
        }
    }

    /** @test */
    public function it_can_get_paths_with_prefixes_and_dates_from_log_files()
    {
        foreach ($this->filesystem->paths(true) as $prefix => $dates) {
            $this->assertSame('laravel', $prefix);
            foreach ($dates as $date => $path) {
                static::assertDate($date);
                static::assertFileExists($path);
            }
        }
    }

    /** @test */
    public function it_must_throw_a_filesystem_exception_on_read()
    {
        $this->expectException(\Arcanedev\LogViewer\Exceptions\FilesystemException::class);

        $this->filesystem->read('laravel', '2222-11-11'); // Future FTW
    }

    /** @test */
    public function it_must_throw_a_filesystem_exception_on_delete()
    {
        $this->expectException(\Arcanedev\LogViewer\Exceptions\FilesystemException::class);

        $this->filesystem->delete('laravel', '2222-11-11'); // Future FTW
    }

    /** @test */
    public function it_can_set_and_get_pattern()
    {
        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setExtension('');

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPrefixPattern('laravel-cli-');

        static::assertSame(
            'laravel-cli-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setDatePattern('[0-9][0-9][0-9][0-9]');

        static::assertSame(
            'laravel-cli-[0-9][0-9][0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern();

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern(
            'laravel-',
            '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            '.log'
        );

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );
    }
}
