<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ISALU HOSPITAL LIMITED...::...Justices E-payment</title>
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
        .totext
        {
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



    <div class="col-md-12" style="padding: 26px">

        <div class="panel panel-default">
            <div class="panel-body">

                <!-- HEADER -->
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h4 class="text-success"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                        <h6 class="text-success"><strong>JUSTICES E-PAYMENT SCHEDULE</strong></h6>
                    </div>
                </div>

                <!-- LETTER HEADING -->
                <div class="row" style="margin-top:40px;">
                    <div class="col-xs-6" style="padding-left:40px;">
                        <p><strong>THE BRANCH MANAGER,</strong></p>
                    </div>

                    <div class="col-xs-6 text-right" style="padding-right:40px;">
                        <p><strong>{{ date('d/m/Y') }}</strong></p>
                    </div>
                </div>

            </div>
        </div>

        <!-- MAIN CONTENT WITH WATERMARK -->
        <div class="panel panel-default"
            style="background: url('../Images/watermark2.jpg') no-repeat center 30% !important; -webkit-print-color-adjust: exact;">
            <div class="panel-body">

                <p>
                    Please credit the account(s) of the under listed beneficiary(s) and debit our account above with:
                    (₦) <b><span id="grandTotalAmount"></span></b>
                </p>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tableData">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>BENEFICIARY</th>
                                <th>BANK</th>
                                <th>BRANCH</th>
                                <th>ACC NUMBER</th>
                                <th>AMOUNT (₦)</th>
                                <th>S/CODE</th>
                                <th>PURPOSE OF PAYMENT</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- YOUR EXISTING LOOP (UNCHANGED) -->
                            @php
                                $counter = session('serialNo');
                                $sum = 0;
                                $subTotal = 0;
                                $bkID = '';
                                $bcounter = 0;
                                $refstaff = '';
                            @endphp

                            @foreach ($epayment_detail as $reports)
                                @if ($bkID != $reports->bankID && $bkID != '')
                                    <!-- SUBTOTAL ROW -->
                                    <tr>
                                        <td colspan="5"><strong>Sub Total:</strong></td>
                                        <td><strong>{{ number_format($subTotal, 2) }}</strong></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    @php
                                        $subTotal = 0;
                                        $bcounter = 0;
                                        $refstaff = $reports->name;
                                    @endphp
                                @endif

                                @php
                                    $bkID = $reports->bankID;
                                    $subTotal += $reports->NetPay;
                                @endphp

                                <tr>
                                    <td>{{ $counter }}</td>
                                    <td>{{ $reports->name }}</td>
                                    <td>{{ $reports->bank }}</td>
                                    <td>{{ $reports->bank_branch }}</td>
                                    <td>{{ $reports->AccNo }}</td>
                                    <td>{{ number_format($reports->NetPay, 2) }}</td>
                                    <td></td>
                                    <td>
                                        @if ($reports->remarks != null && $reports->month == session('month'))
                                            {{ $reports->remarks }}
                                        @else
                                            {{ session('month') }} {{ session('year') }} Staff Salary
                                        @endif
                                    </td>
                                </tr>

                                @php
                                    $counter++;
                                    $sum += $reports->NetPay;
                                    $bcounter++;
                                @endphp
                            @endforeach

                            <!-- LAST SUBTOTAL -->
                            <tr>
                                <td colspan="5"><strong>Sub Total:</strong></td>
                                <td><strong>{{ number_format($subTotal, 2) }}</strong></td>
                                <td colspan="2"></td>
                            </tr>

                            <!-- GRAND TOTAL -->
                            <tr>
                                <td colspan="5"><strong>Total</strong></td>
                                <td><strong>{{ number_format($sum, 2) }}</strong></td>
                                <td colspan="2"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- EXPORT BUTTON -->
                <div class="text-center no-print" style="margin-top:20px;">
                    <button class="btn btn-success btn-sm" id="btnExport" onclick="ExportToExcel('xlsx')">
                        Export to Excel
                    </button>
                </div>

                <!-- SIGNATORIES AREA -->
                <div class="row" style="margin-top:40px;">

                    <div class="col-sm-4">
                        <div class="well">
                            <p><strong>Authorised Signature</strong></p>
                            <p>Name:</p>
                            <p>Signature & Thumb Print <br><br><br><br><br></p>
                            <p>Date:</p>
                            <p>Phone No.:</p>
                        </div>

                        <div class="well">
                            <p><strong>Authorised Signature</strong></p>
                            <p>Name:</p>
                            <p>Signature & Thumb Print <br><br><br><br><br></p>
                            <p>Date:</p>
                            <p>Phone No.:</p>
                        </div>
                    </div>

                    <div class="col-sm-4"></div>

                    <div class="col-sm-4">
                        <div class="well">
                            <p>Name: <br><br></p>
                            <p>Signature & Thumb Print <br><br><br><br><br></p>
                            <p>Date: <br><br></p>
                        </div>

                        <div class="well">
                            <p><strong>Confirm By Me</strong></p>
                            <p>Name:</p>
                            <p>Signature & Thumb Print <br><br><br><br><br></p>
                            <p>Date:</p>
                        </div>
                    </div>

                </div>

                <!-- BACK BUTTON -->
                <div class="text-right" style="margin-top:30px;">
                    <a href="{{ url('/con-epayment-justices') }}" class="btn btn-primary btn-sm no-print">
                        Back
                    </a>
                </div>

            </div>
        </div>


        <!-- MODAL -->
        <div id="updateAccountModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Update Account Details</h4>
                    </div>

                    <div class="modal-body">
                        <p><span id="descriptionModal"></span></p>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Account Number</label>
                                <input type="text" id="accountNumberModal" class="form-control">
                                <input type="hidden" id="cvIDModal">
                            </div>

                            <div class="col-md-6">
                                <label>Account Name</label>
                                <input type="text" id="accountNameModal" class="form-control">
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">Close</button>
                        <button id="updateNowBtn" class="btn btn-success" data-dismiss="modal">Update</button>
                    </div>

                </div>
            </div>
        </div>

    </div>


    {{-- <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
            <script src="{{ asset('assets/js/table2excel.js') }}"></script> --}}

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

    <script>
        function getAccountDetailsFunction(cvID, accName, accNumber, description) {
            $("#accountNameModal").val($('#accNameTable' + cvID).html() ? $('#accNameTable' + cvID).html() : accName);
            $("#accountNumberModal").val($('#accNumTable' + cvID).html() ? $('#accNumTable' + cvID).html() : accNumber);
            $("#descriptionModal").html(description.toUpperCase());
            $("#cvIDModal").val(cvID);
        };

        $("#updateNowBtn").click(function() {
            var accName = $("#accountNameModal").val();
            var accNumber = $("#accountNumberModal").val();
            var cvID = $("#cvIDModal").val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: murl + '/cv-details/update',
                type: "post",
                data: {
                    'cvID': cvID,
                    'accountName': accName,
                    'accountNumber': accNumber,
                },
                success: function(data) {
                    //Update Table
                    $('#accNumTable' + cvID).html(accNumber);
                    $('#accNameTable' + cvID).html(accName);
                    //Update Modal
                    $("#accountNameModal").val(accName);
                    $("#accountNumberModal").val(accNumber);
                    //Refresh
                    // $('#accNumTable'+cvID).load('#accNumTable'+cvID);
                    // $('#accNameTable'+cvID).load('#accNameTable'+cvID);
                    //window.location.reload();
                    //location.reload(true);
                }
            });
        });
    </script>

    <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/table2excel.js') }}"></script>

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
        function ExportToExcel() {
            $("#tableData").table2excel({
                filename: "{{ session('month') }}_{{ session('year') }}_Mandate.xls"
            });
            $("#tableData").excelexportjs({
                containerid: "tableData",
                datatype: 'table'
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


        $("#grandTotalAmount").html("<?php echo $sum; ?>");
    </script>

    <script type="text/javascript">
        var amount = "";
        var amount = "<?php echo $sum; ?>";
        var money = amount.split('.');

        function lookup() {
            var words;
            var naira = money[0];
            var kobo = money[1];
            var word1 = toWords(naira) + " naira";
            var word2 = ", " + toWords(kobo) + " kobo";
            if (kobo != "00")
                words = word1 + word2;
            else
                words = word1;
            document.getElementById('result').innerHTML = words.toUpperCase();
        }
    </script>

</body>

</html>
