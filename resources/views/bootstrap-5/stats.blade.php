@extends('log-viewer::bootstrap-5._log')

@section('section')
<div class="card mb-4">
    <div class="card-header">
        <div class="card-title">{{ trans('log-viewer::general.stats') }}</div>
    </div>
    <ul class="list-group list-group-flush">
        @foreach ($stats as $name => $stat)
            <li class="list-group-item d-flex justify-content-between flex-wrap">
                <span>{{ $name }}</span>
                <span>{{ $stat }}</span>
            </li>
        @endforeach
    </ul>
    <ul class="list-group list-group-flush">
        @foreach ($context as $dottedKey => $values)
            @php
                $valuesCount = count($values);
                $collapsable = $valuesCount > 3;
                $collapseId = "values-list-{$dottedKey}";
            @endphp
            <li class="list-group-item d-flex justify-content-between flex-wrap">
                <span>
                    {{ $dottedKey }}
                    @if ($collapsable)
                        <a
                            class="btn btn-sm btn-light"
                            role="button"
                            data-bs-toggle="collapse"
                            href="#{{ $collapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $collapseId }}"
                        >
                            <span class="badge text-bg-primary rounded-pill">{{ $valuesCount }}</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                    @endif
                </span>
                <ul id="{{ $collapseId }}" class="list-group list-group-flush @if ($collapsable) collapse @endif">
                    @foreach ($values as $value => $info)
                        <li class="list-group-item">
                            <span class="badge text-bg-primary rounded-pill">{{ $info['count'] }}</span>
                            <a class="ms-1" href="{{ $info['url'] }}">
                                {{ $value }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</div>
@endsection
