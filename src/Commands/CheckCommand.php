<?php

namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Contracts\Utilities\LogChecker as LogCheckerContract;

/**
 * Class     PublishCommand
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CheckCommand extends Command
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
    protected $name = 'log-viewer:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all LogViewer requirements.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:check';

    /* -----------------------------------------------------------------
     |  Getter & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the Log Checker instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\Utilities\LogChecker
     */
    protected function getChecker()
    {
        return $this->laravel[LogCheckerContract::class];
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayLogViewer();
        $this->displayRequirements();
        $this->displayMessages();
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Display LogViewer requirements.
     */
    private function displayRequirements()
    {
        $requirements = $this->getChecker()->requirements();

        $this->frame('Application requirements');

        $this->table([
            'Status', 'Message',
        ], [
            [$requirements['status'], $requirements['message']],
        ]);
    }

    /**
     * Display LogViewer messages.
     */
    private function displayMessages()
    {
        $checker = $this->getChecker();
        $messages = $checker->messages();
        $pattern = $checker->pattern;

        $rows = [];
        foreach ($messages['files'] as $file => $message) {
            $rows[] = [$file, $message];
        }

        if (! empty($rows)) {
            $this->frame('LogViewer messages');
            $this->table(['File', 'Message'], $rows);
            $this->info('The format must match glob pattern');
            $this->output->writeln("<info>Glob pattern</info>: <comment>{$pattern}</comment>");
            $this->output->writeln('<info>Configured in</info>: <comment>config/log-viewer.php</comment> pattern');
        }
    }
}
