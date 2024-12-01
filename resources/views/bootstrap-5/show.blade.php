@extends('log-viewer::bootstrap-5._log')

@section('section')
<div class="card mb-4">
    @if ($entries->hasPages())
        <div class="card-header">
            <span class="badge text-bg-info float-right">
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
                                <a class="badge text-bg-env text-bg-extra-{{ $propName }}" href="{{ route('log-viewer::logs.show', array_merge(request()->input(), ['prefix' => $log->prefix, 'date' => $log->date, $propName => $extra])) }}">
                                    {{ $extra }}
                                </a>
                            @endforeach
                            <span class="badge text-bg-env">{{ $entry->env }}</span>
                            <span class="badge text-bg-level-{{ $entry->level }}">
                                {!! $entry->level() !!}
                            </span>
                            <span class="badge text-bg-secondary">
                                <time
                                    class="time"
                                    datetime="{{ $entry->getDatetime()->format('Y-m-d\TH:i:s') . ".000Z" }}"
                                >{{ $entry->getDatetime()->toTimeString() }}</time>
                            </span>

                            <br/>

                            <div class="btn-group">
                               <a class="btn btn-sm btn-light" href="{{ route('log-viewer::logs.show', ['prefix' => $log->prefix, 'date' => $log->date, 'level' => $entry->level, 'search' => $entry->header, 'fuzzy' => true]) }}">{{ trans('log-viewer::general.similar') }}</a>
                                @if (!request('search'))
                                    <a class="btn btn-sm btn-light" href="{{ route($route, array_merge($filters, ['exclude_similar' => array_merge($filters['exclude_similar'] ?? [], [$entry->header])])) }}">{{ trans('log-viewer::general.exclude-similar') }}</a>
                                @endif
                            </div>

                            @if ($entry->hasContext())
                                <a class="btn btn-sm btn-light" role="button" data-bs-toggle="collapse"
                                href="#log-context-{{ $key }}" aria-expanded="false" aria-controls="log-context-{{ $key }}">
                                    <i class="bi bi-toggle-on"></i> Context
                                </a>
                            @endif

                            @if ($entry->hasStack())
                                <a class="btn btn-sm btn-light" role="button" data-bs-toggle="collapse"
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
                            <span class="badge text-bg-secondary">{{ trans('log-viewer::general.empty-logs') }}</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{!! $entries->appends(compact('search'))->render(method_exists($entries, 'total') ? 'pagination::bootstrap-5' : 'pagination::simple-bootstrap-5') !!}
@endsection
