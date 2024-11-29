<?php
/**
 * @var  Arcanedev\LogViewer\Entities\Log                                                                     $log
 * @var  Illuminate\Pagination\LengthAwarePaginator|array<string|int, Arcanedev\LogViewer\Entities\LogEntry>  $entries
 * @var  string|null                                                                                          $search
 */
?>

@extends('log-viewer::bootstrap-4._master')

@section('content')
    <div class="page-header mb-4">
        <h1>{{ trans('log-viewer::general.log') }} <span class="text-muted">{{ $log->prefix }}</span> [{{ $log->date }}]</h1>
    </div>

    <div class="row">
        <aside class="col-lg-2">
            {{-- Log Menu --}}
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-flag"></i> {{ trans('log-viewer::general.levels') }}<span class="btn btn-light ml-3 aside-hide">&lt;&lt;</span></div>
                <div class="list-group list-group-flush log-menu">
                    @foreach($log->menu() as $levelKey => $item)
                        @if ($item['count'] === 0)
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled">
                                <span class="level-name">{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                <span class="badge empty">{{ $item['count'] }}</span>
                            </a>
                        @else
                            <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center level-{{ $levelKey }}{{ $level === $levelKey ? ' active' : ''}}">
                                <span class="level-name">{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                <span class="badge badge-level-{{ $levelKey }}">{{ $item['count'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </aside>
        <section class="main-col col-lg-10">
            {{-- Log Details --}}
            <div class="card mb-4">
                <div class="card-header">
                    {{ trans('log-viewer::general.log-info') }}
                    <div class="group-btns pull-right">
                        <a href="{{ route('log-viewer::logs.download', [$log->prefix, $log->date]) }}" class="btn btn-sm btn-success mb-1 mb-sm-0">
                            <i class="bi bi-download"></i> {{ trans('log-viewer::general.download') }}
                        </a>
                        <a href="#delete-log-modal" class="btn btn-sm btn-danger mb-1 mb-sm-0" data-toggle="modal">
                            <i class="bi bi-trash"></i> {{ trans('log-viewer::general.delete') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed mb-0">
                        <tbody>
                            <tr>
                                <td>{{ trans('log-viewer::general.file-path') }}</td>
                                <td colspan="7">{{ $log->getPath() }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('log-viewer::general.log-entries') }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ method_exists($entries, 'total') ? $entries->total() : 'N' }}</span>
                                </td>
                                <td>{{ trans('log-viewer::general.size') }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->size() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::general.created-at') }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->createdAt() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::general.updated-at') }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->updatedAt() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <form action="{{ route('log-viewer::logs.show', ['prefix' => $log->prefix, 'date' => $log->date, 'level' => $level]) }}" method="GET">
                        <input type="hidden" name="level" value="{{ $level }}">
                        <div class="form-row justify-content-between align-items-center">
                            <div class="col-auto">
                                <div class="form-check">
                                    <input
                                        id="fuzzy"
                                        class="form-check-input"
                                        type="checkbox"
                                        name="fuzzy"
                                        @checked(request('fuzzy'))
                                    >
                                    <label class="form-check-label" for="fuzzy">{{
                                        trans('log-viewer::general.fuzzy-search')
                                    }}</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="form-check">
                                    <input
                                        id="unique"
                                        class="form-check-input"
                                        type="checkbox"
                                        name="unique"
                                        @checked(request('unique'))
                                    >
                                    <label class="form-check-label" for="unique">{{
                                        trans('log-viewer::general.unique')
                                    }}</label>
                                </div>
                            </div>
                            <div class="col-auto row justify-content-between align-items-center">
                                <label class="col-form-label" for="similarity">{{ trans('log-viewer::general.similarity') }}</label>
                                <div class="col-auto">
                                    <input
                                        id="similarity"
                                        type="range"
                                        class="form-control-range"
                                        name="similarity"
                                        min="0"
                                        max="100"
                                        value="{{ request('similarity', config('log-viewer.similarity', 76)) }}"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="form-row justify-content-between align-items-center">
                            <div class="col-auto">
                                <select class="custom-select" id="order-select" name="order">
                                    @foreach (['asc', 'desc'] as $o)
                                        <option value="{{ $o }}" @selected($order == $o)>
                                            {{ trans("log-viewer::general.order-{$o}") }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <div class="input-group">
                                <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="{{ trans('log-viewer::general.search-placeholder') }}">
                                <div class="input-group-append">
                                    @unless (is_null($search))
                                        <a href="{{ route('log-viewer::logs.show', [$log->prefix, $log->date]) }}" class="btn btn-secondary">
                                            <span class="sr-only">{{ trans('log-viewer::general.search-reset') }}</span> <i class="bi bi-x-circle"></i>
                                        </a>
                                    @endunless
                                    <button id="search-btn" class="btn btn-primary">
                                        <span class="sr-only">{{ trans('log-viewer::general.search-submit') }}</span> <span class="bi bi-search"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Log Entries --}}
            <div class="card mb-4">
                @if ($entries->hasPages())
                    <div class="card-header">
                        <span class="badge badge-info float-right">
                            {{ trans('log-viewer::general.page') }} {{ $entries->currentPage() }} {{ trans('log-viewer::general.of') }} {{ method_exists($entries, 'lastPage') ? $entries->lastPage() : 'N' }}
                        </span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="entries" class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ trans('log-viewer::general.info-actions') }}</th>
                                <th>{{ trans('log-viewer::general.header') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $key => $entry)
                                <tr>
                                    <td>
                                        @foreach ($entry->extra as $propName => $extra)
                                            <a class="badge badge-env badge-extra-{{ $propName }}" href="{{ route('log-viewer::logs.show', array_merge(request()->input(), ['prefix' => $log->prefix, 'date' => $log->date, $propName => $extra])) }}">
                                                {{ $extra }}
                                            </a>
                                        @endforeach
                                        <span class="badge badge-env">{{ $entry->env }}</span>
                                        <span class="badge badge-level-{{ $entry->level }}">
                                            {!! $entry->level() !!}
                                        </span>
                                        <span class="badge badge-secondary">
                                            {{ $entry->getDatetime()->format('H:i:s') }}
                                        </span>

                                        <br/>

                                        <div class="btn-group">
                                            <a class="btn btn-sm btn-light" href="{{ route('log-viewer::logs.show', ['prefix' => $log->prefix, 'date' => $log->date, 'level' => $entry->level, 'search' => $entry->header, 'fuzzy' => true]) }}">{{ trans('log-viewer::general.similar') }}</a>
                                            @if (!request('search'))
                                                <a class="btn btn-sm btn-light" href="{{ route($route, array_merge($filters, ['exclude_similar' => array_merge($filters['exclude_similar'] ?? [], [$entry->header])])) }}">{{ trans('log-viewer::general.exclude-similar') }}</a>
                                            @endif
                                        </div>

                                        @if ($entry->hasContext())
                                            <a class="btn btn-sm btn-light" role="button" data-toggle="collapse"
                                            href="#log-context-{{ $key }}" aria-expanded="false" aria-controls="log-context-{{ $key }}">
                                                <i class="bi bi-toggle-on"></i> Context
                                            </a>
                                        @endif

                                        @if ($entry->hasStack())
                                            <a class="btn btn-sm btn-light" role="button" data-toggle="collapse"
                                            href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                <i class="bi bi-toggle-on"></i> Stack
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $entry->header }}
                                    </td>
                                </tr>
                                @if ($entry->hasStack() || $entry->hasContext())
                                    <tr>
                                        <td colspan="5" class="stack py-0">
                                            @if ($entry->hasContext())
                                                <div class="stack-content collapse" id="log-context-{{ $key }}">
                                                    <pre>{{ $entry->context() }}</pre>
                                                </div>
                                            @endif

                                            @if ($entry->hasStack())
                                                <div class="stack-content collapse" id="log-stack-{{ $key }}">
                                                    {!! $entry->stack() !!}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <span class="badge badge-secondary">{{ trans('log-viewer::general.empty-logs') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {!! $entries->appends(compact('search'))->render(method_exists($entries, 'total') ? 'pagination::bootstrap-4' : 'pagination::simple-bootstrap-4') !!}
        </section>
    </div>
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="prefix" value="{{ $log->prefix }}">
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">DELETE LOG FILE</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <span class="badge badge-danger">DELETE</span> this log file <span class="badge badge-secondary">{{ $log->prefix }}</span> <span class="badge badge-primary">{{ $log->date }}</span> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">DELETE FILE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="aside-show card btn btn-default" style="display: none;">&gt;&gt;</div>
    <style>
        .aside-show {
            z-index: 1;
            position: absolute;
            top: 7.5rem;
            left: 0;
        }
        .aside-hide {
            cursor: pointer;
            padding: 0;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(function () {
            var deleteLogModal = $('div#delete-log-modal'),
                deleteLogForm  = $('form#delete-log-form'),
                submitBtn      = deleteLogForm.find('button[type=submit]');

            deleteLogForm.on('submit', function(event) {
                event.preventDefault();
                submitBtn.button('loading');

                $.ajax({
                    url:      $(this).attr('action'),
                    type:     $(this).attr('method'),
                    dataType: 'json',
                    data:     $(this).serialize(),
                    success: function(data) {
                        submitBtn.button('reset');
                        if (data.result === 'success') {
                            deleteLogModal.modal('hide');
                            location.replace("{{ route('log-viewer::logs.list') }}");
                        }
                        else {
                            alert('OOPS ! This is a lack of coffee exception !')
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('AJAX ERROR ! Check the console !');
                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });

            $('aside .aside-hide').click(function () {
                $('aside').hide();
                $('.aside-show').show();
                $('.main-col').removeClass('col-lg-10');
                $('.main-col').addClass('col-lg-12');
            });
            $('.aside-show').click(function () {
                $('.aside-show').hide();
                $('aside').show();
                $('.main-col').removeClass('col-lg-12');
                $('.main-col').addClass('col-lg-10');
            });

            @unless (empty(log_styler()->toHighlight()))
                @php
                    $htmlHighlight = version_compare(PHP_VERSION, '7.4.0') >= 0
                        ? join('|', log_styler()->toHighlight())
                        : join(log_styler()->toHighlight(), '|');
                @endphp

                $('.stack-content').each(function() {
                    var $this = $(this);
                    var html = $this.html().trim()
                        .replace(/({!! $htmlHighlight !!})/gm, '<strong>$1</strong>');

                    $this.html(html);
                });
            @endunless
        });
    </script>
@endsection
