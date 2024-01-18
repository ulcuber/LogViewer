<?php

namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Entities\Log;

/**
 * Class     LatestCommand
 *
 * @author   ulcuber
 */
class LatestCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'log-viewer:latest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display latest entry in log.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:latest';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = date('Y-m-d');

        $filesystem = $this->filesystem();
        $filesystem->setPattern(FilesystemContract::PATTERN_PREFIX, $date);
        $paths = $filesystem->logs();

        if (empty($paths)) {
            $this->warn("No file for [{$date}]");

            return 1;
        }

        $path = array_pop($paths);
        $log = Log::make('laravel', $date, $path, $filesystem->readPath($path));

        if ($entry = $log->entries()->first()) {
            $stack = implode(PHP_EOL, array_reverse(preg_split("/\r\n|\n|\r/", $entry->stack)));
            $this->line($stack);

            $this->line($entry->header);

            $row = [];
            foreach ($entry->extra as $propName => $extra) {
                $row[$propName] = $extra;
            }
            $row['Env'] = $entry->env;
            $row['Level'] = $entry->level;
            $this->table(array_keys($row), [$row]);
        }
    }

    protected function filesystem(): FilesystemContract
    {
        return $this->laravel[FilesystemContract::class];
    }
}
