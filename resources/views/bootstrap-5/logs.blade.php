@extends('log-viewer::bootstrap-5._master')

@section('content')
    <div class="page-header mb-4">
        <h1>{{ trans('log-viewer::general.logs') }}</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    @foreach($headers as $key => $header)
                    <th scope="col" class="text-center">
                        @if ($key == 'date' || $key == 'prefix')
                            <span class="badge text-bg-info">{{ $header }}</span>
                        @else
                            <span class="badge text-bg-level-{{ $key }}">
                                {{ log_styler()->icon($key) }}
                            </span>
                        @endif
                    </th>
                    @endforeach
                    <th scope="col" class="text-right">{{ trans('log-viewer::general.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $prefix => $dates)
                    @foreach ($dates as $date => $row)
                        <tr>
                            @foreach($row as $key => $value)
                                <td class="text-center">
                                    @if ($key == 'date')
                                        <a href="{{ route('log-viewer::logs.list', compact('date')) }}"><span class="badge text-bg-primary">{{ $value }}</span></a>
                                    @elseif ($key == 'prefix')
                                        <a href="{{ route('log-viewer::logs.list', ['prefix' => $value]) }}"><span class="badge text-bg-primary">{{ $value }}</span></a>
                                    @elseif ($value == 0)
                                        <span class="badge empty">{{ $value }}</span>
                                    @else
                                        <a href="{{ route('log-viewer::logs.show', ['prefix' => $prefix, 'date' => $date, 'level' => $key === 'all' ? null : $key]) }}">
                                            <span class="badge text-bg-level-{{ $key }}">{{ $value }}</span>
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-right">
                                <a href="{{ route('log-viewer::logs.show', [$prefix, $date]) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('log-viewer::logs.stats', [$prefix, $date]) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-calculator"></i>
                                </a>
                                <a href="{{ route('log-viewer::logs.download', [$prefix, $date]) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="#delete-log-modal" class="btn btn-sm btn-danger" data-log-prefix="{{ $prefix }}" data-log-date="{{ $date }}">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="11" class="text-center">
                            <span class="badge text-bg-secondary">{{ trans('log-viewer::general.empty-logs') }}</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $rows->render() }}
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="prefix" value="">
                <input type="hidden" name="date" value="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">DELETE LOG FILE</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">Cancel</button>
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

            $("a[href='#delete-log-modal']").on('click', function(event) {
                event.preventDefault();
                var prefix = $(this).data('log-prefix');
                var date = $(this).data('log-date');
                deleteLogForm.find('input[name=prefix]').val(prefix);
                deleteLogForm.find('input[name=date]').val(date);
                deleteLogModal.find('.modal-body p').html(
                    'Are you sure you want to <span class="badge text-bg-danger">DELETE</span> this log file <span class="badge text-bg-secondary">' + prefix + '</span> <span class="badge text-bg-primary">' + date + '</span> ?'
                );

                deleteLogModal.modal('show');
            });

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
                            location.reload();
                        }
                        else {
                            alert('AJAX ERROR ! Check the console !');
                            console.error(data);
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

            deleteLogModal.on('hidden.bs.modal', function() {
                deleteLogForm.find('input[name=prefix]').val('');
                deleteLogForm.find('input[name=date]').val('');
                deleteLogModal.find('.modal-body p').html('');
            });
        });
    </script>
@endsection
