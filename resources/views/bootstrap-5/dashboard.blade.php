@extends('log-viewer::bootstrap-5._master')

@section('content')
    <div class="page-header mb-4">
        <h1>{{ trans('log-viewer::general.dashboard') }}@isset($date)[<a href="{{ route('log-viewer::logs.list', compact('date')) }}">{{ $date }}</a>]@endisset</h1>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-3">
            <canvas id="stats-doughnut-chart" height="300" class="mb-3"></canvas>
        </div>

        <div class="col-md-6 col-lg-9">
            <div class="row">
                @foreach($percents as $level => $item)
                    <div class="col-sm-6 col-md-12 col-lg-4 mb-3">
                        @isset($date)
                            <a class="box-link" href="{{ route('log-viewer::logs.show', ['prefix' => config('log-viewer.default_prefix', 'laravel'), 'date' => $date, 'level' => $level === 'all' ? null : $level]) }}">
                        @endisset
                        <div class="box level-{{ $level }} {{ $item['count'] === 0 ? 'empty' : '' }}">
                            <div class="box-icon">
                                {!! log_styler()->icon($level) !!}
                            </div>

                            <div class="box-content">
                                <span class="box-text">{{ $item['name'] }}</span>
                                <span class="box-number">
                                    {{ $item['count'] }} {{ trans('log-viewer::general.of-entries') }} - {!! $item['percent'] !!} %
                                </span>
                                <div class="progress" style="height: 3px;">
                                    <div class="progress-bar" style="width: {{ $item['percent'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        @isset($date)
                        </a>
                        @endisset
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            new Chart(document.getElementById("stats-doughnut-chart"), {
                type: 'doughnut',
                data: {!! $chartData !!},
                options: {
                    legend: {
                        position: 'bottom'
                    }
                }
            });
        });
    </script>
@endsection
