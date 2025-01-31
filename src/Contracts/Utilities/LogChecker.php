<?php

namespace Arcanedev\LogViewer\Contracts\Utilities;

use Illuminate\Contracts\Config\Repository as ConfigContract;

/**
 * Interface  LogChecker
 *
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogChecker
{
    /* -----------------------------------------------------------------
     |  Constants
     | -----------------------------------------------------------------
     */

    /**
     * @link http://laravel.com/docs/5.4/errors#configuration
     * @link https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#log-to-files-and-syslog
     */
    public const HANDLER_DAILY = 'daily';

    public const HANDLER_SINGLE = 'single';

    public const HANDLER_SYSLOG = 'syslog';

    public const HANDLER_ERRORLOG = 'errorlog';

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the config instance.
     *
     *
     * @return self
     */
    public function setConfig(ConfigContract $config);

    /**
     * Set the Filesystem instance.
     *
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem);

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get messages.
     *
     * @return array
     */
    public function messages();

    /**
     * Check passes ??
     *
     * @return bool
     */
    public function passes();

    /**
     * Check fails ??
     *
     * @return bool
     */
    public function fails();

    /**
     * Get the requirements
     *
     * @return array
     */
    public function requirements();
}
