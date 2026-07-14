<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>SUPREME COURT...::...E-payment Schedule</title>
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

    <div class="col-md-12">
        <div class="col-md-12">
            <div>
                <p>
                <div class="row input-sm">
                    <div class="col-xs-2"></div>
                    <div class="col-xs-8">
                        <div>
                            {{-- <h4 class="text-success text-center"><strong>COURT OF APPEAL</strong></h4> --}}
                            <h5 class="text-center text-success"><strong> ISALU HOSPITAL LIMITED </strong></h5>
                            <h6 class=" text-center text-success"><strong>THREE ARMS ZONE</strong></h6>
                            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE - {{strtoupper($divisionName)}} DIVISION</h6>
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
                                <td align="left">TO THE MANAGER,</td>
                            </tr>
                            <tr>
                                <td align="left">{{ $bank_name }}</td>
                            </tr>
                            {{-- <tr><td align="left">MAITAMA, ABUJA</td></tr> --}}

                        </table>
                    </div>

                    <div align="right" class="col-xs-6" style="padding-top:90px; padding-right:40px;">
                        <table>
                            <tr>
                                <td>
                                    <div align="left">{{ date('d/m/Y') }} <br />
                                        PV No: ............................... <br />
                                        PF 133<br />
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
                    <strong><u>PayPoint Schedule (Details)</u></strong>
                </div>
            </div>

            <table border="0" align="center" cellpadding="0" cellspacing="0"
                style="width:100%;>
                <tr>
                    <td colspan="2">
                <table class="table table-responsive table-bordered" id="tableData">
                    <tr>
                        <td colspan="7" class="text-center">
                            E-PAYMENT SCHEDULE - {{strtoupper($divisionName)}} DIVISION @if ($bank_name != ''), {{$bank_name}}  @endif for {{$month}}-{{$year}}
                        </td>
                    </tr>
                    <tr class="tblborder">
                        <td class="tblborder">
                            <div align="center"><strong>S/N</strong></div>
                        </td>
                        <td class="tblborder">
                            <div align="center"><strong>STAFF NO.</strong></div>
                        </td>
                        <td class="tblborder">
                            <div align="center"><strong>
                                Grade
                            </strong></div>
                        </td>
                        <td class="tblborder"><div align="center"><strong>
                            Step
                            </strong></div>
                        </td>
                        <td class="tblborder">
                            <div align="center"><strong>BENEFICIARY</strong></div>
                            <div align="center"></div>
                            <div align="center"></div>
                        </td>
                        <td class="tblborder">
                            <div align="center"><strong>ACC NUMBER</strong></div>
                        </td>
                        <td class="tblborder">
                            <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                        </td>
                    </tr>
                    <?php $counter = 0; 
                        $totalNetPay = 0
                    ?>
                    @foreach ($epayment_detail as $reports)
                        <?php $counter = $counter + 1; 
                        $totalNetPay += $reports->NetPay
                        ?>
                        <tr class="tblborder">
                            <td class="tblborder" style="width: 18px;"> {{ $counter }}</td>
                            <td class="tblborder"> {{ $reports->fileNo }}</td>
                            <td class="tblborder">{{$reports->grade}}</td>
                            <td class="tblborder">{{$reports->step}}</td>
                            <td class="tblborder"> {{ $reports->name }} </td>
                            <td class="tblborder totext"> &nbsp;{{ $reports->AccNo }} </td>
                            <td class="tblborder" align="right">
                                {{ number_format($reports->NetPay, 2, '.', ',') }} </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td> <strong> No. Of Staff </strong> {{$counter}} </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td align="right"><strong>{{ $bank_name }} - Total </strong></td>
                        <td align="right">{{number_format($totalNetPay, 2, '.', ',' )}}</td>
                    </tr>
                </table>
                </td>
                </tr>

                <tr>
                    <td>
                        <div class="no-print text-center" align="center">
                            <input type="button" class="hidden-print" id="btnExport" value="Export to Excel" onclick="Export()" />    
                        </div>
                    </td>
                </tr>

                {{-- <tr>
                    <td colspan="2">
                        <table class="table">

                            <tr>
                                <td style="width: 35%">
                                    <div class="col-md-12 sigtab" style="padding:0px;">
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

                </tr> --}}

                <div class="row container">
                <div class="col-md-6">
                    Approved By: ........................................................ <br>
                    Designation: ........................................................ <br>
                    Sign: ............................................................... <br>
                    Date: ............................................................... <br>
                </div>

                <div class="col-md-6 pull-right">
                    <div style="border: 1px solid black; margin-right:0px !important;">
                        <u>Acknowledgement</u><br>
                        I hereby acknowledge receipt of cheque number........................as shown, for an amount of............................................................. <br>

                        <div class="row">
                            <div class="col-md-6">
                                .............................................<br>
                                Bank Manager
                            </div>
                            <div class="col-md-6">
                                .............................................<br>
                                Cheque No
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="row">
                    <h2><a class="no-print" type="submit" class="btn btn-success btn-sm pull-right"
                                href="{{ url('/schedule/bank-by-bank') }}">Back</a></h2>
                </div>

                <tr>
                    <td colspan="2">
                        <h2></h2>
                    </td>
                </tr>
            </table>

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

            <script type="text/javascript">
                function Export() {
                    $("#tableData").table2excel({
                        filename: "{{ session('month') }}_{{ session('year') }}_Salary-Schedule.xls"
                    });
                }
            </script>
            <script>
                var murl = "{{ url('/') }}";
            </script>

</body>

</html>
