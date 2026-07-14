@extends('layouts.layout')

@section('pageTitle')
    Month Control Variable
@endsection

<style type="text/css">
    .style25 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        color: #FF0000;
    }

    a:link {
        text-decoration: none;
    }

    a:visited {
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    a:active {
        text-decoration: none;
    }

    body {
        /*background-image: url({{ asset('Images/watermark.jpg') }});*/
    }

    .tblborder {
        border-top-width: 1px;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-left-width: 1px;
        border-top-style: dotted;
        border-right-style: dotted;
        border-bottom-style: dotted;
        border-left-style: dotted;
    }

    .FED {
        color: #008000;
    }

    body,
    td,
    th {
        font-size: 15px;
        font-family: Verdana, Geneva, sans-serif;
    }

    -->table tr th {
        line-height: 35px;
        font-size: 14px;
    }
</style>
@section('content')


    {{-- <div class="box box-default" style="border: none;">
        <div class="box-body box-profile" style="margin:0 5px;">
            <form method="POST" action="{{ url('/monthly-control-variable') }}">
                {{ csrf_field() }}

                <div class="col-md-12 hidden-print">
                    @if (!empty($errors) && count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Error!</strong>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('msg'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Success!</strong> {{ session('msg') }}
                        </div>
                    @endif
                    @if (session('err'))
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Operation Error !<br></strong> {{ session('err') }}
                        </div>
                    @endif
                </div>

                <p>
                <h2 class="text-success text-center">
                    <strong>MONTH CONTROL VARIABLE</strong>
                </h2>
                </p>

                <div class="row">
                    <div class="col-sm-12">

                        <div style="margin: 0px  5%;">
                            <div class="form-group" style="margin-bottom: 5%;">

                                <div class="col-sm-12 row">
                                    @if (auth::user()->is_global && $division != '')
                                        <div class="col-sm-4">
                                            <label class="control-label">Division</label>
                                            <select class="form-control" name="division" id="divisions">
                                                <option value=""> -All Division- </option>
                                                @foreach ($division as $div)
                                                    @if ($div->divisionID == session('selected_division'))
                                                        <option value="{{ $div->divisionID }}" selected="selected">
                                                            {{ $div->division }}</option>
                                                    @else
                                                        <option value="{{ $div->divisionID }}">{{ $div->division }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label>Division</label>
                                                <input type="text" readonly class="form-control" name="division"
                                                    value="{{ $loggedDivision->division }}">
                                            </div>
                                        </div>
                                        <input type="hidden" id="putDivisionInSession" name="division"
                                            value="{{ $loggedDivision->divisionID }}">
                                    @endif

                                    <div class="col-sm-4">
                                        <label class="control-label">Control variable</label>
                                        <select class="form-control" name="controlvariable" id="controlvariable">
                                            <option value=""> --Select-- </option>
                                            <option value="1"
                                                {{ isset($EorDSession[0]) && $EorDSession[0]->particularID == 1 ? 'selected' : '' }}>

                                                Earning</option>
                                            <option value="2"
                                                {{ isset($EorDSession[0]) && $EorDSession[0]->particularID == 2 ? 'selected' : '' }}>
                                                Deduction</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="control-label">
                                            @if ($EorDSession != '')
                                                @if ($EorDSession[0]->particularID == 1)
                                                    {{ 'Select Type of Earning' }}
                                                @else
                                                    {{ 'Select Type of Deduction' }}
                                                @endif
                                            @endif
                                        </label>
                                        <select class="form-control" name="earnordeduction" id="earnordeduction">
                                            <option value=""> --Select-- </option>
                                            @if ($EorDSession != '')
                                                @foreach ($EorDSession as $ed)
                                                    @if ($ed->ID == $edses)
                                                        <option value="{{ $ed->ID }}" selected="selected">
                                                            {{ $ed->description }}</option>
                                                    @else
                                                        <option value="{{ $ed->ID }}"> {{ $ed->description }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @else
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <label>Select a Year</label>

                                        <select name="year" id="section" class="form-control">

                                            <option value="">Select Year</option>
                                            @for ($i = 2025; $i <= 2040; $i++)
                                                <option value="{{ $i }}"
                                                    @if ($activeMonth !== '' && $activeMonth->year == $i) selected @elseif($year == $i) selected @endif>
                                                    {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-sm-4 mb-3">
                                        <label> Select a Month </label>
                                        <select name="month" id="section" class="form-control input-sm">

                                            <option value="">Select Month </option>

                                            <option value="JANUARY"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'JANUARY') selected @elseif ($month == 'JANUARY') selected @endif>
                                                January</option>
                                            <option value="FEBRUARY"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'FEBRUARY') selected @elseif($month == 'FEBRUARY') selected @endif>
                                                February</option>
                                            <option value="MARCH"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'MARCH') selected @elseif ($month == 'MARCH') selected @endif>
                                                March</option>
                                            <option value="APRIL"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'APRIL') selected @elseif ($month == 'APRIL') selected @endif>
                                                April</option>
                                            <option value="MAY"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'MAY') selected @elseif ($month == 'MAY') selected @endif>
                                                May</option>
                                            <option value="JUNE"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'JUNE') selected @elseif ($month == 'JUNE') selected @endif>
                                                June</option>
                                            <option value="JULY"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'JULY') selected @elseif ($month == 'JULY') selected @endif>
                                                July</option>
                                            <option value="AUGUST"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'AUGUST') selected @elseif ($month == 'AUGUST') selected @endif>
                                                August</option>
                                            <option value="SEPTEMBER"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'SEPTEMBER') selected @elseif ($month == 'SEPTEMBER') selected @endif>
                                                September</option>
                                            <option value="OCTOBER"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'OCTOBER') selected @elseif ($month == 'OCTOBER') selected @endif>
                                                October</option>
                                            <option value="NOVEMBER"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'NOVEMBER') selected @elseif ($month == 'NOVEMBER') selected @endif>
                                                November</option>
                                            <option value="DECEMBER"
                                                @if ($activeMonth !== '' && $activeMonth->month == 'DECEMBER') selected @elseif ($month == 'DECEMBER') selected @endif>
                                                December</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <button type="submit" name="" class="btn btn-success"> <i
                                                class="fa fa-save"></i>
                                            Search
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div> --}}

    <div class="panel panel-default">
        <div class="panel-heading text-center">
            <h3 class="panel-title text-success"><strong>MONTH CONTROL VARIABLE</strong></h3>
        </div>

        <div class="panel-body">
            <form method="POST" action="{{ url('/monthly-control-variable') }}">
                {{ csrf_field() }}

                {{-- Alert Messages --}}
                <div class="col-md-12 hidden-print">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Error!</strong>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('msg'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong> {{ session('msg') }}
                        </div>
                    @endif

                    @if (session('err'))
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Operation Error!</strong> {{ session('err') }}
                        </div>
                    @endif
                </div>

                {{-- Form Inputs --}}
                <div class="row" style="margin-top: 15px;">
                    {{-- ===== First Row (3 inputs) ===== --}}
                    @if (auth::user()->is_global && $division != '')
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Division</label>
                                <select class="form-control" name="division" id="divisions">
                                    <option value=""> -All Division- </option>
                                    @foreach ($division as $div)
                                        <option value="{{ $div->divisionID }}"
                                            {{ $div->divisionID == session('selected_division') ? 'selected' : '' }}>
                                            {{ $div->division }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Division</label>
                                <input type="text" readonly class="form-control" value="{{ $loggedDivision->division }}">
                                <input type="hidden" name="division" value="{{ $loggedDivision->divisionID }}">
                            </div>
                        </div>
                    @endif

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Control Variable</label>
                            <select class="form-control" name="controlvariable" id="controlvariable">
                                <option value="">-- Select --</option>
                                <option value="1"
                                    {{ isset($EorDSession[0]) && $EorDSession[0]->particularID == 1 ? 'selected' : '' }}>
                                    Earning
                                </option>
                                <option value="2"
                                    {{ isset($EorDSession[0]) && $EorDSession[0]->particularID == 2 ? 'selected' : '' }}>
                                    Deduction
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>
                                @if ($EorDSession != '')
                                    {{ $EorDSession[0]->particularID == 1 ? 'Select Type of Earning' : 'Select Type of Deduction' }}
                                @else
                                    Select Type
                                @endif
                            </label>
                            <select class="form-control" name="earnordeduction" id="earnordeduction">
                                <option value="">-- Select --</option>
                                @if ($EorDSession != '')
                                    @foreach ($EorDSession as $ed)
                                        <option value="{{ $ed->ID }}" {{ $ed->ID == $edses ? 'selected' : '' }}>
                                            {{ $ed->description }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ===== Second Row (2 inputs + button) ===== --}}
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Select Year</label>
                            <select name="year" class="form-control">
                                <option value="">Select Year</option>
                                @for ($i = 2025; $i <= 2040; $i++)
                                    <option value="{{ $i }}" @if (($activeMonth && $activeMonth->year == $i) || $year == $i) selected @endif>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Select Month</label>
                            <select name="month" class="form-control">
                                <option value="">Select Month</option>
                                @foreach (['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'] as $m)
                                    <option value="{{ $m }}" @if (($activeMonth && $activeMonth->month == $m) || $month == $m) selected @endif>
                                        {{ ucfirst(strtolower($m)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-4 text-center" style="margin-top: 25px;">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>






    <div class="box box-default" style="border: none;">
        <div class="box-body box-profile" style="margin:0 5px;">
            <div class="box" id="tableData">
                {{-- Debug --}}

                @if (!empty($monthControlVariables) && count($monthControlVariables) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th colspan="4">
                                    <h2 class="text-success">

                                        <strong>ISALU HOSPITAL LIMITED</strong>
                                    </h2>
                                </th>
                            </tr>
                        </thead>
                        <tr>

                            @if ($mydivision)
                                <th colspan="4">
                                    <h5>
                                        {{-- MONTHLY CONTROL VARIABLE FOR THE MONTH OF {{ $month }}, {{ $year }} --}}
                                        {{ $mycontrolvariable }} FOR THE MONTH OF {{ $month }},
                                        {{ $year }}
                                        -
                                        {{ strtoupper($mydivision) }} DIVISION
                                    </h5>
                                </th>
                            @else
                                <th colspan="4">
                                    <h5>
                                        {{-- MONTHLY CONTROL VARIABLE FOR THE MONTH OF {{ $month }}, {{ $year }} --}}
                                        {{ $mycontrolvariable }} FOR THE MONTH OF {{ $month }},
                                        {{ $year }}
                                        -
                                        ALL DIVISION
                                    </h5>
                                </th>
                            @endif
                        </tr>
                    </table>
                @else
                @endif
                <br>
                <table class="table table-bordered table-striped table-highlight">
                    <thead>
                        <tr bgcolor="#c7c7c7">

                            <th> S/N</th>
                            <th> Name</th>
                            <th>Division</th>
                            <th>Amount</th>

                        </tr>
                    </thead>
                    @php $i=1;@endphp

                    <?php
                    $counter = session('serialNo');
                    $sum = 0;
                    ?>
                    <?php
                    $subTotal = 0;
                    $divID = '';
                    ?>

                    @if (!empty($monthControlVariables) && count($monthControlVariables) > 0)
                        @foreach ($monthControlVariables as $con)
                            @if ($divID != $con->divisionID && $divID != '')
                                <tr>
                                    <td colspan="3" class="tblborder"><strong> Sub Total: </strong> </td>
                                    <td colspan="1" class="tblborder"><strong>
                                            {{ number_format($subTotal, 2) }} </strong></td>
                                </tr>
                                <?php
                                $subTotal = 0;
                                ?>
                            @endif

                            @php
                                $divID = $con->divisionID;
                                $subTotal += $con->amount;
                            @endphp

                            <tr>
                                <td>{{ $counter }}</td>
                                <td>{{ $con->surname }} {{ $con->first_name }} {{ $con->othernames }}</td>
                                {{-- <td>{{ $con->division }}</td> --}}
                                <td>{{ $con->division ?? 'N/A' }}</td>
                                <td>{{ number_format($con->amount, '2', '.', ',') }}</td>

                            </tr>
                            <?php $counter = $counter + 1; ?>

                            <?php $sum = $sum + $con->amount; ?>
                        @endforeach
                        {{-- <tr>
                            <td style="font-weight:bold;">TOTAL</span></td>
                            <td></td>
                            <td style="font-weight:bold;">{{ $grossTotal }}</td>
                        </tr> --}}
                        @if ($divID != '')
                            <tr class="tblborder">

                                <td colspan="3" class="tblborder"><strong> Sub Total:</strong> </td>
                                <td colspan="1" class="tblborder"><strong> {{ number_format($subTotal, 2) }}
                                    </strong></td>
                            </tr>
                            <?php $subTotal = 0; ?>
                        @endif

                        <tr class="tblborder">
                            <td class="tblborder" colspan="3"><strong>Total</strong></td>
                            {{-- <td class="tblborder" align="right"><strong> {{ number_format($sum, 2, '.', ',') }} --}}
                            <td class="tblborder" colspan="1"><strong> {{ number_format($sum, 2, '.', ',') }}
                                </strong></td>
                            <!--<td class="tblborder" colspan="1"></td>-->
                        </tr>

                        <tr class="tblborder">
                            <td class="tblborder" colspan="8"></td>
                        </tr>
                    @else
                        <tr class="text-center">
                            <td colspan="4" class="text-danger">No Result found!...</td>
                        </tr>
                    @endif

                </table>
                <hr />
            </div>
            <table class="table tblborder hidden-print" border="1" align="left" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="3">
                        <div class="no-print hidden-print" align="center">
                            <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                                onclick="Export()" />
                        </div>

                    </td>
                </tr>
            </table>
        </div>
    </div>

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <style type="text/css">
        .autocomplete-suggestions {

            background-color: #eee !IMPORTANT;
            border: 1px solid #c3c3c3 !important;
            padding: 1px 5px !important;
            cursor: Pointer !important;
            overflow: scroll;

        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.autocomplete.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>
    <script type="text/javascript">
        (function() {
            $('#court').change(function() {
                var court = $(this).val();
                var check = 'court';
                $('#processing').text('Processing. Please wait...');


                $.ajax({
                    url: murl + '/account-info/court',
                    type: "post",
                    data: {
                        'courtID': court,
                        'check': check,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        location.reload(true);
                    }
                })
            });
        })();
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            let divSession = $('#putDivisionInSession').val()
            var check = 'division';
            if (divSession) {
                console.log("my div session", divSession)
                $.ajax({
                    url: murl + '/account-info/court',
                    type: "post",
                    data: {
                        'divisionID': divSession,
                        'check': check,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        // location.reload(true);
                        // console.log(data);
                    }
                });
            } else {
                console.log("you have empy division session")
            }

            $('#divisions').change(function() {
                var division = $(this).val();
                var check = 'division';
                // console.log(division);

                $.ajax({
                    url: murl + '/account-info/court',
                    type: "post",
                    data: {
                        'divisionID': division,
                        'check': check,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        location.reload(true);
                        console.log(data);
                    }
                });
            });

            $('#controlvariable').change(function() {
                var controlV = $(this).val();
                console.log(controlV);
                $.ajax({
                    url: murl + '/get-earnordeduction',
                    type: "GET",
                    data: {
                        'controlvariable': controlV,
                        // '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        location.reload(true);
                        console.log(data);
                    }
                });
            });


            $('#earnordeduction').change(function() {
                var ed = $(this).val();
                // console.log(ed);
                $.ajax({
                    url: murl + '/set-current-ed',
                    type: "GET",
                    data: {
                        'ed': ed,
                        // '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        // location.reload(true);
                        // console.log(data);
                    }
                });
            });

        });
    </script>

    <script type="text/javascript">
        $(function() {

            $("#autocomplete").autocomplete({
                serviceUrl: murl + '/account-info/get-staff',
                minLength: 2,
                onSelect: function(suggestion) {
                    $('#fileNo').val(suggestion.data);
                    $('#searchName').attr("disabled", false);
                    showAll();
                }
            });
        });
    </script>

    <script type="text/javascript">
        function Export() {
            $("#tableData").table2excel({
                filename: "{{ $month }}_{{ $year }}_MONTLY_CV.xls"
            });
        }
    </script>
@endsection

@endsection
