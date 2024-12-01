<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="LogViewer">
    <meta name="author" content="ARCANEDEV">
    <title>LogViewer - Created by ARCANEDEV</title>
    {{-- Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700|Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>
    @include('log-viewer::bootstrap-5._styles')
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
                <a href="https://github.com/ulcuber/LogViewer/tree/v9.x">
                    <i class="bi bi-github"></i>
                    LogViewer
                </a> - <span class="badge text-bg-info">version {{ log_viewer()->version() }}</span>
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>

    <script>
        $(() => {
          const locale = undefined;
          const dateTimeFormat = new Intl.DateTimeFormat(locale, {
            dateStyle: 'short',
            timeStyle: 'short',
          });
          const timeFormat = new Intl.DateTimeFormat(locale, {
            timeStyle: 'short',
          });

          function local(selector, formatter) {
            const elements = document.querySelectorAll(selector);
            const dates = Array.from(elements);
            el = dates.pop();
            while (el) {
              el.textContent = formatter.format(
                  new Date(el.getAttribute('datetime'))
              );
              el = dates.pop();
            }
          }

          local('time.datetime', dateTimeFormat);
          local('time.time', timeFormat);
        });
    </script>

    @yield('modals')
    @yield('scripts')
</body>
</html>
