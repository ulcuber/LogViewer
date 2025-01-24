<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer;

use Arcanedev\Support\Providers\PackageServiceProvider;

/**
 * Class     LogViewerServiceProvider
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerServiceProvider extends PackageServiceProvider
{
    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'log-viewer';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();

        $this->registerConfig();

        $this->registerProvider(Providers\RouteServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->registerCommands([
                Commands\PublishCommand::class,
                Commands\StatsCommand::class,
                Commands\CheckCommand::class,
                Commands\ClearCommand::class,
                Commands\LatestCommand::class,
            ]);
        }
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->loadTranslations();
        $this->loadViews();

        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishTranslations();
            $this->publishViews();
        }
    }

    /**
     * Publish the translations.
     * Uses old tag name.
     */
    protected function publishTranslations(?string $path = null): void
    {
        $this->publishes([
            $this->getTranslationsPath() => $path ?: $this->getTranslationsDestinationPath(),
        ], $this->getPublishedTags('lang'));
    }

    /**
     * Get the translations path.
     * Uses old translations path.
     */
    protected function getTranslationsPath(): string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
    }

    /**
     * Get the base views path.
     * Uses old views path.
     */
    protected function getViewsPath(): string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }
}
