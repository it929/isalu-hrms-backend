<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title></title>

  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/AdminLTE.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/_all-skins.min.css') }}">
  <style type="text/css">
    .table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td
    {
   border: 1px solid #000;
  }
  body {
    color: #000;
    font-size: 16px;
    font-weight: bolder;
  }

  </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <div id="page-wrapper">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <table width="1122" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td colspan="3"><div align="center">
                <h2>ISALU HOSPITAL LIMITED<br />


                  NEW SALARY STRUCTURE</h2>
                  <h3>
                    GRADE LEVEL

                     {{$current_grade}}

                  </h3>
                </div></td>
              </tr>
            </table><br>
            <table border="1" class="table table-bordered table-hover">
              <tr>
                <th class="text-center">Grade</th>
                <th class="text-center">Step</th>
                <th class="text-center">Basic</th>

                <th class="text-center">JUSU</th>


                <th class="text-center">Tax</th>

                <th class="text-center">Pension</th>

                <th class="text-center">NHF</th>
                <th class="text-center">Union Dues</th>

              </tr>
              @foreach ($report as $reports)
              <tr>
                <td>{{ $reports->grade }}</td>
                <td>{{ $reports->step }}</td>
                <td align="right">{{ number_format($reports->amount, 2, '.', ',') }}</td>

                <td align="right">{{ number_format($reports->peculiar, 2, '.', ',') }}</td>

                <td align="right">{{ number_format($reports->tax, 2, '.', ',') }}</td>
                <td align="right">{{ number_format($reports->pension, 2, '.', ',') }}</td>
                <td align="right">{{ number_format($reports->nhf, 2, '.', ',') }}</td>
                <td align="right">{{ number_format($reports->unionDues, 2, '.', ',')  }}</td>







              </tr>
              @endforeach
            </table>
            <div class="no-print">
            <a href = "{{ url('/salaryScale') }}"> <strong> HOME </strong></a>  <Bold> Select a grade:</Bold></br>
            {{$results->links()}}
          </div>
          </div>
        </div>
      </div>
    </div>
    <!-- jQuery 2.2.0 -->
    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <!-- Bootstrap 3.3.6 -->
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <!-- <script src="../../plugins/slimScroll/jquery.slimscroll.min.js"></script> -->
    <!-- FastClick -->
    <!-- <script src="../../plugins/fastclick/fastclick.js"></script> -->
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('assets/js/demo.js') }}"></script>
  </body>
  </html>
