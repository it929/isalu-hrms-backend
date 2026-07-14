<!DOCTYPE html>
<html>

<head>

    <title>ISALU HOSPITAL LIMITED...::...E-payment Schedule</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <style type="text/css">
        /* <!-- */
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

        <title>SUPREME COURT...::...E-payment Schedule</title><link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}"><style type="text/css">

        /* <!-- */
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

        /* --> */

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            /* needed for Firefox: */
            overflow: hidden;
            width: 60%;
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
                            <h2 class="text-center"><strong>ISALU HOSPITAL LIMITED</strong></h2>

                            <h3 class="text-center">BANK SCHEDULE FOR THE MONTH ENDED {{ $month }}
                                {{ $year }}</h3>
                            <h4 class="text-center">
                                @if ($bank != '')
                                    ADDRESS {{ $bank->bank }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    <div class="col-xs-2"></div>
                </div>
                </p>
            </div>

            <div>&nbsp;
                <br />
                <div align="left">

                </div>
            </div>
        </div>
        <div
            style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">

            <?php
            $sum1 = 0;
            ?>

            <table border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2">
                        <table class="table table-responsive table-bordered">
                            <tr class="tblborder">
                                <td class="tblborder">
                                    <div align="center"><strong>S/N</strong></div>
                                </td>
                                @if ($bankID == 6 || $bankID == 33)
                                    <td class="tblborder"><strong>ACCOUNT NUMBERS </strong></td>
                                @endif
                                <td class="tblborder"><strong>BANK </strong></td>
                                <td class="tblborder">
                                    <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                                </td>
                                <td class="tblborder">
                                    <div align="center"><strong>BENEFICIARY</strong></div>
                                    <div align="center">
                                    </div>
                                    <div align="center"></div>
                                </td>
                            </tr>

                            <?php $counter = session('serialNo');
                            $counter = 1;
                            $sum = 0; ?>
                            <?php $subTotal = 0;
                            $bkID = ''; ?>

                            @foreach ($epayment_detail as $reports)
                                @if ($bkID != $reports->bank && $bkID != '')
                                    <tr class="tblborder">
                                        <td colspan="2" class="tblborder"><strong> Sub Total:</strong> </td>
                                        @if ($bankID == 6 && $bankID == 33)
                                            <td></td>
                                        @endif
                                        <td colspan="1" class="tblborder" align="right"><strong>
                                                {{ number_format($subTotal, 2) }} </strong></td>
                                        <td class="tblborder"></td>
                                    </tr>
                                    <?php $subTotal = 0; ?>
                                @endif

                                <?php $bkID = $reports->bank;
                                $subTotal += $reports->NetPay; ?>

                                <tr class="tblborder">
                                    <td class="tblborder"> {{ $counter }}</td>
                                    @if ($bankID == 6 || $bankID == 33)
                                        <td class="tblborder"> {{ $reports->AccNo }} </td>
                                    @endif
                                    <td class="tblborder"> {{ $reports->bank }} </td>
                                    <td class="tblborder" align="right">
                                        {{ number_format($reports->NetPay, 2, '.', ',') }} </td>
                                    <td class="tblborder">{{ $reports->name }}</td>
                                </tr>

                                <?php
                                $sum = $sum + $reports->NetPay;
                                $counter = $counter + 1;
                                ?>
                            @endforeach

                            <?php
                            $finalsum = 0;
                            ?>
                            @foreach ($epayment_total as $reports)
                                <?php
                                $finalsum = $finalsum + $reports->NetPay;
                                ?>
                            @endforeach

                            @if ($bkID != '')
                                <tr class="tblborder">

                                    <td colspan="2" class="tblborder"><strong> Sub Total:</strong> </td>
                                    @if ($bankID == 6 || $bankID == 33)
                                        <td></td>
                                    @endif
                                    <td colspan="1" class="tblborder" align="right"><strong>
                                            {{ number_format($subTotal, 2) }} </strong></td>
                                    <td class="tblborder"></td>
                                </tr>
                                <?php
                                $subTotal = 0;
                                ?>
                            @endif

                            <tr class="tblborder">
                                <td class="tblborder" colspan="2"><strong>Total</strong></td>
                                @if ($bankID == 6 || $bankID == 33)
                                    <td></td>
                                @endif
                                <td class="tblborder" align="right"><strong> {{ number_format($sum, 2, '.', ',') }}
                                    </strong></td>
                                <td class="tblborder"></td>
                            </tr>
                        </table>
                    </td>
                </tr> {{--  end of 1st tr --}}









                <tr>
                    <td colspan="2">
                        <div class="no-print" align="center">
                        </div>
                    </td>
                </tr> {{--  end of 2st tr --}}

                <tr>
                    <td colspan="2">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <br>

                                <td class="no-border" colspan="5">
                                    <div align="center">
                                        {{-- Please credit the account(s) of the above listed beneficiary(s) and debit our account above with: (&#8358;)<b>{{ number_format( $sum, 2, '.', ',')}}</b><br> --}}
                                        <span id="result">
                                            <script type="text/javascript">
                                                /* var amount = "";
                                                                                                                                        var amount = "<?php echo number_format($sum, 2, '.', ''); ?>";
                                                                                                                                        var money = amount.split('.');
                                                                                                                                        function lookup()
                                                                                                                                        {
                                                                                                                                        var words;
                                                                                                                                        var naira = money[0];
                                                                                                                                        var kobo = money[1];
                                                                                                                                        var word1 = toWords(naira)+" naira";
                                                                                                                                        var word2 = ", "+toWords(kobo)+" kobo";
                                                                                                                                        if(kobo != "00")
                                                                                                                                            words = word1 + word2;
                                                                                                                                        else
                                                                                                                                            words = word1;
                                                                                                                                        document.getElementById('result').innerHTML = words.toUpperCase();
                                                                                                                                        }*/
                                            </script>
                                            <br />
                                        </span>
                                    </div>
                                    <br />
                                </td>

                            </tr>
                        </table>
                    </td>
                </tr> {{--  end of 3rd tr --}}
                <tr>
                    <td colspan="2">
                        <h2><a class= "no-print" type="submit" class="btn btn-success btn-sm pull-right"
                                href = "{{ url('/summary/bybanks') }}">Back</a></h2>
                    </td>
                </tr> {{--  end of 4tt tr --}}

            </table> {{--  end of 1st table --}}
















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
