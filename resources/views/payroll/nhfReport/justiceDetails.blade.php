@extends('layouts.layout')
@section('pageTitle')
    NHF Reports
@endsection
@section('content')

    <div class="box-body" style="background:#FFF;">
        <div class="row">
            {{ csrf_field() }}
            <h2 class="text-center">ISALU HOSPITAL LIMITED</h2>
            <h3 class="text-center">ADDRESS:................................................</h3>
            <h3 class="text-center">NHF PAYMENT SCHEDULE FOR THE MONTH OF
                {{ $month }}, {{ $year }}.
            </h3>
            @if (Auth::user()->is_global == 1)
                @if ($division != '')
                    <h3 class="text-center">DIVISION: @foreach ($division as $division)
                            {{ $division->division }}</h3>
                @endforeach
            @endif
            @if ($division == '')
                <h3 class="text-center">ALL DIVISIONS</h3>
            @endif
        @else
            <h3 class="text-center">DIVISION: @foreach ($division as $division)
                    {{ $division->division }}</h3>
            @endforeach
            @endif
            <div class="col-md-12">
                <div class="" style="margin-bottom:20px;">

                </div>

                <div class="table-responsive">

                    <table class="table table-striped table-condensed table-bordered" id="tableData">
                        <thead class="text-gray-b">
                            <tr>
                                <th colspan="11" align="center">
                                    <p class="text-center" style="padding-top:15px; font-size:20px;">NHF SCHEDULE TEMPLATE
                                    </p>
                                </th>
                            </tr>
                            <tr>
                                <th>S/N</th>
                                <th>EMPLOYER NAME</th>
                                @if ($division == '')
                                    <th>Division</th>
                                @endif
                                <th>EMPLOYER NUMBER</th>
                                <th>LAST NAME</th>
                                <th>FIRST NAME</th>
                                <th>MIDDLE NAME</th>
                                <th>NHF NUMBER</th>
                                <th>AMOUNT</th>


                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                                $totalNHF = 0;
                                $count = 0;
                                $subTotal = 0;
                                $divID = '';

                            @endphp


                            @foreach ($nhf as $list)
                                @php
                                    $i = 1;

                                    $count++;
                                    $basicSalary = DB::table('basicsalaryconsolidated')
                                        ->where('employee_type', '=', $list->employee_type)
                                        ->where('grade', '=', $list->grade)
                                        ->where('step', '=', $list->step)
                                        ->value('amount');
                                @endphp
                                @if ($divID != $list->divisionID && $divID != '')
                                    <tr>
                                        <td colspan="8" class="tblborder"><strong> Sub Total: </strong> </td>
                                        <td colspan="1" class="tblborder"><strong>
                                                {{ number_format($subTotal, 2) }} </strong></td>
                                    </tr>
                                    <?php
                                    $subTotal = 0;
                                    ?>
                                @endif

                                @php
                                    $divID = $list->divisionID;
                                    $subTotal += $list->NHF;
                                @endphp


                                <tr>
                                    <td>{{ $count }}</td>
                                    <td>Supreme Court Of Nigeria</td>

                                    @if ($division == '')
                                        <td class="text-uppercase">{{ $list->division }}</td>
                                    @endif
                                    <td>020001146-6</td>
                                    <td class="text-uppercase">{{ $list->surname }}</td>
                                    <td class="text-uppercase">{{ $list->first_name }}</td>
                                    <td class="text-uppercase">{{ $list->othernames }}</td>

                                    <td id="{{ $list->ID }}"
                                        onclick="getAccountDetailsFunction('{{ $list->ID }}', '{{ $list->nhfNo }}')"
                                        data-toggle="modal" data-target="#updateAccountModal"> <a> {{ $list->nhfNo }}</a>
                                    </td>
                                    <td><?php $totalNHF += $list->NHF; ?>{{ number_format($list->NHF, 2) }}</td>







                                </tr>
                            @endforeach
                            @if ($divID != '')
                                <tr class="tblborder">

                                    <td colspan="8" class="tblborder"><strong> Sub Total:</strong> </td>
                                    <td colspan="1" class="tblborder"><strong> {{ number_format($subTotal, 2) }}
                                        </strong></td>
                                </tr>
                                <?php $subTotal = 0; ?>
                            @endif

                            <tr>
                                <td colspan="8"><strong>TOTAL:</strong></td>
                                <td class=""><strong>{{ number_format($totalNHF, 2) }}</strong></td>
                                <td colspan="1"></td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div class="no-print" align="center">
                                        <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                                            onclick="Export();" />
                                    </div>

                                </td>
                            </tr>

                        </tbody>
                        <div id="updateAccountModal" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Update NHF Number</h4>
                                    </div>
                                    <div class="modal-body">
                                        <br />
                                        <div class="row">


                                            <form>
                                                <div class="col-md-6">
                                                    <label>NHF number</label>
                                                    <input type="hidden" name="nhfID" id="nhfID"
                                                        class="form-control" />
                                                    <input type="text" name="nhfnumber" id="nhf"
                                                        class="form-control" />


                                                </div>


                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" id="close"
                                            data-dismiss="modal">Close</button>

                                        <button type="button" name="submit" id="submitButton"
                                            class="btn btn-success">Update</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </table>
                </div>


            </div>
        </div>
    </div><!-- /.col -->
    </div><!-- /.row -->

@endsection
@section('styles')
    <style>
        .init {
            line-height: 30px;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>


    <script type="text/javascript">
        $(document).ready(function() {

            $("#court").on('change', function(e) {
                e.preventDefault();
                var id = $(this).val();
                //alert(id);
                $token = $("input[name='_token']").val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $token
                    },
                    url: murl + '/session/court',

                    type: "post",
                    data: {
                        'courtID': id
                    },
                    success: function(data) {
                        location.reload(true);
                        //console.log(data);
                    }
                });

            });
        });

        function getAccountDetailsFunction(a, b) {

            $('#nhfID').val(a);
            $('#nhf').val(b);


        };
        // $("#submitButton").click(function() {
        //   var ID = $("#nhfID").val();
        //  var nhf = $("#nhf").val();
        //                     $.ajax({
        //                         headers: {
        //                             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //                         },
        //                         url: `/update-nhf`,
        //                         type: "post",
        //                         data: {
        //                             'ID': ID,
        //                             'nhf': nhf,
        //                         },
        //                         success: function(data) {
        //                             location.reload(true);
        //                         }
        //                     });
        //                 });

        $(document).ready(function() {
            $("#submitButton").click(function() {
                var ID = $("#nhfID").val();
                var nhf = $("#nhf").val();
                $('#submitButton').html('Please wait...')
                $('#submitButton').prop('disable', true)

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/update-nhf/' + ID + '/' + nhf,
                    method: "GET",

                    success: function(data) {
                        location.reload(true);
                        // $("#"+ID).html(nhf);
                        $('#submitButton').html('Update')
                        $('#submitButton').prop('disable', false)
                        $('#close').click()
                        alert("Record Updated successfully.");
                    },
                    error: function(xhr, status, error) {
                        alert("Error sending record: " + error);
                        $('#submitButton').html('Update')
                        $('#submitButton').prop('disable', false)
                    }
                });
            });
        });
    </script>
    <script type="text/javascript">
        function Export() {
            $("#tableData").table2excel({
                filename: "{{ $month }}_{{ $year }}__JusticesNHF.xls"
            });
        }
    </script>
@endsection
