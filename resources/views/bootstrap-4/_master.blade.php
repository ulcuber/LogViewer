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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700|Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>
    @include('log-viewer::bootstrap-4._styles')
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark sticky-top bg-dark p-0">
        <a href="{{ route('log-viewer::dashboard') }}" class="navbar-brand mr-0">
            <i class="fa fa-fw fa-book"></i> LogViewer
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ Route::is('log-viewer::dashboard') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::dashboard') }}" class="nav-link">
                        <i class="fa fa-dashboard"></i> {{ trans('log-viewer::general.dashboard') }}
                    </a>
                </li>
                <li class="nav-item {{ Route::is('log-viewer::today') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::today') }}" class="nav-link">
                        <i class="fa fa-dashboard"></i> {{ trans('log-viewer::general.today') }}
                    </a>
                </li>
                <li class="nav-item {{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::logs.list') }}" class="nav-link">
                        <i class="fa fa-archive"></i> {{ trans('log-viewer::general.logs') }}
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
        <div class="container-fluid">
            <p class="text-muted pull-left">
                LogViewer - <span class="badge badge-info">version {{ log_viewer()->version() }}</span>
            </p>
            <p class="text-muted pull-right">
                Created with <i class="fa fa-heart"></i> by <a href="https://github.com/ARCANEDEV">ARCANEDEV</a> <sup>&copy;</sup> adopted by <a href="https://github.com/ulcuber/">&#64;ulcuber</a>
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
