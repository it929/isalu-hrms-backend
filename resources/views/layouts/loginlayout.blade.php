<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Co mpatible" content="IE=edge">
    <?php $url = $_SERVER['HTTP_HOST']; ?>
    <title> SCN-GRP </title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/_all-skins.min.css') }}">
    @yield('styles')
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        var murl = "{{ url('/') }}";
    </script>
</head>

<body class="hold-transition skin-green sidebar-mini">

    <div class=""><!--wrapper-->
        {{-- <header class="main-header">
                <nav class="navbar navbar-static-top" style="background: #0B610B">
                    <div align="center" style="color: #fff; font-size: 26px; padding: 10px;">
                        <b> ISALU HOSPITALS LIMITED</b>
                        <div style="font-size: 18px;">
                            <h2>HRMS</h2>
                        </div>
                        <!--<img src="{{ asset('Images/coat.jpg') }}" height="45" align="left" style="border-radius: 4px;">-->
                    </div>
                </nav>
            </header> --}}

        <header class="main-header custom-header">
            <nav class="navbar navbar-static-top custom-navbar">

                <div class="header-content">
                    <div class="brand-title">
                        ISALU HOSPITALS LIMITED
                    </div>

                    <div class="brand-subtitle">
                        HRMS
                    </div>
                </div>

            </nav>
        </header>

        <div class="">
            <section class="">
                <div class="box box-default">
                    <p>

                        @yield('content')

                    </p>
                </div>
                <div class="row"></div>
            </section>
        </div>

        <footer class="main-footer" style="position: fixed; bottom: 0; width: 100%;">
            <div class="hidden-xs">
                <header class="main-header">
                    <nav class="navbar navbar-static-top" style="background: #0B610B">
                        <div align="center" style="color: #fff; font-size: 15px; padding: 10px;">
                            <!--<img src="{{ asset('Images/coat.jpg') }}" height="30" style="border-radius: 4px;">-->
                            <b>Designed by</b> <a href="#" target="_blank" style="color: white;">TUNDE</a> |
                            <strong>Copyright &copy; <?php echo date('Y'); ?> .</strong> All rights reserved.
                            <!--<img src="{{ asset('Images/coat.jpg') }}" height="30" style="border-radius: 4px;">-->
                        </div>
                    </nav>
                </header>
            </div>
        </footer>
    </div>
    @yield('scripts')
</body>

</html>
