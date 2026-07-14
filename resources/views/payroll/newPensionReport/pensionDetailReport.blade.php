@extends('layouts.layout')
@section('pageTitle')
    {{ strtoupper('ALL Staff Pension Records') }}
@endsection

<style>
    @media print {

        .no-print,
        .no-print * {
            display: none !important;
        }
    }
</style>

@section('content')
    <div class="box box-body" style="background: white; padding: 5px 20px 0 0;">
        <div class="col-md-12 hidden-print">
            <h5><b>@yield('pageTitle') <span id='processing'></span></b></h5>
            <hr />
        </div>

        <div class="col-md-12"><!--1st col-->
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
                    <p>{{ session('msg') }}</p>
                </div>
            @endif
            @if (session('err'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Allowed ! </strong>
                    <p>{{ session('err') }}</p>
                </div>
            @endif
        </div>


        <div id="pensionSummaryPrint">
            <div align="center" class="text-success">
                <h3><b>ISALU HOSPITAL LIMITED</b></h3>
                <h4><b>PENSION SUMMARY FOR {{ strtoupper($division[0]->division) }}, FOR {{ $month }}
                        {{ $year }}</b></h4>
                {{-- <h5><b>{{ strtoupper($nameOfPFA) }}</b></h5> --}}
            </div>

            <p class="pull-right no-print" style="margin-right: 30px;">Printed On:
                {{ date_format(date_create(date('Y-m-d')), 'dS l F, Y') }}.</p>
            <br />

            <div class="row" style="margin: 0 10px;">
                <div class="col-md-12" id="tableData">
                    <table class="table table-striped table-condensed table-bordered input-sm">
                        <thead>
                            <th>S/N</th>
                            <th>File No.</th>
                            <th>NAME OF EMPLOYEE</th>
                            {{-- <th>DESIGNATION</th> --}}
                            <th>DATE OF BIRTH</th>
                            <th>DATE OF 1ST APPT.</th>
                            <th>DATE OF PRESENT APPT.</th>
                            <th>GL/STEP</th>
                            <th>EMPLOYEE (8%) &#8358;</th>
                            <th>EMPLOYER (10%) &#8358;</th>
                            <th>TOTAL &#8358;</th>
                        </thead>
                        <tbody class="input-smm">
                            @php
                                $key = 1;
                                $employee_8percent = 0;
                                $basicPlusAllowance = 0;
                                $employer_10percent = 0;
                                $sumAllEmployeePer = 0;
                                $sumAllEmployerPer = 0;
                                $sumAllEmployerEmployeePer = 0;
                            @endphp
                            @foreach ($pensionReport as $user)
                                @php
                                    $employee_8percent = $user->PEN;
                                    $basicPlusAllowance = substr(
                                        ($employee_8percent * 100) / 8,
                                        0,
                                        strpos(($employee_8percent * 100) / 8, '.') + 12,
                                    );
                                    $employer_10percent = $basicPlusAllowance * 0.1;
                                @endphp
                                <tr>
                                    <td>{{ $key++ }}</td>
                                    <td>{{ $user->fileNo }}</td>
                                    <td>{{ strtoupper($user->surname . ' ' . $user->first_name . ' ' . $user->othernames) }}
                                    </td>

                                    <td>{{ formatDate($user->dob) }}</td>
                                    <td>{{ formatDate($user->appointment_date) }}</td>
                                    <td>{{ formatDate($user->incremental_date) }}</td>
                                    <td>{{ strtoupper('GL ' . $user->grade . ' STEP ' . $user->step) }}</td>
                                    <td @php $sumAllEmployeePer += $user->PEN @endphp> <strong>
                                            {{ number_format($user->PEN, 2, '.', ',') }}</strong></td>
                                    <td @php $sumAllEmployerPer += $employer_10percent @endphp><strong>
                                            {{ number_format($employer_10percent, 2, '.', ',') }}</strong></td>
                                    <td @php $sumAllEmployerEmployeePer += ($user->PEN + $employer_10percent) @endphp>
                                        <strong>
                                            {{ number_format($user->PEN + $employer_10percent, 2, '.', ',') }}</strong>
                                    </td>

                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="7"> <strong>TOTAL</strong> </td>
                                <td><strong>{{ number_format($sumAllEmployeePer, 2) }}</strong></td>
                                <td><strong>{{ number_format($sumAllEmployerPer, 2) }}</strong></td>
                                <td><strong>{{ number_format($sumAllEmployerEmployeePer) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <div class="no-print hidden-print" align="center">
                                        <input type="button" class="btn btn-primary hidden-print" id="btnExport"
                                            value="Export to Excel" onclick="Export()" />
                                        <button class="btn btn-success print-window">Print</button>
                                    </div>

                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
        <div class="row hidden-print">
            <div class="col-md-12">
                <div class="col-md-3">
                    <div align="left" class="form-group">
                        <label for="month">&nbsp;</label><br />
                        <a href="{{ url('/pension-deduction-report') }}" title="Back" class="btn btn-warning"><i
                                class="fa fa-arrow-circle-left"></i> Back </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $('#searchName').attr("disabled", true);
            $("#autocomplete").autocomplete({
                serviceUrl: murl + '/data/searchUser',
                minLength: 2,
                onSelect: function(suggestion) {
                    $('#fileNo').val(suggestion.data);
                    $('#searchName').attr("disabled", false);
                    //showAll();
                }
            });
        });
    </script>
    <script type="text/javascript">
        $('.print-window').click(function() {
            // var element = document.getElementById("contain1");
            // element.classList.remove("panel");
            window.print();
            // element.classList.add("panel");
        });

        function Export() {
            $("#tableData").table2excel({
                filename: "{{ $division[0]->division }}_{{ $month }}_{{ $year }}_MONTLY_PENSION.xls"
            });
        }
    </script>
@endsection

@section('styles')
    <style type="text/css">
        .table,
        tr,
        th,
        td {
            border: #030303 solid 1px !important;
            font-size: 10px !important;
        }
    </style>
@stop
