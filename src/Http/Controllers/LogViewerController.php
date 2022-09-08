<?php

namespace Arcanedev\LogViewer\Http\Controllers;

use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Exceptions\LogNotFoundException;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class     LogViewerController
 *
 * @package  LogViewer\Http\Controllers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerController extends Controller
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The log viewer instance
     *
     * @var \Arcanedev\LogViewer\Contracts\LogViewer
     */
    protected $logViewer;

    /** @var int */
    protected $perPage = 30;

    /** @var string */
    protected $showRoute = 'log-viewer::logs.show';

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * LogViewerController constructor.
     *
     * @param  \Arcanedev\LogViewer\Contracts\LogViewer  $logViewer
     */
    public function __construct(LogViewerContract $logViewer)
    {
        $this->logViewer = $logViewer;
        $this->perPage = config('log-viewer.per-page', $this->perPage);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats     = $this->logViewer->statsTable();
        $chartData = $this->prepareChartData($stats);
        $percents  = $this->calcPercentages($stats->footer(), $stats->header());

        return $this->view('dashboard', compact('chartData', 'percents'));
    }

    /**
     * List all logs.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\View\View
     */
    public function listLogs(Request $request)
    {
        $stats   = $this->logViewer->statsTable();
        $headers = $stats->header();
        $rows    = $this->paginate($stats->rows(), $request);

        return $this->view('logs', compact('headers', 'rows'));
    }

    /**
     * Show the log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $prefix
     * @param  string                    $date
     *
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $prefix, string $date)
    {
        if ($order = $request->order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        $filters = array_merge($request->except('page'), $request->route()->parameters());

        $level   = 'all';
        $log     = $this->getLogOrFail($prefix, $date);
        $query   = $request->get('query');
        $levels  = $this->logViewer->levelsNames();

        $extras = $request->only(config('log-viewer.parser.extra_groups'));
        $uuidKeys = ['uuid', 'parentUuid'];
        $uuids = Arr::only($extras, $uuidKeys);
        $extras = Arr::except($extras, $uuidKeys);

        $entries = $log->entries($level)
            ->unless(empty($uuids), function (LogEntryCollection $entries) use ($uuids, $uuidKeys) {
                return $entries->filter(function (LogEntry $entry) use ($uuids, $uuidKeys) {
                    foreach ($uuidKeys as $key) {
                        foreach ($uuids as $uuid) {
                            if ($entry->{$key} === $uuid) {
                                return true;
                            }
                        }
                    }
                    return false;
                });
            })
            ->unless(empty($extras), function (LogEntryCollection $entries) use ($extras) {
                return $entries->filter(function (LogEntry $entry) use ($extras) {
                    foreach ($extras as $key => $extra) {
                        if ($entry->{$key} === $extra) {
                            return true;
                        }
                    }
                    return false;
                });
            })
            ->paginate($this->perPage);

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries', 'filters'));
    }

    /**
     * Show similar entries.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $prefix
     * @param  string                    $date
     * @param  string                    $level
     *
     * @return \Illuminate\View\View
     */
    public function showSimilar(Request $request, string $prefix, string $date, string $level)
    {
        if ($order = $request->order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        $filters = array_merge($request->except('page'), $request->route()->parameters());

        $text = $request->get('text', '');

        $log     = $this->getLogOrFail($prefix, $date);
        $query   = $request->get('query');
        $levels  = $this->logViewer->levelsNames();
        $entries = $log->getSimilar($text, 76)->paginate($this->perPage);

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries', 'filters'));
    }

    /**
     * Filter the log entries by level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $prefix
     * @param  string                    $date
     * @param  string                    $level
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showByLevel(Request $request, string $prefix, string $date, string $level)
    {
        if ($order = $request->order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        if ($level === 'all') {
            return redirect()->route($this->showRoute, [$prefix, $date]);
        }

        $filters = array_merge($request->except('page'), $request->route()->parameters());

        $log     = $this->getLogOrFail($prefix, $date);
        $query   = $request->get('query');
        $levels  = $this->logViewer->levelsNames();
        $entries = $log->entries($level)->paginate($this->perPage);

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries', 'filters'));
    }

    /**
     * Show the log with the search query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $prefix
     * @param  string                    $date
     * @param  string                    $level
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function search(Request $request, string $prefix, string $date, string $level = 'all')
    {
        if ($order = $request->order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        $query = $request->get('query');

        if (is_null($query)) {
            return redirect()->route($this->showRoute, [$prefix, $date]);
        }

        $filters = array_merge($request->except('page'), $request->route()->parameters());

        $log     = $this->getLogOrFail($prefix, $date);
        $levels  = $this->logViewer->levelsNames();
        $needles = array_map(function ($needle) {
            return Str::lower($needle);
        }, array_filter(explode(' ', $query)));
        $entries = $log->entries($level)
            ->unless(empty($needles), function (LogEntryCollection $entries) use ($needles) {
                return $entries->filter(function (LogEntry $entry) use ($needles) {
                    return Str::containsAll(Str::lower($entry->header), $needles);
                });
            })
            ->paginate($this->perPage);

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries', 'filters'));
    }

    /**
     * Download the log
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $prefix, string $date)
    {
        return $this->logViewer->download($prefix, $date);
    }

    /**
     * Delete a log.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        abort_unless($request->ajax(), 405, 'Method Not Allowed');

        $validator = validator($request->all(), [
            'prefix' => 'required|string',
            'date' => 'required|string',
        ]);
        $validator->validate();
        $validated = $validator->validated();

        $prefix = $validated['prefix'];
        $date = $validated['date'];

        return response()->json([
            'result' => $this->logViewer->delete($prefix, $date) ? 'success' : 'error'
        ]);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     *
     * @return \Illuminate\View\View
     */
    protected function view($view, $data = [], $mergeData = [])
    {
        $theme = config('log-viewer.theme');

        $data['route'] = app('router')->currentRouteName();

        return view()->make("log-viewer::{$theme}.{$view}", $data, $mergeData);
    }

    /**
     * Paginate logs.
     *
     * @param  array                     $data
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginate(array $data, Request $request)
    {
        $data = new Collection($data);
        $page = $request->get('page', 1);
        $path = $request->url();

        return new LengthAwarePaginator(
            $data->forPage($page, $this->perPage),
            $data->count(),
            $this->perPage,
            $page,
            compact('path')
        );
    }

    /**
     * Get a log or fail
     *
     * @param  string  $prefix
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log|null
     */
    protected function getLogOrFail(string $prefix, string $date)
    {
        $log = null;

        try {
            $log = $this->logViewer->get($prefix, $date);
        } catch (LogNotFoundException $e) {
            abort(404, $e->getMessage());
        }

        return $log;
    }

    /**
     * Prepare chart data.
     *
     * @param  \Arcanedev\LogViewer\Tables\StatsTable  $stats
     *
     * @return string
     */
    protected function prepareChartData(StatsTable $stats)
    {
        $totals = $stats->totals()->all();

        return json_encode([
            'labels'   => Arr::pluck($totals, 'label'),
            'datasets' => [
                [
                    'data'                 => Arr::pluck($totals, 'value'),
                    'backgroundColor'      => Arr::pluck($totals, 'color'),
                    'hoverBackgroundColor' => Arr::pluck($totals, 'highlight'),
                ],
            ],
        ]);
    }

    /**
     * Calculate the percentage.
     *
     * @param  array  $total
     * @param  array  $names
     *
     * @return array
     */
    protected function calcPercentages(array $total, array $names)
    {
        $percents = [];
        $all      = Arr::get($total, 'all');

        foreach ($total as $level => $count) {
            $percents[$level] = [
                'name'    => $names[$level],
                'count'   => $count,
                'percent' => $all ? round(($count / $all) * 100, 2) : 0,
            ];
        }

        return $percents;
    }
}
