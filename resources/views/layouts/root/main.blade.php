<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>BODY</title>

    <!-- General CSS Files -->
    <link rel="manifest" href={{ asset('manifest.json') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/fontawesome/css/all.min.css') }}>
    <link rel="stylesheet"
        href={{ asset('assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/select2/dist/css/select2.min.css') }}>

    <!-- CSS Libraries -->
    <link rel="stylesheet" href={{ asset('assets/modules/jqvmap/dist/jqvmap.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/summernote/summernote-bs4.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/izitoast/css/iziToast.min.css') }}>

    <!-- Template CSS -->
    <link rel="stylesheet" href={{ asset('assets/css/style.css') }}>
    <link rel="stylesheet" href={{ asset('assets/css/components.css') }}>
    <script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
    <!-- Start GA -->
    <!-- /END GA -->
</head>

<body class="sidebar-mini">
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>

            {{-- navbar --}}
            @include('layouts.partials.nav')

            {{-- sidenav --}}
            @include('layouts.partials.sidenav')

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">

                    @yield('main')

                </section>
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; ITD 2023
                </div>
                <div class="footer-right">

                </div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src={{ asset('assets/modules/jquery.min.js') }}></script>
    <script src={{ asset('assets/modules/popper.js') }}></script>
    <script src={{ asset('assets/modules/tooltip.js') }}></script>
    <script src={{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}></script>
    <script src={{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}></script>
    <script src={{ asset('assets/modules/moment.min.js') }}></script>
    <script src={{ asset('assets/js/stisla.js') }}></script>
    <script src={{ asset('assets/modules/datatables/datatables.min.js') }}></script>

    @yield('custom-script')

    <!-- JS Libraies -->
    <script src={{ asset('assets/modules/jquery.sparkline.min.js') }}></script>
    <script src={{ asset('assets/modules/chart.min.js') }}></script>
    <script src={{ asset('assets/modules/owlcarousel2/dist/owl.carousel.min.js') }}></script>
    <script src={{ asset('assets/modules/summernote/summernote-bs4.js') }}></script>
    <script src={{ asset('assets/modules/chocolat/dist/js/jquery.chocolat.min.js') }}></script>
    <script src={{ asset('dist/assets/select2/dist/js/select2.full.min.js') }}></script>

    <!-- Page Specific JS File -->
    <script src={{ asset('assets/js/page/index.js') }}></script>
    <script src={{ asset('assets/js/page/bootstrap-modal.js') }}></script>
    <script src={{ asset('assets/modules/izitoast/js/iziToast.min.js') }}></script>

    <!-- Template JS File -->
    <script src={{ asset('assets/js/scripts.js') }}></script>
    <script src={{ asset('assets/js/custom.js') }}></script>

    @stack('scripts')

</body>

</html>
