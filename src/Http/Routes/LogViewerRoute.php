<?php

namespace Arcanedev\LogViewer\Http\Routes;

use Arcanedev\LogViewer\Http\Controllers\LogViewerController;
use Arcanedev\Support\Routing\RouteRegistrar;

/**
 * Class     LogViewerRoute
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @codeCoverageIgnore
 */
class LogViewerRoute extends RouteRegistrar
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Map all routes.
     */
    public function map()
    {
        $attributes = (array) config('log-viewer.route.attributes');

        $this->group($attributes, function () {
            $this->name('log-viewer::')->group(function () {
                // log-viewer::dashboard
                $this->get('/', [LogViewerController::class, 'index'])->name('dashboard');

                // log-viewer::today
                $this->get('today', [LogViewerController::class, 'today'])->name('today');

                $this->mapLogsRoutes();
            });
        });
    }

    /**
     * Map the logs routes.
     */
    private function mapLogsRoutes()
    {
        $this->prefix('logs')->name('logs.')->group(function () {
            $this->get('/', [LogViewerController::class, 'listLogs'])
                ->name('list'); // log-viewer::logs.list

            $this->delete('delete', [LogViewerController::class, 'delete'])
                ->name('delete'); // log-viewer::logs.delete

            $this->prefix('{prefix}')->group(function () {
                $this->prefix('{date}')->group(function () {
                    $this->get('/', [LogViewerController::class, 'show'])
                        ->name('show'); // log-viewer::logs.show

                    $this->get('download', [LogViewerController::class, 'download'])
                        ->name('download'); // log-viewer::logs.download

                    $this->get('{level}', [LogViewerController::class, 'showByLevel'])
                        ->name('filter'); // log-viewer::logs.filter

                    $this->get('{level}/search', [LogViewerController::class, 'search'])
                        ->name('search'); // log-viewer::logs.search

                    $this->get('{level}/similar', [LogViewerController::class, 'showSimilar'])
                        ->name('similar'); // log-viewer::logs.similar
                });
            });
        });
    }
}
