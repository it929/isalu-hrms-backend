@extends('layouts.layout')
@section('pageTitle')
@endsection

@section('content')
    <div class="box box-default" style= "border:none;">
        <div class="box-header with-border hidden-print">
            <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
        </div>
        @if ($warning != '')
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $warning }}</strong>
            </div>
        @endif
        @if ($success != '')
            <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $success }}</strong>
            </div>
        @endif
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

        <div class="box-body">
            <div class="row">
                <div class="col-xs-2"><img src="{{ asset('assets/image-logo/njc-logo.jpg') }}"
                        class="img-responsive responsive" style="width:120px; height:120px;"></div>
                {{-- <div class="col-xs-2"><img src="{{ asset('assets/image-logo/scn_logo.png') }}" class="img-responsive responsive"
                        style="width:120px; height:120px;"></div> --}}
                <div class="col-xs-8">
                    <div>
                        <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                        <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong>
                        </h4>
                        <h4 class="text-center text-success"><strong>Monthly Allocation Recieved</strong></h4>
                    </div>
                </div>
                <div class="col-xs-2"><img src="{{ asset('assets/image-logo/coat.jpg') }}"
                        style="width:140px; height:120px;" class="responsive"></div>
            </div>

            <form method="post" id="thisform1" name="thisform1">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="row hidden-print">
                        <div class="col-md-2">
                            <label>Period</label>
                            <?php if (old('period') != '') {
                                $period = old('period');
                            } ?>
                            <select name="period" id="period" class="form-control" onchange ="ReloadForm();">
                                <option value="" selected>-Select Year-</option>
                                @foreach ($YearPeriod as $b)
                                    <option value="{{ $b->Period }}" {{ $period == $b->Period ? 'selected' : '' }}>
                                        {{ $b->Period }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Budget Type</label>
                            <select class="form-control" id="economicGroup" name="economicGroup" onchange="ReloadForm()"
                                required="">
                                <option value="">Choose One</option>
                                @foreach ($EconomicGroup as $list)
                                    <option value="{{ $list->ID }}"
                                        {{ $economicGroup == $list->ID ? 'selected' : '' }}>
                                        {{ $list->contractType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Month</label>
                            <select class="form-control" id="month" name="month" onchange="ReloadForm()"
                                required="">
                                <option value=""> Choose One</option>
                                <option value="january" {{ $month == 'january' ? 'selected' : '' }}> January</option>
                                <option value="february" {{ $month == 'february' ? 'selected' : '' }}> February</option>
                                <option value="march" {{ $month == 'march' ? 'selected' : '' }}> March</option>
                                <option value="april" {{ $month == 'april' ? 'selected' : '' }}> April</option>
                                <option value="may" {{ $month == 'may' ? 'selected' : '' }}> May</option>
                                <option value="june" {{ $month == 'june' ? 'selected' : '' }}> June</option>
                                <option value="july" {{ $month == 'july' ? 'selected' : '' }}> July</option>
                                <option value="august" {{ $month == 'august' ? 'selected' : '' }}> August</option>
                                <option value="september" {{ $month == 'september' ? 'selected' : '' }}> September</option>
                                <option value="october" {{ $month == 'october' ? 'selected' : '' }}> October</option>
                                <option value="november" {{ $month == 'november' ? 'selected' : '' }}> November</option>
                                <option value="december" {{ $month == 'december' ? 'selected' : '' }}> December</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Total Received</label>
                            <input type="text" name="amount" class="form-control" value="{{ $amount }}"
                                placeholder="">
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control"> </textarea>
                        </div>
                        <div class="col-md-2">
                            <br>
                            <button type="submit" class="btn btn-success" name="update">
                                <i class="fa fa-btn fa-floppy-o"></i> Add New
                            </button>
                        </div>
                    </div>
                    <input id ="delcode" type="hidden" name="delcode">

                    <div class="table-responsive" style="font-size: 12px; padding:10px;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Year</th>
                                    <th>Month</th>
                                    <th>Remarks</th>
                                    <th>Balance BF </th>
                                    <th>Total Received </th>
                                    <th>Total Alloted</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            @php
                                $serialNum = 1;
                                $bf = 0;
                                $tr = 0;
                                $ta = 0;
                            @endphp

                            @foreach ($QReport as $b)
                                <tr>
                                    <td>{{ $serialNum++ }} </td>

                                    <td>{{ $b->year }}</td>
                                    <td style="text-transform:capitalize;">{{ $b->month }}</td>
                                    <td style="text-transform:capitalize;">{{ $b->remarks }}</td>
                                    <td style="text-align:right;">{{ number_format($bf, 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($b->amount, 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($b->Allotted, 2) }}</td>
                                    @php
                                        $bf = $bf + $b->amount - $b->Allotted;
                                        $tr += $b->amount;
                                        $ta += $b->Allotted;
                                    @endphp
                                    <td style="text-align:right;">{{ number_format($bf, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td> </td>

                                <td></td>
                                <td style="text-transform:capitalize;">Total</td>
                                <td style="text-transform:capitalize;"></td>
                                <td style="text-align:right;"></td>
                                <td style="text-align:right;">{{ number_format($tr, 2) }}</td>
                                <td style="text-align:right;">{{ number_format($ta, 2) }}</td>

                                <td style="text-align:right;"></td>
                            </tr>

                        </table>
                        <button class="print hidden-print" type="submit">Print</button>
                    </div>
                </div>

            </form>

        </div>
    @endsection

    @section('styles')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    @endsection

    @section('scripts')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
        <link rel="stylesheet" type="text/css"
            href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

        <style>
            .table tr th {
                text-transform: uppercase;
                font-size: 14px;
            }

            .table tr td {
                font-size: 14px;
            }
        </style>

        <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

        <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>

        <script>
            $('.print').click(function() {
                window.print();
            });
        </script>
        <script type="text/javascript">
            function ReloadForm() {
                document.getElementById('thisform1').submit();
                return;
            }
        </script>
    @endsection
