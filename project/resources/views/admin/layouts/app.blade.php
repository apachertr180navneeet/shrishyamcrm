<!DOCTYPE html>
<html lang="en" ng-app="{{ config('app.name') }}" lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free">
    <head>
        <meta charset="utf-8" />
        <title>{{ config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
        <meta name="description" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="ws_url" content="{{ env('WS_URL') }}">
        <meta name="user_id" content="{{ Auth::id() }}">
        <link rel="icon" type="image/x-icon" href="{{asset('assets/admin/img/favicon/favicon.ico')}}" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <link rel="stylesheet" href="{{asset('assets/admin/vendor/fonts/boxicons.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/admin/vendor/css/core.css')}}" class="template-customizer-core-css" />
        <link rel="stylesheet" href="{{asset('assets/admin/vendor/css/theme-default.css')}}" class="template-customizer-theme-css" />
        <link rel="stylesheet" href="{{asset('assets/admin/css/demo.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrapDataTable.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="{{asset('assets/admin/vendor/js/helpers.js')}}"></script>
        <script src="{{asset('assets/admin/js/config.js')}}"></script>
        <link rel="stylesheet" href="{{asset('assets/admin/css/sweet-alert.css')}}" />
        @yield('style')
        <style>
            :root {
                --bs-primary: #2563EB;
                --bs-primary-rgb: 37, 99, 235;
                --primary-navy: #1B365D;
                --accent-gold: #D97706;
                --accent-saffron: #EA580C;
                --bg-app: #F8FAFC;
            }
            body {
                font-family: 'Inter', 'Hind', system-ui, -apple-system, sans-serif !important;
                background-color: var(--bg-app) !important;
            }
            .bg-menu-theme {
                background-color: #0F172A !important;
                color: #94A3B8 !important;
            }
            .bg-menu-theme .menu-header {
                color: #64748B !important;
            }
            .bg-menu-theme .menu-link, .bg-menu-theme .menu-horizontal-prev, .bg-menu-theme .menu-horizontal-next {
                color: #CBD5E1 !important;
            }
            .bg-menu-theme .menu-item.active > .menu-link:not(.menu-toggle) {
                background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%) !important;
                color: #FFFFFF !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
            }
            .bg-menu-theme .menu-item:hover:not(.active) > .menu-link {
                background-color: #1E293B !important;
                color: #FFFFFF !important;
            }
            .bg-menu-theme .app-brand {
                background-color: #0B1120 !important;
                border-bottom: 1px solid #1E293B;
            }
            .bg-menu-theme .app-brand .app-brand-text {
                color: #F8FAFC !important;
            }
            .bg-menu-theme .menu-inner-shadow,
            .layout-menu .menu-inner-shadow,
            .menu-inner-shadow {
                background: linear-gradient(#0F172A 41%, rgba(15, 23, 42, 0.11) 95%, rgba(15, 23, 42, 0)) !important;
            }
            .btn-primary {
                background-color: #2563EB !important;
                border-color: #2563EB !important;
            }
            .btn-primary:hover {
                background-color: #1D4ED8 !important;
                border-color: #1D4ED8 !important;
            }
            .text-primary {
                color: #2563EB !important;
            }
            .bg-label-primary {
                background-color: #EFF6FF !important;
                color: #2563EB !important;
            }
            .bg-label-success {
                background-color: #DCFCE7 !important;
                color: #15803D !important;
            }
            .bg-label-warning {
                background-color: #FEF3C7 !important;
                color: #B45309 !important;
            }
            .bg-label-danger {
                background-color: #FEE2E2 !important;
                color: #B91C1C !important;
            }
            .card {
                border-radius: 12px !important;
                border: 1px solid #E2E8F0 !important;
            }
        </style>
        
    </head>
    <body>
       <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                @include('admin.layouts.elements.left_sidebar')
                <div class="layout-page">
                    @include('admin.layouts.elements.header')
                    <div class="content-wrapper">
                        @yield('content')
                        @include('admin.layouts.elements.footer')
                        <div class="content-backdrop fade"></div>
                    </div>
                    @include('admin.layouts.elements.right_sidebar')
                </div>
        
                <script src="{{asset('assets/admin/vendor/libs/jquery/jquery.js')}}"></script>
                <script src="{{asset('assets/admin/vendor/libs/popper/popper.js')}}"></script>
                <script src="{{asset('assets/admin/vendor/js/bootstrap.js')}}"></script>
                <script src="{{asset('assets/admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>
                <script src="{{asset('assets/admin/vendor/js/menu.js')}}"></script>
                <script src="{{asset('assets/admin/vendor/libs/apex-charts/apexcharts.js')}}"></script>
                <script src="{{asset('assets/admin/js/main.js')}}"></script>
                <script src="{{asset('assets/admin/js/dataTable.js')}}"></script>
                <script src="{{asset('assets/admin/js/bootstrapDataTable.js')}}"></script>
                <script src="{{asset('assets/admin/js/dashboards-analytics.js')}}"></script>
                <script src="{{asset('assets/admin/js/moment.min.js')}}"></script>
                <script src="{{asset('assets/admin/js/ssws-dynamic.js')}}"></script>
                <script async defer src="https://buttons.github.io/buttons.js"></script>
                @yield('script')
                @include('admin.layouts.elements.sweet_alerts')
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </body>
</html>