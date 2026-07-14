<!DOCTYPE html>
<html>

<head>
    <title>ISALU HOSPITAL LIMITED...::...E-payment Schedule</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

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

        .tblborder {
            border: 1px solid #303030 !important;
        }

        .no-border {
            border: none !important;
            border: 0;
        }

        .table tr td {
            padding: 1px;
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

            table,
            tr,
            td {
                page-break-inside: avoid !important;
            }

            .PleaseCredit {
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
            }

            /* .tableCredit {
                page-break-before: avoid !important;
            } */

            tr {
                page-break-inside: avoid !important;
                /* page-break-after: auto; */
            }
            .authorizerSign{
                margin-top: 20px;
                padding-top: 20px;
            }
        }

        select {
            appearance:
                none;
            -webkit-appearance:
                none;
            -moz-appearance:
                none;
            border:
                none;
            overflow: hidden;
            width:
                60%;
        }
    </style>

    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body style="background: url(../Images/watermarks.jpg) repeat !important; -webkit-print-color-adjust: exact;"
    onload="lookup()">

    <div class="col-md-12">
        <div class="col-md-12">
            <div class="PleaseCredit">
                <p>
                <div class="row input-sm">
                    <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="responsive" width="50px">
                    </div>
                    <div class="col-xs-8">
                        <div>
                            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                            <h5 class="text-center text-success"><strong> ISALU HOSPITAL LIMITED </strong></h5>
                            <h6 class=" text-center text-success"><strong>THREE ARM ZONE</strong></h6>
                            <h6 class=" text-center text-success"><strong>ACCOUNT NUMBER:
                                    {{ $accountDetails->account_no ?? 'No Account Number assigned' }}</strong></h6>
                            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
                        </div>
                    </div>
                    <div class="col-xs-2"><img src="{{ asset('Images/coat.png') }}" class="responsive" width="50px">
                    </div>
                </div>
                </p>
            </div>

            <div>&nbsp;

                <p>
                <div class="row PleaseCredit">
                    <div class="col-xs-6">

                        <strong>THE MANAGER <br>
                            UBA PLC. <br></strong>

                    </div>

                    <div class="col-xs-6" style="text-align: right">
                        <strong>Ref No.SCN/SALPE/{{ date('m/Y') }} </strong>
                    </div>

                </div>
            </div>
            </p>

            <br />

        </div>
    </div>
    <div style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">
        <?php
        $sum1 = 0;
        ?>
        @foreach ($epayment_detail as $list)
            <?php
            $sum1 = $sum1 + $list->NetPay;
            
            ?>
        @endforeach


        <?php
        $finalsum = 0;
        ?>
        @foreach ($epayment_total ?? collect() as $reports)
            <?php
            $finalsum = $finalsum + $reports->NetPay;
            ?>
        @endforeach
        <div class="PleaseCredit" style="width:80%; margin:10px auto">
            Please credit the account(s) of the above listed beneficiary(s) and debit our account above with:
            (&#8358;)<b>{{ number_format($sum1, 2, '.', ',') }}</b><br>
            <span id="result">
                <script type="text/javascript">
                    var amount = "";
                    var amount = "<?php echo number_format($sum1, 2, '.', ''); ?>";
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
            </span>
        </div>
        <table border="0" class="tableCredit" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <table class="table table-responsive table-bordered">
                        <tr class="tblborder">
                            <td class="tblborder">
                                <div align="center"><strong>S/N</strong></div>
                            </td>
                            <td class="tblborder">
                                <div align="center"><strong>BENEFICIARY</strong></div>
                                <div align="center"></div>
                                <div align="center"></div>
                            </td>
                            <td class="tblborder"><strong>BANK </strong></td>
                            <td class="tblborder"><strong>BRANCH</strong></td>
                            <td class="tblborder">
                                <div align="center"><strong>ACC NUMBER</strong></div>
                            </td>
                            <td class="tblborder">
                                <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                            </td>
                            {{-- <td class="tblborder"><strong>S/CODE</strong></td> --}}
                            <td class="tblborder"><strong>PURPOSE OF PAYMENT</strong></td>
                        </tr>
                        <?php $counter = session('serialNo');
                        $sum = 0; ?>
                        <?php
                        $sig1 = DB::table('tblmandatesignatory')->join('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->where('tblmandatesignatory.id', '=', 1)->first();
                        $sig2 = DB::table('tblmandatesignatory')->join('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->where('tblmandatesignatory.id', '=', 2)->first();
                        $subTotal = 0;
                        $bkID = '';
                        ?>
                        @foreach ($epayment_detail as $reports)
                            @if ($bkID != $reports->bank && $bkID != '')
                                <tr class="tblborder">

                                    <td colspan="5" class="tblborder"> Sub Total: </td>
                                    <td colspan="3" class="tblborder"> {{ number_format($subTotal, 2) }} </td>
                                </tr>
                                <?php
                                $subTotal = 0;
                                ?>
                            @endif
                            <?php
                            $bkID = $reports->bank;
                            $subTotal += $reports->NetPay;
                            ?>
                            <tr class="tblborder">
                                <td class="tblborder"> {{ $counter }}</td>
                                <td class="tblborder"> {{ $reports->name }} </td>
                                <td class="tblborder"> {{ $reports->bank }} </td>
                                <td class="tblborder"> {{ $reports->bank_branch }} </td>
                                <td class="tblborder"> {{ $reports->AccNo }} </td>
                                <td class="tblborder" align="right">
                                    {{ number_format($reports->NetPay, 2, '.', ',') }} </td>
                                {{-- <td class="tblborder"> </td> --}}
                                <td class="tblborder"> {{ session('month') }} {{ session('year') }} Staff Salary
                                </td>
                            </tr>
                            <?php
                            $sum = $sum + $reports->NetPay;
                            $counter = $counter + 1;
                            ?>
                        @endforeach

                        <?php
                        $finalsum = 0;
                        ?>
                        @foreach ($epayment_total ?? collect() as $reports)
                            <?php
                            $finalsum = $finalsum + $reports->NetPay;
                            ?>
                        @endforeach
                        <tr class="tblborder">
                            <td class="tblborder" colspan="5"><strong>Total</strong></td>
                            <td class="tblborder" align="right"><strong> {{ number_format($sum, 2, '.', ',') }}
                                </strong></td>
                            <td class="tblborder" colspan="2"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="no-print" align="center" style="margin: 20px 0;">
                        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                        <a class="btn btn-success" href="{{ url('/con-epayment') }}">Back</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="authorizerSign">
                    <table border="0" cellpadding="0" cellspacing="0"
                        style="width: 100%; page-break-inside: avoid;">
                        <tr>
                            <td class="no-border" width="40%" style="padding: 20px;">
                                <div><strong>Authorized Signatory:</strong> {{ $sig1->Name }}</div>
                                <div style="margin-top: 10px;"><strong>Signature:</strong>
                                    ................................</div>
                                <div style="margin-top: 10px; display: flex; align-items: center;"><strong>Thumb
                                        Print:</strong>
                                    <div style="border: 1px solid #000; height: 60px; width: 80px; margin-left: 10px;">
                                    </div>
                                </div>
                            </td>
                            <td class="no-border" width="20%">&nbsp;</td>
                            <td class="no-border" width="40%" style="padding: 20px;">
                                <div><strong>Authorized Signatory:</strong> {{ $sig2->Name }}</div>
                                <div style="margin-top: 10px;"><strong>Signature:</strong>
                                    ................................</div>
                                <div style="margin-top: 10px; display: flex; align-items: center;"><strong>Thumb
                                        Print:</strong>
                                    <div style="border: 1px solid #000; height: 60px; width: 80px; margin-left: 10px;">
                                    </div>
                                </div>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>

        </table>

        <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>

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
                            //alert(datas.phoneno);
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
