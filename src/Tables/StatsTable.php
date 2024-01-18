<?php

namespace Arcanedev\LogViewer\Tables;

use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class     StatsTable
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class StatsTable extends AbstractTable
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make a stats table instance.
     *
     * @param  string|null  $locale
     * @return self
     */
    public static function make(array $data, LogLevelsContract $levels, $locale = null)
    {
        return new self($data, $levels, $locale);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Prepare table header.
     */
    protected function prepareHeader(array $data): array
    {
        return array_merge_recursive(
            [
                'prefix' => $this->translate('general.prefix'),
                'date' => $this->translate('general.date'),
                'all' => $this->translate('general.all'),
            ],
            $this->levels->names($this->locale)
        );
    }

    /**
     * Prepare table rows.
     */
    protected function prepareRows(array $data): array
    {
        $rows = [];

        foreach ($data as $prefix => $dates) {
            foreach ($dates as $date => $levels) {
                $rows[$prefix][$date] = array_merge(compact('prefix', 'date'), $levels);
            }
        }

        return $rows;
    }

    /**
     * Prepare table footer.
     */
    protected function prepareFooter(array $data): array
    {
        $footer = [];

        foreach ($data as $dates) {
            foreach ($dates as $levels) {
                foreach ($levels as $level => $count) {
                    if (! isset($footer[$level])) {
                        $footer[$level] = 0;
                    }

                    $footer[$level] += $count;
                }
            }
        }

        return $footer;
    }

    /**
     * Get totals.
     *
     * @param  string|null  $locale
     */
    public function totals(): Collection
    {
        $totals = Collection::make();

        foreach (Arr::except($this->footer(), 'all') as $level => $count) {
            $totals->put($level, [
                'label' => $this->translate("levels.$level"),
                'value' => $count,
                'color' => $this->color($level),
                'highlight' => $this->color($level),
            ]);
        }

        return $totals;
    }

    /**
     * Get json totals data.
     */
    public function totalsJson(?string $locale = null): string
    {
        return $this->totals($locale)->toJson(JSON_PRETTY_PRINT);
    }
}
