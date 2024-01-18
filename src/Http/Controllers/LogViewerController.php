<?php

namespace Arcanedev\LogViewer\Http\Controllers;

use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
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
    public function index(Request $request)
    {
        $stats = $this->logViewer->statsTable();
        $chartData = $this->prepareChartData($stats);
        $percents = $this->calcPercentages($stats->footer(), $stats->header());

        return $this->view($request, 'dashboard', compact('chartData', 'percents'));
    }

    /**
     * Show the dashboard for today.
     *
     * @return \Illuminate\View\View
     */
    public function today(Request $request)
    {
        $date = date('Y-m-d');

        $stats = $this->logViewer->statsTableForDate($date);
        $chartData = $this->prepareChartData($stats);
        $percents = $this->calcPercentages($stats->footer(), $stats->header());

        return $this->view($request, 'dashboard', compact('chartData', 'percents', 'date'));
    }

    /**
     * List all logs.
     *
     *
     * @return \Illuminate\View\View
     */
    public function listLogs(Request $request)
    {
        $filters = $request->only(['prefix', 'date']);

        $stats = $this->logViewer->logs()
            ->when(! empty($filters), function (LogCollection $files) use ($filters) {
                return $files->filter(function (Log $log) use ($filters) {
                    foreach ($filters as $key => $value) {
                        if ($log->{$key} !== $value) {
                            return false;
                        }
                    }

                    return true;
                });
            })
            ->stats();

        $stats = $this->logViewer->statsTableFor($stats);
        $headers = $stats->header();
        $rows = $this->paginate($stats->rows(), $request);

        return $this->view($request, 'logs', compact('headers', 'rows'));
    }

    /**
     * Show the log.
     *
     *
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $prefix, string $date)
    {
        if ($order = $request->order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        $level = 'all';
        $log = $this->getLogOrFail($prefix, $date);
        $query = $request->get('query');
        $levels = $this->logViewer->levelsNames();

        $entries = $this->filter($log->entries($level), $request)->paginate($this->perPage);

        return $this->view($request, 'show', compact('level', 'log', 'query', 'levels', 'entries'));
    }

    /**
     * Show similar entries.
     *
     *
     * @return \Illuminate\View\View
     */
    public function showSimilar(Request $request, string $prefix, string $date, string $level)
    {
        if ($order = $request->order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        $text = $request->get('text', '');
        $similarity = (float) $request->get('similarity', config('log-viewer.similarity', 76));

        $log = $this->getLogOrFail($prefix, $date);
        $query = $request->get('query');
        $levels = $this->logViewer->levelsNames();
        $entries = $log->getSimilar($text, $similarity)->paginate($this->perPage);

        return $this->view($request, 'show', compact('level', 'log', 'query', 'levels', 'entries'));
    }

    /**
     * Filter the log entries by level.
     *
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

        $log = $this->getLogOrFail($prefix, $date);
        $query = $request->get('query');
        $levels = $this->logViewer->levelsNames();
        $entries = $this->filter($log->entries($level), $request)->paginate($this->perPage);

        return $this->view($request, 'show', compact('level', 'log', 'query', 'levels', 'entries'));
    }

    /**
     * Show the log with the search query.
     *
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

        $log = $this->getLogOrFail($prefix, $date);
        $levels = $this->logViewer->levelsNames();
        $needles = array_map(function ($needle) {
            return Str::lower($needle);
        }, array_filter(explode(' ', $query)));
        $entries = $this->filter($log->entries($level), $request)
            ->unless(empty($needles), function (LogEntryCollection $entries) use ($needles) {
                return $entries->filter(function (LogEntry $entry) use ($needles) {
                    return Str::containsAll(Str::lower($entry->header), $needles);
                });
            })
            ->paginate($this->perPage);

        return $this->view($request, 'show', compact('level', 'log', 'query', 'levels', 'entries'));
    }

    /**
     * Download the log
     *
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
            'result' => $this->logViewer->delete($prefix, $date) ? 'success' : 'error',
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
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View
     */
    protected function view(Request $request, $view, $data = [], $mergeData = [])
    {
        $theme = config('log-viewer.theme');

        $data['route'] = app('router')->currentRouteName();

        $filters = array_merge($request->except('page'), $request->route()->parameters());
        if ($exclude = $request->exclude_similar) {
            $filters['exclude_similar'] = (array) $exclude;
        }
        $data['filters'] = $filters;

        $data['similarity'] = (float) $request->get('similarity', config('log-viewer.similarity', 76));

        return view()->make("log-viewer::{$theme}.{$view}", $data, $mergeData);
    }

    /**
     * Paginate logs.
     *
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

    protected function filter(LogEntryCollection $entries, Request $request): LogEntryCollection
    {
        $extras = $request->only(config('log-viewer.parser.extra_groups'));
        $uuidKeys = ['uuid', 'parentUuid'];
        $uuids = Arr::only($extras, $uuidKeys);
        $extras = Arr::except($extras, $uuidKeys);

        return $entries->unless(empty($uuids), function (LogEntryCollection $entries) use ($uuids, $uuidKeys) {
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
            ->when($request->exclude_similar, function (LogEntryCollection $entries, $similar) use ($request) {
                $similarity = (float) $request->get('similarity', config('log-viewer.similarity', 76));

                return $entries->filter(function (LogEntry $entry) use ($similar, $similarity) {
                    foreach ((array) $similar as $text) {
                        if ($entry->isSimilar($text, $similarity)) {
                            return false;
                        }
                    }

                    return true;
                });
            })
            ->when($request->unique, function (LogEntryCollection $entries) use ($request) {
                $was = [];
                $similarity = (float) $request->get('similarity', config('log-viewer.similarity', 76));

                return $entries->filter(function (LogEntry $entry) use (&$was, $similarity) {
                    $header = $entry->header;
                    if (isset($was[$header])) {
                        return false;
                    }

                    foreach ($was as $text => $b) {
                        if ($entry->isSimilar($text, $similarity)) {
                            return false;
                        }
                    }

                    $was[$header] = true;

                    return true;
                });
            });
    }

    /**
     * Prepare chart data.
     *
     *
     * @return string
     */
    protected function prepareChartData(StatsTable $stats)
    {
        $totals = $stats->totals()->all();

        return json_encode([
            'labels' => Arr::pluck($totals, 'label'),
            'datasets' => [
                [
                    'data' => Arr::pluck($totals, 'value'),
                    'backgroundColor' => Arr::pluck($totals, 'color'),
                    'hoverBackgroundColor' => Arr::pluck($totals, 'highlight'),
                ],
            ],
        ]);
    }

    /**
     * Calculate the percentage.
     *
     *
     * @return array
     */
    protected function calcPercentages(array $total, array $names)
    {
        $percents = [];
        $all = Arr::get($total, 'all');

        foreach ($total as $level => $count) {
            $percents[$level] = [
                'name' => $names[$level],
                'count' => $count,
                'percent' => $all ? round(($count / $all) * 100, 2) : 0,
            ];
        }

        return $percents;
    }
}
