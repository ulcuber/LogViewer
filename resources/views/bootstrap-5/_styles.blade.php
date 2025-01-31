<style>
    html {
        position: relative;
        min-height: 100%;
    }

    body {
        font-size: .875rem;
        margin-bottom: 60px;
    }

    .main-footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 60px;
        line-height: 60px;
        background-color: #E8EAF6;
    }

    .main-footer p {
        margin-bottom: 0;
    }

    .main-footer .fa.fa-heart {
        color: #C62828;
    }

    .page-header {
        border-bottom: 1px solid #8a8a8a;
    }

    /*
     * Navbar
     */

    .navbar-brand {
        padding: .75rem 1rem;
        font-size: 1rem;
    }

    .navbar-nav .nav-link {
        padding-right: .5rem;
        padding-left: .5rem;
    }

    /*
     * Boxes
     */

    .box-link:hover {
        text-decoration: none;
    }

    .box {
        display: block;
        padding: 0;
        min-height: 70px;
        background-color: {{ log_styler()->color('empty') }};
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: .25rem;
    }

    .box > .box-icon > i,
    .box .box-content .box-text,
    .box .box-content .box-number {
        color: #FFF;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
    }

    .box > .box-icon {
        border-radius: 2px 0 0 2px;
        display: block;
        float: left;
        height: 70px; width: 70px;
        text-align: center;
        font-size: 40px;
        line-height: 70px;
        background: rgba(0,0,0,0.2);
    }

    .box .box-content {
        padding: 5px 10px;
        margin-left: 70px;
    }

    .box .box-content .box-text {
        display: block;
        font-size: 1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }

    .box .box-content .box-number {
        display: block;
    }

    .box .box-content .progress {
        background: rgba(0,0,0,0.2);
        margin: 5px -10px 5px -10px;
    }

    .box .box-content .progress .progress-bar {
        background-color: #FFF;
    }

    /*
     * Log Menu
     */

    .log-menu .list-group-item.disabled {
        cursor: not-allowed;
    }

    .log-menu .list-group-item.disabled .level-name {
        color: #D1D1D1;
    }

    /*
     * Log Entry
     */

    .stack-content {
        color: #AE0E0E;
        font-family: consolas, Menlo, Courier, monospace;
        white-space: pre-line;
        font-size: .8rem;
    }

    /*
     * Colors: Badge & Infobox
     */

    .badge.text-bg-env,
    .badge.text-bg-level-all,
    .badge.text-bg-level-emergency,
    .badge.text-bg-level-alert,
    .badge.text-bg-level-critical,
    .badge.text-bg-level-error,
    .badge.text-bg-level-warning,
    .badge.text-bg-level-notice,
    .badge.text-bg-level-info,
    .badge.text-bg-level-debug,
    .badge.empty {
        color: #FFF;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
    }

    .badge.text-bg-level-all,
    .box.level-all {
        background-color: {{ log_styler()->color('all') }};
    }

    .badge.text-bg-level-emergency,
    .box.level-emergency {
        background-color: {{ log_styler()->color('emergency') }};
    }

    .badge.text-bg-level-alert,
    .box.level-alert {
        background-color: {{ log_styler()->color('alert') }};
    }

    .badge.text-bg-level-critical,
    .box.level-critical {
        background-color: {{ log_styler()->color('critical') }};
    }

    .badge.text-bg-level-error,
    .box.level-error {
        background-color: {{ log_styler()->color('error') }};
    }

    .badge.text-bg-level-warning,
    .box.level-warning {
        background-color: {{ log_styler()->color('warning') }};
    }

    .badge.text-bg-level-notice,
    .box.level-notice {
        background-color: {{ log_styler()->color('notice') }};
    }

    .badge.text-bg-level-info,
    .box.level-info {
        background-color: {{ log_styler()->color('info') }};
    }

    .badge.text-bg-level-debug,
    .box.level-debug {
        background-color: {{ log_styler()->color('debug') }};
    }

    .badge.empty,
    .box.empty:not(:hover) {
        background-color: {{ log_styler()->color('empty') }};
    }

    .box:hover:not(.empty) {
        background-color: {{ log_styler()->color('empty') }};
    }

    .badge.text-bg-env {
        background-color: #6A1B9A;
    }
    @foreach (log_styler()->extraColors() as $key => $color)
        .badge.text-bg-extra-{{ $key }} {
            background-color: {{ $color }};
        }
    @endforeach

    .table-sm th,
    .table-sm td {
        padding: 0.1rem;
    }

    .btn-group>.btn-group:not(:first-child)>.badge, .btn-group>.badge:not(:first-child) {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .btn-group>.btn-group:not(:last-child)>.badge, .btn-group>.badge:not(:last-child):not(.dropdown-toggle) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .pagination {
        flex-wrap: wrap;
    }
</style>
