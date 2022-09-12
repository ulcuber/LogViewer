<?php
/**
 * @var  Arcanedev\LogViewer\Entities\Log            $log
 * @var  Illuminate\Pagination\LengthAwarePaginator  $entries
 * @var  string|null                                 $query
 */
?>

@extends('log-viewer::bootstrap-3._master')

@section('content')
    <h1 class="page-header">{{ trans('log-viewer::general.log') }} <span class="text-muted">{{ $log->prefix }}</span> [{{ $log->date }}]</h1>

    <div class="row">
        <div class="col-md-2">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-fw fa-flag"></i> {{ trans('log-viewer::general.levels') }}</div>
                <ul class="list-group">
                    @foreach($log->menu() as $levelKey => $item)
                        @if ($item['count'] === 0)
                            <a href="#" class="list-group-item disabled">
                                <span class="badge">
                                    {{ $item['count'] }}
                                </span>
                                {!! $item['icon'] !!} {{ $item['name'] }}
                            </a>
                        @else
                            <a href="{{ $item['url'] }}" class="list-group-item {{ $levelKey }}">
                                <span class="badge level-{{ $levelKey }}">
                                    {{ $item['count'] }}
                                </span>
                                <span class="level level-{{ $levelKey }}">
                                    {!! $item['icon'] !!} {{ $item['name'] }}
                                </span>
                            </a>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md-10">
            {{-- Log Details --}}
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ trans('log-viewer::general.log-info') }}

                    <div class="group-btns pull-right">
                        <a href="{{ route('log-viewer::logs.download', [$log->prefix, $log->date]) }}" class="btn btn-xs btn-success">
                            <i class="fa fa-download"></i> {{ trans('log-viewer::general.download') }}
                        </a>
                        <a href="#delete-log-modal" class="btn btn-xs btn-danger" data-toggle="modal">
                            <i class="fa fa-trash-o"></i> {{ trans('log-viewer::general.delete') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <td>{{ trans('log-viewer::general.file-path') }}</td>
                                <td colspan="5">{{ $log->getPath() }}</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ trans('log-viewer::general.log-entries') }}</td>
                                <td>
                                    <span class="label label-primary">{{ $entries->total() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::general.size') }}</td>
                                <td>
                                    <span class="label label-primary">{{ $log->size() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::general.created-at') }}</td>
                                <td>
                                    <span class="label label-primary">{{ $log->createdAt() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::general.updated-at') }}</td>
                                <td>
                                    <span class="label label-primary">{{ $log->updatedAt() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    {{-- Search --}}
                    <form action="{{ route('log-viewer::logs.search', [$log->prefix, $log->date, $level]) }}" method="GET">
                        <div class="form-group">
                            <div class="input-group">
                                <input id="query" name="query" class="form-control" value="{{ $query }}" placeholder="Type here to search">
                                <span class="input-group-btn">
                                    @unless (is_null($query))
                                        <a href="{{ route('log-viewer::logs.show', [$log->prefix, $log->date]) }}" class="btn btn-default">
                                            ({{ $entries->count() }} {{ trans('log-viewer::general.of-results') }}) <span class="glyphicon glyphicon-remove"></span>
                                        </a>
                                    @endunless
                                    <button id="search-btn" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </form>
                    @if (config('log-viewer.reversed_order'))
                        <a class="btn btn-xs btn-info" href="{{ route($route, array_merge($filters, ['order' => 'asc'])) }}">{{ trans('log-viewer::general.order-asc') }}</a>
                    @else
                        <a class="btn btn-xs btn-info" href="{{ route($route, array_merge($filters, ['order' => 'desc'])) }}">{{ trans('log-viewer::general.order-desc') }}</a>
                    @endif
                    @if (request('unique'))
                        <a class="btn btn-xs btn-info active" aria-pressed="true" href="{{ route($route, array_merge($filters, ['unique' => null])) }}">{{ trans('log-viewer::general.unique') }}</a>
                    @else
                        <a class="btn btn-xs btn-info" href="{{ route($route, array_merge($filters, ['unique' => true])) }}">{{ trans('log-viewer::general.unique') }}</a>
                    @endif
                    <div class="pull-right">
                        <div class="btn-group">
                            <span class="badge badge-info">{{ trans('log-viewer::general.similarity') }} {{ $similarity }}</span>
                            <a class="btn badge badge-info" href="{{ route($route, array_merge($filters, ['similarity' => $similarity - 1])) }}">-</a>
                            <a class="btn badge badge-info" href="{{ route($route, array_merge($filters, ['similarity' => $similarity + 1])) }}">+</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Log Entries --}}
            <div class="panel panel-default">
                @if ($entries->hasPages())
                    <div class="panel-heading">
                        {{ $entries->appends(compact('query'))->render() }}

                        <span class="label label-info pull-right">
                            {{ trans('log-viewer::general.page') }} {{ $entries->currentPage() }} {{ trans('log-viewer::general.of') }} {{ $entries->lastPage() }}
                        </span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="entries" class="table table-condensed">
                        <thead>
                            <tr>
                                <th>{{ trans('log-viewer::general.info-actions') }}</th>
                                <th>{{ trans('log-viewer::general.header') }}</th>
                                <th class="text-right">{{ trans('log-viewer::general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $key => $entry)
                                <?php /** @var  Arcanedev\LogViewer\Entities\LogEntry  $entry */ ?>
                                <tr>
                                    <td>
                                        @foreach ($entry->extra as $key => $extra)
                                            <a class="label label-env label-extra-{{ $key }}" href="{{ route('log-viewer::logs.show', array_merge(request()->input(), ['prefix' => $log->prefix, 'date' => $log->date, $key => $extra])) }}">
                                                {{ $extra }}
                                            </a>
                                        @endforeach
                                        <span class="label label-env">{{ $entry->env }}</span>
                                        <span class="level level-{{ $entry->level }}">{!! $entry->level() !!}</span>
                                        <span class="label label-default">
                                            {{ $entry->getDatetime()->format('H:i:s') }}
                                        </span>
                                        <br/>
                                        <a class="btn btn-xs btn-default" href="{{ route('log-viewer::logs.similar', [$log->prefix, $log->date, $entry->level, 'text' => $entry->header]) }}">{{ trans('log-viewer::general.similar') }}</a>
                                    </td>
                                    <td>
                                        <p>{{ $entry->header }}</p>
                                    </td>
                                    <td class="text-right">
                                        @if ($entry->hasContext())
                                            <a class="btn btn-xs btn-default" role="button" data-toggle="collapse"
                                            href="#log-context-{{ $key }}" aria-expanded="false" aria-controls="log-context-{{ $key }}">
                                                <i class="fa fa-toggle-on"></i> Context
                                            </a>
                                        @endif
                                        @if ($entry->hasStack())
                                            <a class="btn btn-xs btn-default" role="button" data-toggle="collapse"
                                            href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                <i class="fa fa-toggle-on"></i> Stack
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @if ($entry->hasStack() || $entry->hasContext())
                                    <tr>
                                        <td colspan="5" class="stack">
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
                                        <span class="label label-default">{{ trans('log-viewer::general.empty-logs') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($entries->hasPages())
                    <div class="panel-footer">
                        {!! $entries->appends(compact('query'))->render() !!}

                        <span class="label label-info pull-right">
                            {{ trans('log-viewer::general.page') }} {{ $entries->currentPage() }} {{ trans('log-viewer::general.of') }} {{ $entries->lastPage() }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="prefix" value="{{ $log->prefix }}">
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">DELETE LOG FILE</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <span class="label label-danger">DELETE</span> this log file <span class="label label-secondary">{{ $log->prefix }}</span> <span class="label label-primary">{{ $log->date }}</span> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">DELETE FILE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
