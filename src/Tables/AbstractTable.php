<?php

namespace Arcanedev\LogViewer\Tables;

use Arcanedev\LogViewer\Contracts\Table as TableContract;
use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;

/**
 * Class     AbstractTable
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class AbstractTable implements TableContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var array */
    private $header = [];

    /** @var array */
    private $rows = [];

    /** @var array */
    private $footer = [];

    /** @var \Arcanedev\LogViewer\Contracts\Utilities\LogLevels */
    protected $levels;

    /** @var string|null */
    protected $locale;

    /** @var array */
    private $data = [];

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a table instance.
     *
     * @param  string|null  $locale
     */
    public function __construct(array $data, LogLevelsContract $levels, $locale = null)
    {
        $this->setLevels($levels);
        $this->setLocale(is_null($locale) ? config('log-viewer.locale') : $locale);
        $this->setData($data);
        $this->init();
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set LogLevels instance.
     *
     *
     * @return self
     */
    protected function setLevels(LogLevelsContract $levels)
    {
        $this->levels = $levels;

        return $this;
    }

    /**
     * Set table locale.
     *
     *
     * @return self
     */
    protected function setLocale(?string $locale)
    {
        if (is_null($locale) || $locale === 'auto') {
            $locale = app()->getLocale();
        }

        $this->locale = $locale;

        return $this;
    }

    /**
     * Get table header.
     */
    public function header(): array
    {
        return $this->header;
    }

    /**
     * Get table rows.
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * Get table footer.
     */
    public function footer(): array
    {
        return $this->footer;
    }

    /**
     * Get raw data.
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Set table data.
     *
     *
     * @return self
     */
    private function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Prepare the table.
     */
    protected function init()
    {
        $this->header = $this->prepareHeader($this->data);
        $this->rows = $this->prepareRows($this->data);
        $this->footer = $this->prepareFooter($this->data);
    }

    /**
     * Prepare table header.
     */
    abstract protected function prepareHeader(array $data): array;

    /**
     * Prepare table rows.
     */
    abstract protected function prepareRows(array $data): array;

    /**
     * Prepare table footer.
     */
    abstract protected function prepareFooter(array $data): array;

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Translate.
     */
    protected function translate(string $key): string
    {
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = trans();

        return $translator->get('log-viewer::'.$key, [], $this->locale);
    }

    /**
     * Get log level color.
     */
    protected function color(string $level): string
    {
        return log_styler()->color($level);
    }
}
