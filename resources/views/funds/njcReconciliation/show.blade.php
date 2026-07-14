<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>NJC::Treasury Cash Book</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/StyleInput.min.css') }}">
    <script>
        var murl = "{{ url('/') }}";
    </script>
</head>

<body>

    <style type="text/css">
        .table td {
            border: #9f9f9f solid 1px !important;
            font-size: 11px !important;
            padding: 1px !important;
        }

        .table th {
            border: #9f9f9f solid 1px !important;
            font-size: 11px !important;
            padding: 1px !important;
        }

        .table tr th td {
            width: auto !important;

        }
    </style>

    <div class="box-body hidden-print">
        <div class="row">
            <div class="col-md-12">
                @if (count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong>
                        {{ session('msg') }}
                    </div>
                @endif

                @if (session('err'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                        <strong>Input Error !</strong>
                        {{ session('err') }}
                    </div>
                @endif
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->


    <div class="col-md-12 hidden-print">
        <div class="row">
            <div>
                <h3 class="text-success text-center"><b>ISALU HOSPITAL LIMITED</b></h3>
            </div>
            <div>
                <h4 class="text-success text-center"><b>ISALU HOSPITAL LIMITED,THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</b></h4>
            </div>
        </div>
    </div>

    @if ($reportType == 1)
        @include('funds.njcReconciliation.partial_report.splitReport')
    @else
        @include('funds.njcReconciliation.partial_report.viewAllReport')
    @endif

    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
</body>

</html>
