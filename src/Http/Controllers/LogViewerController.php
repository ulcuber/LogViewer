<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Http\Controllers;

use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Exceptions\LogNotFoundException;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * @var LogViewerContract
     */
    protected $logViewer;

    /** @var int */
    protected $perPage = 30;

    public function __construct(LogViewerContract $logViewer)
    {
        $this->logViewer = $logViewer;
        $this->perPage = config('log-viewer.per-page', $this->perPage);
    }

    public function index(Request $request): View
    {
        $stats = $this->logViewer->statsTable();
        $chartData = $this->prepareChartData($stats);
        $percents = $this->calcPercentages($stats->footer(), $stats->header());

        return $this->view($request, 'dashboard', compact('chartData', 'percents'));
    }

    /**
     * Show the dashboard for today.
     *
     * @return View
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
     * @return View
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

    public function show(Request $request, string $prefix, string $date): View
    {
        $order = $request->get(
            'order',
            fn () => config('log-viewer.reversed_order') ? 'desc' : 'asc'
        );
        if ($order) {
            config(['log-viewer.reversed_order' => $order === 'desc']);
        }

        $level = $request->get('level', 'all');
        $log = $this->getLogOrFail($prefix, $date);
        $search = $request->get('search');
        $levels = $this->logViewer->levelsNames();

        $entries = $this->filter($log->entries($level), $request)->paginate($this->perPage);

        return $this->view($request, 'show', compact('level', 'log', 'search', 'levels', 'entries', 'order'));
    }

    public function stats(Request $request, string $prefix, string $date): View
    {
        $level = $request->get('level', 'all');
        $routeLevel = $level === 'all' ? null : $level;

        $log = $this->getLogOrFail($prefix, $date);
        $search = $request->get('search');
        $levels = $this->logViewer->levelsNames();

        $entries = $this->filter($log->entries($level), $request);

        $stats = [
            'has_context' => 0,
        ];
        $context = [];
        foreach ($entries as $entry) {
            /** @var LogEntry $entry */
            if ($entry->hasContext()) {
                $stats['has_context']++;
                $flat = Arr::dot($entry->context);
                foreach ($flat as $dottedKey => $value) {
                    if (! isset($context[$dottedKey])) {
                        $context[$dottedKey] = [];
                    }

                    if (is_array($value)) {
                        // empty array could not be flattened
                        $value = '';
                    }

                    if (isset($context[$dottedKey][$value])) {
                        $context[$dottedKey][$value]['count']++;
                    } else {
                        $context[$dottedKey][$value] = [
                            'url' => route('log-viewer::logs.show', [
                                'prefix' => $log->prefix,
                                'date' => $log->date,
                                'level' => $routeLevel,
                                'context' => [
                                    $dottedKey => $value,
                                ],
                            ]),
                            'count' => 1,
                        ];
                    }
                }
            }
        }

        foreach ($context as $dottedKey => &$values) {
            $values = collect($values)->sortByDesc('count')->all();
        }

        return $this->view($request, 'stats', compact('level', 'log', 'search', 'levels', 'stats', 'context'));
    }

    public function download(string $prefix, string $date): BinaryFileResponse
    {
        return $this->logViewer->download($prefix, $date);
    }

    public function delete(Request $request): JsonResponse
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

    protected function view(Request $request, string $view, array $data = [], array $mergeData = []): View
    {
        $theme = config('log-viewer.theme');

        $data['route'] = app('router')->currentRouteName();

        $filters = array_merge($request->except('page'), $request->route()->parameters());
        if ($exclude = $request->exclude_similar) {
            $filters['exclude_similar'] = (array) $exclude;
        }
        $data['filters'] = $filters;

        $data['similarity'] = $request->float('similarity', (float) config('log-viewer.similarity', 76));

        return view()->make("log-viewer::{$theme}.{$view}", $data, $mergeData);
    }

    protected function paginate(array $data, Request $request): LengthAwarePaginator
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

    protected function getLogOrFail(string $prefix, string $date): Log
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
        $context = $request->context;

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
                $similarity = $request->float('similarity', (float) config('log-viewer.similarity', 76));

                return $entries->filter(function (LogEntry $entry) use ($similar, $similarity) {
                    foreach ((array) $similar as $text) {
                        if ($entry->isSimilar($text, $similarity)) {
                            return false;
                        }
                    }

                    return true;
                });
            })
            ->when($request->search, function (LogEntryCollection $entries, string|array $search) use ($request) {
                if ($request->boolean('fuzzy')) {
                    $similarity = $request->float('similarity', (float) config('log-viewer.similarity', 76));

                    return $entries->filter(function (LogEntry $entry) use (&$search, &$similarity) {
                        foreach ((array) $search as $text) {
                            if ($entry->isSimilar($text, $similarity)) {
                                return true;
                            }
                        }

                        return false;
                    });
                }

                $needles = array_map(function ($needle) {
                    return Str::lower($needle);
                }, array_filter(explode(' ', $search)));

                return $entries->unless(empty($needles), function (LogEntryCollection $entries) use ($needles) {
                    return $entries->filter(function (LogEntry $entry) use ($needles) {
                        return Str::containsAll(Str::lower($entry->header), $needles);
                    });
                });
            })
            ->unless(empty($context), function (LogEntryCollection $entries) use (&$context) {
                return $entries->filter(function (LogEntry $entry) use (&$context) {
                    if ($entry->hasNotContext()) {
                        return false;
                    }

                    foreach ((array) $context as $key => $value) {
                        if (Arr::get($entry->context, $key) === $value) {
                            return true;
                        }
                    }

                    return false;
                });
            })
            ->when($request->unique, function (LogEntryCollection $entries) use ($request) {
                $was = [];
                $similarity = $request->float('similarity', (float) config('log-viewer.similarity', 76));

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

    protected function prepareChartData(StatsTable $stats): string
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

    protected function calcPercentages(array $total, array $names): array
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
