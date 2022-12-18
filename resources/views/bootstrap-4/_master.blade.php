<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="LogViewer">
    <meta name="author" content="ARCANEDEV">
    <title>LogViewer - Created by ARCANEDEV</title>
    {{-- Styles --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700|Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>
    @include('log-viewer::bootstrap-4._styles')
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark sticky-top bg-dark p-0">
        <a href="{{ route('log-viewer::dashboard') }}" class="navbar-brand mr-0">
            <i class="bi bi-book"></i> LogViewer
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ Route::is('log-viewer::dashboard') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::dashboard') }}" class="nav-link">
                        <i class="bi bi-speedometer2" style="font-size: 1.5rem;"></i> {{ trans('log-viewer::general.dashboard') }}
                    </a>
                </li>
                <li class="nav-item {{ Route::is('log-viewer::today') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::today') }}" class="nav-link">
                        <i class="bi bi-speedometer2" style="font-size: 1.5rem;"></i> {{ trans('log-viewer::general.today') }}
                    </a>
                </li>
                <li class="nav-item {{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::logs.list') }}" class="nav-link">
                        <i class="bi bi-list" style="font-size: 1.5rem;"></i> {{ trans('log-viewer::general.logs') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('log-viewer::logs.list', ['date' => date('Y-m-d')]) }}" class="nav-link">
                        <i class="bi bi-list" style="font-size: 1.5rem;"></i> {{ trans('log-viewer::general.today') }}
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <main role="main" class="pt-3">
            @yield('content')
        </main>
    </div>

    {{-- Footer --}}
    <footer class="main-footer">
        <div class="container-fluid d-flex flex-wrap justify-content-between">
            <p class="text-muted">
                LogViewer - <span class="badge badge-info">version {{ log_viewer()->version() }}</span>
            </p>
            <p class="text-muted">
                {{ log_viewer()->memoryString() }}
            </p>
            <p class="text-muted">
                Created with <i class="bi bi-heart" style="color: red;"></i> by <a href="https://github.com/ARCANEDEV">ARCANEDEV</a> <sup>&copy;</sup> adopted by <a href="https://github.com/ulcuber/">&#64;ulcuber</a>
            </p>
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>

    @yield('modals')
    @yield('scripts')
</body>
</html>
