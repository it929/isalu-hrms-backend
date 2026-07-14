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
        .totext
        {
        mso-number-format:"\@";
        /*force
        text*/
        }
    </style>

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

                <p>
                <div class="row">
                    <div align="left" class="col-xs-6" style="padding-top:90px;padding-left:40px;">
                        <table>
                            <tr>
                                <td align="left">THE BRANCH MANAGER,</td>
                            </tr>
                            <tr>
                                <td align="left">ZENITH BANK PLC</td>
                            </tr>
                            <tr>
                                <td align="left">MAITAMA, ABUJA</td>
                            </tr>

                        </table>
                    </div>

                    <div align="right" class="col-xs-6" style="padding-top:90px; padding-right:40px;">
                        <table>
                            <tr>
                                <td>
                                    <div align="left">{{ date('d/m/Y') }} <br />
                                        {{-- NICN/9/19/04/VOL.XII/152 <br />
                                        NICN/ZENITH/{{ $nmonth = date('m', strtotime($month)) }}/BAT-@if (count($bat) > 0)
                                            {{ $bat->batNo }}
                                        @else
                                        @endif <br /> --}}



                                        <br />
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                </p>

                <br />
                <div align="left">

                </div>

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
                <div class="text-center" style="width:80%; margin:10px auto">
                    Please credit the account(s) of the under listed beneficiary(s) and debit our account above with:
                    (&#8358;)<b>{{ number_format($grossTotal, 2, '.', ',') }}</b><br>
                    <span id="result">
                        <script type="text/javascript">
                            var amount = "";
                            var amount = "<?php echo number_format($t, 2, '.', ''); ?>";
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

                </div>
            </div>

            <table border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2">
                        <table class="table table-responsive table-bordered" id="tableData">
                            <tr class="tblborder">
                                <td class="tblborder"><strong>REFERENCE</strong></td>
                                <td class="tblborder">
                                    <div align="center"><strong>BENEFICIARY</strong></div>
                                    <div align="center"></div>
                                    <div align="center"></div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>PYMT DATE</strong> </div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>S/N</strong></div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>BEN ACCOUNT</strong></div>
                                </td>
                                <td class="tblborder"><strong>SORTCODE</strong></td>
                                <td class="tblborder"><strong>DEBIT ACCOUNT</strong></td>

                            </tr>
                            <?php $counter = 1;
                            $sum = 0; ?>
                            <?php
                            $total = 0;
                            
                            ?>
                            @foreach ($isPayable as $reports)
                            @php ++$sn @endphp
                                <tr class="tblborder">
                                    <td class="tblborder">
                                        {{$reports->description}} DEDUCT_{{$sn}}
                                    </td>
                                    <td class="tblborder">
                                        <a href="javascript:;"
                                            onclick="getAccountDetailsFunction('@php echo $reports->ID @endphp', '@php echo $reports->account_name @endphp', '@php echo $reports->account_number @endphp', '@php echo $reports->bank @endphp', '@php echo $reports->description @endphp', '@php echo $reports->sort_code @endphp')"
                                            data-toggle="modal" data-target="#updateAccountModal">
                                            {{ strtoupper( $reports->account_name != '' ? $reports->account_name : $reports->description ) }}
                                        </a>
                                    </td>
                                    <td class="tblborder" align="right">
                                        {{ number_format($reports->total, 2, '.', ',') }} </td>
                                    <td class="tblborder">{{ date('m/d/Y') }}</td>
                                    <td class="tblborder"> {{ $counter }}</td>
                                    <td class="tblborder totext"> &nbsp;{{ $reports->account_number }} </td>
                                    <td class="tblborder"> {{$reports->Bankcode}} </td>
                                    <td class="tblborder"> {{$cpoPaymentAccount}} </td>
                                </tr>
                                <?php
                                $counter = $counter + 1;
                                $sum = $total += $reports->total;
                                ?>
                            @endforeach


                            <tr class="tblborder">
                                <td class="tblborder" colspan="2"><strong>Total</strong></td>
                                <td class="tblborder" align="right"><strong> {{ number_format($sum, 2, '.', ',') }}
                                    </strong></td>
                                <td class="tblborder" colspan="2"></td>
                            </tr>

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
                        <table class="table">

                            <tr>
                                <td style="width: 35%">
                                    <div class="col-md-12  sigtab" style="padding:0px;">
                                        <div class="inner-wrap">
                                            <p><strong> Authorised Signature </strong></p>
                                            <p>Name: {{ $sig1->Name }}</p>
                                            <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                            <p>Date: </p>
                                            <p>Phone No. {{ $sig1->phone }}</p>
                                        </div>

                                        <div class="inner-wrap">
                                            <p><strong> Authorised Signature </strong></p>
                                            <p>Name: {{ $sig2->Name }}</p>
                                            <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                            <p>Date: </p>
                                            <p>Phone No. {{ $sig2->phone }}</p>
                                        </div>

                                    </div>
                                </td>

                                <td style="width: 30%">

                                </td>


                                <td style="width: 35%">
                                    <div class="col-md-12 col-xs-12 col-sm-12 sigtab" style="padding:0px;">
                                        <div class="inner-wrap">
                                            <p><strong> </strong></p>
                                            <p>Name: <br><br></p>
                                            <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                            <p>Date: <br><br><br></p>


                                        </div>

                                        <div class="inner-wrap">
                                            <p><strong> Confirm By Me </strong></p>
                                            <p>Name: </p>
                                            <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                                            <p>Date: <br><br><br></p>

                                        </div>
                                    </div>
                                </td>

                        </table>
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
                            <p><span id="descriptionModal"></span></p>
                            <br />
                            <div class="row">
                                {{-- <div class="col-md-6">
                                    <label>Bank Name</label>
                                    <input type="text" name="bank" id="bank"
                                        class="form-control" />
                                </div> --}}
                                <div class="col-md-6">
                                    <label>Select Bank  <span class="text-danger">**</span></label>
                                    <select name="bank" id="bank" class="form-control" required>
                                        <option value="">--Select Bank--</option>
                                        @foreach ($allbanklist as $b)
                                            <option value="{{$b->bankID}}">{{$b->bank}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Account Number</label>
                                    <input type="text" name="accountNumber" id="accountNumberModal"
                                        class="form-control" />
                                    <input type="hidden" name="cvID" id="cvIDModal" />
                                </div>
                                {{-- <div class="col-md-6">
                                    <label>Sort Code</label>
                                    <input type="text" name="sort_code" id="sort_code"
                                        class="form-control" />
                                </div> --}}
                                <div class="col-md-6">
                                    <label>Account Name</label>
                                    <input type="text" name="accountName" id="accountNameModal"
                                        class="form-control" />
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
            {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script> --}}

            <script>
                function getAccountDetailsFunction(cvID, accName, accNumber, bank, description, sort_code) {
                    // console.log("my bank", cvID)
                    $("#bank").val(bank)
                    $("#sort_code").val(sort_code)
                    $("#accountNameModal").val($('#accNameTable'+cvID).html() ? $('#accNameTable'+cvID).html() : accName);
                    $("#accountNumberModal").val($('#accNumTable'+cvID).html() ? $('#accNumTable'+cvID).html() : accNumber);
                    $("#descriptionModal").html(description.toUpperCase());
                    $("#cvIDModal").val(cvID);
                };
              
                $("#updateNowBtn").click(function(){
                    var accName = $("#accountNameModal").val();
                    var accNumber = $("#accountNumberModal").val();
                    var bank = $("#bank").val()
                    var sort_code = $("#sort_code").val()
                    var cvID = $("#cvIDModal").val();
                    $.ajax({
                      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                      url: murl +'/cv-details/update',
                      type: "post",
                      data: {
                        'cvID': cvID,
                        'accountName': accName,
                        'accountNumber': accNumber,
                        'bank': bank,
                        'sort_code': sort_code
                      },
                      success: function(data){
                        //Update Table
                        $('#accNumTable'+cvID).html(accNumber);
                        $('#accNameTable'+cvID).html(accName);
                        //Update Modal
                        $("#accountNameModal").val(accName);
                        $("#accountNumberModal").val(accNumber);
                        //Refresh
                        // $('#accNumTable'+cvID).load('#accNumTable'+cvID);
                        // $('#accNameTable'+cvID).load('#accNameTable'+cvID);
                        //window.location.reload();
                        location.reload(true);
                      }
                    });
                });
              </script>

            <script type="text/javascript">
                function Export() {
                    $("#tableData").table2excel({
                        filename: "{{ session('month') }}_{{ session('year') }}_CpoJusticeEpayment.xls"
                    });
                }
            </script>

    <script> var murl = "{{ url('/')}}"; </script>


</body>

</html>
