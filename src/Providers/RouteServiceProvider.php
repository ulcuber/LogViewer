<?php

namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Http\Routes\LogViewerRoute;
use Arcanedev\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Class     RouteServiceProvider
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Check if routes is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config('enabled', false);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Define the routes for the application.
     */
    public function boot(): void
    {
        if ($this->isEnabled()) {
            $this->routes(function () {
                static::mapRouteClasses([LogViewerRoute::class]);
            });
        }
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get config value by key
     *
     * @param  string  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return $this->app['config']->get("log-viewer.route.$key", $default);
    }

    /**
     * Load the cached routes for the application.
     *
     * @return void
     */
    protected function loadCachedRoutes()
    {
        $this->app->booted(function () {
            if ($this->app['router']->getRoutes()->count() === 0) {
                require $this->app->getCachedRoutesPath();
            }
            // else routes cache probably already included
        });
    }
}
