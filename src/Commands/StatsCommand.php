<?php

namespace Arcanedev\LogViewer\Commands;

/**
 * Class     StatsCommand
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class StatsCommand extends Command
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'log-viewer:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display stats of all logs.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:stats';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stats = $this->logViewer->statsTable('en');

        $rows = [];
        foreach ($stats->rows() as $prefix => $dates) {
            foreach ($dates as $date => $value) {
                $rows[] = array_merge(compact('prefix', 'date'), $value);
            }
        }
        $total = count($rows);

        $rows[] = $this->tableSeparator();
        $rows[] = array_merge([
            'Total',
            $total,
        ], $stats->footer());

        // Display Data
        $this->displayLogViewer();
        $this->table($stats->header(), $rows);
    }
}
