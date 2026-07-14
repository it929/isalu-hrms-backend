<!DOCTYPE html>
<html>

<head>
    <title>ISALU HOSPITAL LIMITED...::...Staff Cooperative E-payment</title>
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

        table tr td {
            border: 1px solid #444 !important;
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
                    <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="responsive" width="50px">
                    </div>
                    <div class="col-xs-8">
                        <div>
                            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                            <h5 class="text-center text-success"><strong> ISALU HOSPITAL LIMITED </strong></h5>
                            <h6 class=" text-center text-success"><strong>THREE ARM ZONE</strong></h6>
                            <h6 class=" text-center text-success">STAFF COOPERATIVE E-PAYMENT MANDATE</h6>
                        </div>
                    </div>
                    <div class="col-xs-2"><img src="{{ asset('Images/coat.png') }}" class="responsive" width="50px">
                    </div>
                </div>
                </p>
            </div>


        </div>
        <div
            style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">


            <div class="" style="width:80%; margin:10px auto">
                Please credit the account(s) of the above listed beneficiary(s) and debit our account... accordingly:

            </div>
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Beneficiary</th>
                        <th>Bank</th>
                        <th>Branch</th>
                        <th>Account No</th>
                        <th align="right">Amount (₦)</th>
                        <th>Purpose of Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffEarnDeductionReport as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->deduction_name }}</td>
                            <td>{{ $row->bank_name ?? '—' }}</td>
                            <td>Abuja Nigeria</td>
                            <td>{{ $row->account_number ?? '—' }}</td>
                            <td align="right">{{ number_format($row->total_amount, 2) }}</td>
                            <td>{{ $month }} {{ $year }} Payroll Deduction for
                                {{ $row->deduction_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight:bold;">
                        <td colspan="5">Grand Total</td>
                        <td align="right">
                            {{ number_format($cooperativeGrandTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
                
                
            </table>
            <tr>
                    <td colspan="2">
                        <h2><a class= "no-print" type="submit" class="btn btn-success btn-sm pull-right"
                                href = "{{ url('/staff/cooperative-bank-sum') }}">Back</a></h2>
                    </td>
                </tr>


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
