@extends('log-viewer::bootstrap-5._master')

@section('content')
    <div class="page-header mb-4">
        <h1>{{ trans('log-viewer::general.log') }} <span class="text-muted">{{ $log->prefix }}</span> [{{ $log->date }}]</h1>
    </div>

    <div class="row">
        <aside class="col-lg-2">
            {{-- Log Menu --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-flag"></i>
                    {{ trans('log-viewer::general.levels') }}
                    <button type="button" class="btn btn-light ml-3 aside-hide">&lt;&lt;</button>
                </div>
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
                                <span class="badge text-bg-level-{{ $levelKey }}">{{ $item['count'] }}</span>
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
                        @unless ((app('router')->getCurrentRoute()?->getName() ?? false) === 'log-viewer::logs.stats')
                            <a href="{{ route('log-viewer::logs.stats', [$log->prefix, $log->date]) }}" class="btn btn-sm btn-warning mb-1 mb-sm-0">
                                <i class="bi bi-calculator"></i> {{ trans('log-viewer::general.stats') }}
                            </a>
                        @endunless
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed mb-0">
                        <tbody>
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark"></i>
                                    {{ trans('log-viewer::general.file-path') }}
                                </td>
                                <td colspan="7">{{ $log->getPath() }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="bi bi-card-checklist"></i>
                                    {{ trans('log-viewer::general.log-entries') }}
                                </td>
                                <td>
                                    <span class="badge text-bg-primary">{{ count($log) }}</span>
                                </td>
                                <td>
                                    <i class="bi bi-file-earmark-diff"></i>
                                    {{ trans('log-viewer::general.size') }}
                                </td>
                                <td>
                                    <span class="badge text-bg-primary">{{ $log->size() }}</span>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-plus"></i>
                                    {{ trans('log-viewer::general.created-at') }}
                                </td>
                                <td>
                                    <span class="badge text-bg-primary"><time
                                        class="datetime"
                                        datetime="{{ $log->createdAt()->format('Y-m-d\TH:i:s') . ".000Z" }}"
                                    >{{ $log->createdAt() }}</time></span>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-event"></i>
                                    {{ trans('log-viewer::general.updated-at') }}
                                </td>
                                <td>
                                    <span class="badge text-bg-primary"><time
                                        class="datetime"
                                        datetime="{{ $log->updatedAt()->format('Y-m-d\TH:i:s') . ".000Z" }}"
                                    >{{ $log->updatedAt() }}</time></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <form action="{{ route('log-viewer::logs.show', ['prefix' => $log->prefix, 'date' => $log->date, 'level' => $level]) }}" method="GET">
                        <input type="hidden" name="level" value="{{ $level }}">
                        @foreach (request('context', []) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <div class="row justify-content-between align-items-center">
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
                        @isset($order)
                            <div class="row justify-content-between align-items-center">
                                <div class="col-auto">
                                    <select class="form-select" id="order-select" name="order">
                                        @foreach (['asc', 'desc'] as $o)
                                            <option value="{{ $o }}" @selected($order == $o)>
                                                {{ trans("log-viewer::general.order-{$o}") }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endisset
                        <div class="form-group mt-3">
                            <div class="input-group">
                                <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="{{ trans('log-viewer::general.search-placeholder') }}">
                                @unless (is_null($search))
                                    <a
                                        href="{{ route('log-viewer::logs.show', [$log->prefix, $log->date]) }}"
                                        class="btn btn-secondary"
                                    >
                                        <span class="sr-only">{{ trans('log-viewer::general.search-reset') }}</span> <i class="bi bi-x-circle"></i>
                                    </a>
                                @endunless
                                <button id="search-btn" class="btn btn-primary">
                                    <span class="sr-only">{{ trans('log-viewer::general.search-submit') }}</span> <span class="bi bi-search"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @yield('section')

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
                        <p>Are you sure you want to <span class="badge text-bg-danger">DELETE</span> this log file <span class="badge text-bg-secondary">{{ $log->prefix }}</span> <span class="badge text-bg-primary">{{ $log->date }}</span> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">DELETE FILE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <button type="button"class="aside-show card btn btn-default" style="display: none;">&gt;&gt;</button>
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
