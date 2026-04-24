<!DOCTYPE html>
<html>

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <?php $url = $_SERVER['HTTP_HOST']; ?>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            SCN-GRP System
        </title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="stylesheet" href="{{ asset('/assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/assets/font-awesome/css/font-awesome.min.css') }}">
        @yield('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
        <link rel="stylesheet" href="{{ asset('/assets/css/AdminLTE.min.css') }}">
        <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="{{ asset('/assets/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/assets/css/_all-skins.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/assets/css/admin.css') }}">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css" />
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/selectize.js"></script>

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        {{-- <link rel="stylesheet" href="/resources/demos/style.css"> --}}


        <style type="text/css">
            table th {
                width: auto !important;

            }



            @media print {
                .noprint {
                    display: none;
                }
            }

            @media screen {}
        </style>
        <script type="text/javascript" src="{{ asset('/assets/js/number_to_word.js') }}"></script>

        <script>
            var murl = "{{ url('/') }}";
        </script>


        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @yield('style')

    </head>

    {{-- <body class="hold-transition skin-green sidebar-mini" onload="lookup();"> --}}
    <body class="hold-transition skin-green sidebar-mini" onload="lookup();">
        {{-- @include('dueForArrears._incrementAlert') --}}
        <div class="wrapper">

            <header class="main-header hidden-print" >
                <!-- Logo -->
                <a href="{{ url('/') }}" class="logo" >
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini">
                         HRMS
                    </span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><b>
                             HRMS
                        </b></span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" >
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>

                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- End of other dropdown notification -->
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="user">
                                <span>
                                    @if (!Auth::guest())
                                        {{-- {{ session::get('division') }} --}}
                                    @endif
                                </span>
                                </a>
                            </li>


                            <li class="user">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    {{-- <span>{{ Session::get('courtName') }}</span> --}}
                                </a>
                            </li>


                            <li class="dropdown user">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-user"></i>
                                    <span class="hidden-xs">
                                        @if (!Auth::guest())
                                            {{ Auth::user()->name }}
                                        @endif
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-user">
                                    <li><a href="{{ url('/user/editAccount') }}"><i class="fa fa-circle-o"></i> Edit
                                            Account</a></li>
                                    <li><a href="#"><i class="fa fa-circle-o"></i>
                                            {{ $divisionName ?? 'Default Division' }}
                                            </a></li>
                                    <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a>
                                    </li>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            @php
                use Illuminate\Support\Facades\Hash;
                $user = auth()->user();
            @endphp
            @if (!(Hash::check('12345', $user->password) && $user->user_type != 'Technical'))
                <aside class="main-sidebar hidden-print">
                    <section class="sidebar">
                        <div class="user-panel">
                            <div class="pull-left image">
                                <!--<img src="http://njc.gov.ng/webFiles/images/njc-logo2.jpg" class="img-circle" alt="User Image">-->
                            </div>
                            <div class="text-center" style="color: white">
                                <p>@include('MasterRolePermission.layout.getUserRoleName')</p>
                                <a href="#"><i class="fa fa-circle text-success"></i>online</a>
                            </div>
                        </div>
                        <!-- Dynamic User Route for user -->
                        @include('MasterRolePermission.layout.userRouteLink')
                        <!-- Technical Admin Route only -->
                        @include('MasterRolePermission.layout.adminRouteLink')
                    </section>
                </aside>
            @endauth

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <section class="content">

                    @yield('content')

                </section>
            </div>

            <!-- /.content -->

            <!-- /.content-wrapper -->
            <footer class="main-footer hidden-print">
                <div class="pull-right hidden-xs">
                    <b>Designed by</b> <a href="http://isalu.com">ISALU</a>
                </div>
                <strong>Copyright &copy; <?php echo date('Y'); ?> .</strong> All rights
                reserved.
            </footer>
    </div>
    <!-- ./wrapper -->
    <script src="{{ asset('/assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('/assets/js/demo.js') }}"></script>
    <script src="{{ asset('/assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>


    <script type="text/javascript">
        $('#input-tags2').selectize({
            plugins: ['restore_on_backspace'],
            delimiter: ',',
            persist: false,
            create: function(input) {
                return {
                    value: input,
                    text: input
                }
            }
        });

        function editfunc(a, b, c, d) {
            $(document).ready(function() {
                $('#bank').val(a);
                $('#Bankcode').val(b);
                $('#bankID').val(c);
                $("#editModal").modal('show');
            });
        }
    </script>

    @yield('scripts')
    <!-- <script src="{{ asset('assets/js/jquery.cookie.js') }}"></script> -->
</body>

</html>
