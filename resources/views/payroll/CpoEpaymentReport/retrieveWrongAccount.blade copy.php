<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ISALU HOSPITAL LIMITED...::...E-payment Schedule</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <style type="text/css">
        <!--
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

        .tblborder {
            border: 1px solid #303030 !important;
        }

        .no-border {
            border: none !important;
            border: 0;
        }

        .table tr td {
            padding: 2px;
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

        body,
        td,
        th {
            font-size: 15px;
            font-family: Verdana, Geneva, sans-serif;
        }

        a#otherpages {
            font-size: 18px
        }

        @media print {

            .no-print,
            .no-print * {
                display: none !important;
            }
        }
        -->
        select
        {
        appearance:
        none;
        -webkit-appearance:
        none;
        -moz-appearance:
        none;
        border:
        none;
        /*
        needed
        for
        Firefox:
        */
        overflow:hidden;
        width:
        60%;
        }
        .sigtab
        tr
        td
        {
        padding:
        10px;
        }
        .sigtab
        p
        {
        border:
        1px
        solid
        #ccc;
        padding:
        9px;
        width:
        100%;
        margin:
        0px;
        }
        .totext {
        mso-number-format:"\@";
        /*force
        text*/
        }
    </style>


    @if ($lock > 0)
    @else
        <style type="text/css" media="print">
            body {
                display: none;
                visibility: hidden;
            }
        </style>
    @endif

    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body style="background: url(../Images/watermarks.jpg) repeat !important; -webkit-print-color-adjust: exact;"
    onload="lookup()">

    <div class="col-md-12">
        <div class="col-md-12">
            <div>
                <p>
                <div class="row input-sm">
                    <div class="col-xs-2"></div>
                    <div class="col-xs-8">
                        <div>
                            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                            <h5 class="text-center text-success"><strong> P.M.B 322 </strong></h5>
                            <h6 class=" text-center text-success"><strong>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h6>
                            <h6 class=" text-center text-success"><strong><u>IMPLEMENTATION OF e-PAYMENT GUIDELINE FOR
                                        ISALU HOSPITAL LIMITED SALARY</u></strong></h6>
                            {{-- <h6 class=" text-center text-success"><strong>ACCOUNT NUMBER: 1015498475</strong></h6> --}}
                            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
                        </div>
                    </div>
                    <div class="col-xs-2"></div>
                </div>
                </p>
            </div>

            <div>&nbsp;

            </div>
        </div>
        <div
            style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">
            <?php
            
            $sig1 = DB::table('tblmandatesignatory')
                ->join('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')
                ->where('tblmandatesignatory.id', '=', 1)
                ->first();
            $sig2 = DB::table('tblmandatesignatory')
                ->join('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')
                ->where('tblmandatesignatory.id', '=', 2)
                ->first();
            $sum1 = 0;
            $t = 0;
            ?>

            <div style="width:100%;float:left;">
                
            </div>

            <table border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2">
                        <table class="table table-responsive table-bordered" id="tableData">
                            <tr class="tblborder">
                                <td class="tblborder">
                                    <div align="center"><strong>S/N</strong></div>
                                </td>
                                <td class="tblborder"><strong>REFERENCE</strong></td>
                                <td class="tblborder">
                                    <div align="center"><strong>BENEFICIARY</strong></div>
                                    <div align="center"></div>
                                    <div align="center"></div>
                                </td>
                                <td class="tblborder"><strong>BANK </strong></td>
                                {{-- <td class="tblborder"><strong>BRANCH</strong></td> --}}
                                
                                <td class="tblborder">
                                    <div align="center"><strong>ACC NUMBER</strong></div>
                                </td>
                                <td class="tblborder"><strong>SORTCODE</strong></td>

                            </tr>
                            <?php $counter = session('serialNo');
                            $sum = 0; ?>
                            <?php
                            $subTotal = 0;
                            $bkID = '';
                            $bcounter = 0;
                            $refstaff = '';
                            ?>
                            @foreach ($epayment_detail as $reports)
                                @if ($bkID != $reports->bankID && $bkID != '')
                                    @if ($bkID == '6' || $bkID == '33')
                                        <tr class="tblborder">
                                            <td class="tblborder"> {{ $counter }}</td>
                                            <td class="tblborder"><strong>
                                                    @if ($bkID == '6')
                                                        ASO BANK
                                                    @elseif($bkID == '33')
                                                        Garki Micro Finance Bank
                                                    @else
                                                    @endif
                                                </strong> </td>
                                            <td class="tblborder" colspan="2">
                                                @if ($bkID == '6')
                                                    UNITY BANK
                                                @elseif($bkID == '33')
                                                    UNITY BANK
                                                @else
                                                @endif
                                            </td>
                                            <td class="tblborder totext"> &nbsp;@if ($bkID == '6')
                                                    0018068474
                                                @elseif($bkID == '33')
                                                    0001478903
                                                @else
                                                @endif
                                            <td colspan="1" class="tblborder"><strong>
                                                    {{ number_format($subTotal, 2) }} </strong></td>
                                            <td class="tblborder" colspan=""> </td>
                                            <td class="tblborder" colspan="">
                                                @if ($bkID == '6')
                                                    {{ $refstaff }} and {{ $bcounter - 1 }} others
                                                    {{ session('month') }} {{ session('year') }} Staff Salary
                                                @elseif($bkID == '33')
                                                    {{ $refstaff }} and {{ $bcounter - 1 }} others
                                                    {{ session('month') }} {{ session('year') }} Staff Salary
                                                @else
                                                @endif
                                            </td>
                                        </tr>
                                        <?php $counter = $counter + 1; ?>
                                    @else
                                        {{-- to show sub total --}}
                                        {{-- <tr class="tblborder">
                                            <td colspan="2" class="tblborder"><strong> Sub Total: </strong> </td>
                                            <td colspan="1" class="tblborder"><strong>
                                                {{ number_format($subTotal, 2) }} </strong></td>
                                            <td class="tblborder" colspan="2">
                                                @if ($bkID == '6')
                                                    UNITY BANK
                                                @elseif($bkID == '33')
                                                    UNITY BANK
                                                @else
                                                @endif
                                            </td>
                                            <td class="tblborder totext">&nbsp; @if ($bkID == '6')
                                                    0018068474
                                                @elseif($bkID == '33')
                                                    0001478903
                                                @else
                                                @endif
                                            </td>
                                            <td class="tblborder" colspan=""> </td>
                                            <td class="tblborder" colspan="">
                                                @if ($bkID == '6')
                                                    {{ $refstaff }} and {{ $bcounter - 1 }} others
                                                    {{ session('month') }} {{ session('year') }} Staff Salary
                                                @elseif($bkID == '33')
                                                    {{ $refstaff }} and {{ $bcounter - 1 }} others
                                                    {{ session('month') }} {{ session('year') }} Staff Salary
                                                @else
                                                @endif
                                            </td>
                                        </tr> --}}
                                    @endif

                                    <?php
                                    $subTotal = 0;
                                    $bcounter = 0;
                                    $refstaff = $reports->name;
                                    ?>
                                @endif
                                <?php
                                $bkID = $reports->bankID;
                                
                                $subTotal += $reports->NetPay;
                                ?>
                                @if ($bkID == '6')
                                @elseif($bkID == '33')
                                @else
                                
                                    <tr class="tblborder">
                                        <td class="tblborder">{{ $counter }}</td>
                                        <td class="tblborder">
                                            @if ($reports->remarks != null && $reports->month == session('month'))
                                                {{ $reports->remarks }}
                                            @else
                                                COA {{$reports->division}} {{ substr(session('month'), 0, 3) }} Salary
                                            @endif
                                        </td>
                                        <td class="tblborder">
                                            <a href="javascript:;"
                                            onclick="getAccountDetailsFunction('@php echo $reports->staffIDCond @endphp', '@php echo str_replace("'", "" ,$reports->name) @endphp', '@php echo $reports->AccNo @endphp', '@php echo $reports->bank @endphp')"
                                            data-toggle="modal" data-target="#updateAccountModal">
                                            {{ trim($reports->name) }}
                                        </a>
                                        </td>
                                        <td class="tblborder"> {{ $reports->bank }}</td>
                                        {{-- <td class="tblborder"> {{ $reports->bank_branch }} </td> --}}
                                        <td class="tblborder totext {{strlen($reports->AccNo) != 10 ? 'alert alert-danger' : ''}}">&nbsp;{{ trim($reports->AccNo) }}</td>
                                        <td class="tblborder {{strlen($reports->Bankcode) != 9 ? 'alert alert-danger' : ''}}">&nbsp;{{trim($reports->Bankcode) }}</td>
                                    </tr>
                                    <?php $counter = $counter + 1; ?>
                                
                               
                                @endif
                            @endforeach

                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <div class="no-print" align="center">
                            <input type="button" class="hidden-print" id="btnExport" value="Export to Excel"
                                onclick="Export()" />
                        </div>

                    </td>
                </tr>
               
                <tr>
                    <td colspan="2">
                        <h2><a class="no-print" type="submit" class="btn btn-success btn-sm pull-right"
                                href="{{ url('/epayment') }}">Back</a></h2>
                    </td>
                    <!--<button id='DLtoExcel-2'  class="btn btn-success hidden-print">Export to Excel</button>-->
                </tr>
            </table>


            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

            <!-- Modal - Update Account Details -->
            <div id="updateAccountModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Update Account Details</h4>
                        </div>
                        <div class="modal-body">
                            <br />
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Staff</label>
                                    <input type="text" name="" id="name"
                                        class="form-control" />
                                    <input type="hidden" name="staffID" id="staffID" />
                                </div>
                                <div class="col-md-6">
                                    <label>Bank</label>
                                    <select name="bank" id="bank" class="form-control" required>
                                        <option value="">--Select Bank--</option>
                                        @foreach ($getAllBank as $b)
                                            <option value="{{$b->bankID}}">{{$b->bank}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Account Number</label>
                                    <input type="text" name="accountNum" id="acc"
                                        class="form-control" required/>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" id="updateNowBtn" class="btn btn-success"
                                data-dismiss="modal">Update</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
            {{-- <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script> --}}
            <script src="{{ asset('assets/js/table2excel.js') }}"></script>

            <script>
                function getAccountDetailsFunction(staffID, name, AccNo, bank) {
                    $('#staffID').val(staffID);
                    $('#name').val(name);
                    $('#acc').val(AccNo);
                    $('#bank').val(bank);
                };

                $("#updateNowBtn").click(function() {
                    var staffID = $("#staffID").val();
                    var bank = $("#bank").val();
                    var accNo = $("#acc").val();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: `/update-wrong-account/${staffID}`,
                        type: "post",
                        data: {
                            'bank': bank,
                            'accNo': accNo,
                        },
                        success: function(data) {
                            location.reload(true);
                        }
                    });
                });
            </script>

            @if ($lock > 0)
            @else
                <script type="text/javascript">
                    $('body').bind('copy paste', function(e) {
                        e.preventDefault();
                        return false;
                    });
                </script>
            @endif

            <script>
                var $btnDLtoExcel = $('#DLtoExcel-2');
                $btnDLtoExcel.on('click', function() {
                    $("#tableData").excelexportjs({
                        containerid: "tableData",
                        datatype: 'table'
                    });

                });
            </script>

            <script type="text/javascript">
                function Export() {
                    $("#tableData").table2excel({
                        filename: "{{ session('month') }}_{{ session('year') }}_CpoEpayment.xls"
                    });
                }
            </script>
            <script>
                var murl = "{{ url('/') }}";
            </script>

            <script type="text/javascript">
                $(function() {

                    $(".selectname").on('change', function() {

                        var id = $(this).val();
                        //alert(id);
                        $token = $("input[name='_token']").val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $token
                            },
                            url: murl + '/epay/test',
                            type: "post",
                            data: {
                                'signid': id
                            },

                            success: function(datas) {
                                console.log(datas.phoneno);
                                $('.sign1').html(datas.phoneno);

                            }
                        });
                    });


                    $(".selectname2").on('change', function() {

                        var id = $(this).val();
                        //alert(id);
                        $token = $("input[name='_token']").val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $token
                            },
                            url: murl + '/epay/test',
                            type: "post",
                            data: {
                                'signid': id
                            },

                            success: function(datas) {
                                console.log(datas.phoneno);
                                //alert(datas.phoneno);
                                $('.sign2').html(datas.phoneno);

                            }
                        });
                    });

                    $(".selectname3").on('change', function() {

                        var id = $(this).val();
                        //alert(id);
                        $token = $("input[name='_token']").val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $token
                            },
                            url: murl + '/epay/test',
                            type: "post",
                            data: {
                                'signid': id
                            },

                            success: function(datas) {
                                console.log(datas.phoneno);
                                //alert(datas.phoneno);
                                $('.sign3').html(datas.phoneno);

                            }
                        });
                    });


                });
            </script>

</body>

</html>
