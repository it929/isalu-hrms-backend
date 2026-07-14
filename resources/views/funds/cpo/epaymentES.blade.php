<!DOCTYPE html>
<html>

<head>

    <title>ISALU HOSPITAL LIMITED</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <style type="text/css">
        @if ($checkApproval == 0)
            @media print {

                .no-print,
                .no-print * {
                    display: none !important;
                }

                body {
                    display: none !important;
                }
            }
        @endif
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            /* needed for Firefox: */
            overflow: hidden;
            width: 60%;
        }

        .bg {
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;

            -webkit-animation: myfirst 5s;
            /* Chrome, Safari, Opera */
            animation: myfirst 5s;
        }

        @-webkit-keyframes myfirst {
            from {
                opacity: 0.2;
            }

            to {
                opacity: 1;
            }
        }

        /* Standard syntax */
        @keyframes myfirst {
            from {
                opacity: 0.2;
            }

            to {
                opacity: 1;
            }
        }
    </style>

    <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>
</head>

<body>

    <div class="col-md-12">
        <div class="col-md-12">
            <div>
                <p>
                <div class="row input-sm">
                    <div class="col-xs-1"><img src="{{ asset('Images/scn_logo.jpg') }}" class="img-responsive"
                            style=""></div>
                    <div class="col-xs-10">
                        <div>
                            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
                            <!--<h5 class="text-center text-success"><strong>10, PORTHARCOURT CRESCENT, AREA 11, GARKI, ABUJA</strong></h5>
            <h6 class=" text-center text-success"><strong>ACCOUNT NUMBER: 2004656203</strong></h6>-->
                            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
                        </div>
                    </div>
                    <div class="col-xs-1"><img src="{{ asset('Images/coat.jpg') }}" class="img-responsive"></div>
                </div>
                </p>
            </div>

            <div>&nbsp;

                <p>
                <div class="row">
                    <div align="left" class="col-xs-6">
                        <table>
                            <tr>
                                <td align="left">The Divisional Head,</td>
                            </tr>
                            <tr>
                                <td align="left">Federal Public Sector Division,</td>
                            </tr>
                            <tr>
                                <td align="left">First City Monumen Bank,</td>
                            </tr>
                            <tr>
                                <td align="left">Plot %32, IBB Way,</td>
                            </tr>
                            <tr>
                                <td align="left">Wuse Zone 4,</td>
                            </tr>
                            <tr>
                                <td align="left">Abuja.</td>
                            </tr>
                        </table>
                    </div>

                    <div align="right" class="col-xs-6">
                        <table>
                            <tr>
                                <td>
                                    <div align="left">Reference No:NJC/2/3/01/03<br />
                                        Code:
                                        <NJC /FCMB/OH/BAT -166br />
                                        Date Printed: {{ date('d/m/Y') }} <br />
                                        Division:


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
        <div style="öpacity:0.2">

            <div style="width:65%; margin:auto">
                Please credit the account(s) of the below listed beneficiary(s) and debit our account below with:
                (&#8358;)<b>{{ number_format($sum + $whtsum + $vatsum, 2) }} </b><br>
                <b><span id="result"></span> ONLY </b>
                <script type="text/javascript">
                    var amount = "";

                    var amount = "<?php echo number_format($sum + $whtsum + $vatsum, 2, '.', ''); ?>";

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

                        document.getElementById('result').innerHTML = words.toUpperCase(); //splitStr.join(' ');
                    }
                </script>
            </div>


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
                    <!--<td class="tblborder"><strong>BRANCH</strong></td>-->
                    <td class="tblborder">
                        <div align="center"><strong>ACC NUMBER</strong></div>
                    </td>
                    <td class="tblborder">
                        <div align="center"><strong>AMOUNT</strong> (&#8358;)</div>
                    </td>
                    <td class="tblborder"><strong>S/CODE</strong></td>
                    <td class="tblborder"><strong>PURPOSE OF PAYMENT</strong></td>
                    <td class="tblborder hidden-print"><strong>View Voucher</strong></td>


                </tr>
                <?php $key = 1; ?>
                @foreach ($mandate as $reports)
                    <?php
                    $url = url('/display/voucher/' . $reports->transactionID);
                    $transId = '';
                    if ($reports->transactionID != $transId) {
                        echo '
                                                                                         <tr class="tblborder">
                                                                                                    <td class="tblborder">' .
                            $key++ .
                            ' </td>
                                                                                                    <td class="tblborder"> ' .
                            $reports->contractor .
                            ' </td>
                                                                                                    <td class="tblborder"> ' .
                            $reports->bank .
                            ' </td>
                    
                                                                                                    <td class="tblborder"> ' .
                            $reports->accountNo .
                            ' </td>
                                                                                                    <td class="tblborder" align="right"> ' .
                            number_format($reports->amount, 2, '.', ',') .
                            ' </td>
                                                                                                    <td class="tblborder"></td>
                                                                                                    <td class="tblborder"> ' .
                            $reports->purpose .
                            ' </td>
                                                                                                    <td><a href="' .
                            $url .
                            '" class="btn btn-success btn-xs hidden-print" target="_blank">View Voucher</a></td>
                    
                                                                                                  </tr>';
                        $transId = $reports->transactionID;
                    }
                    
                    ?>
                    <?php
                    if ($reports->WHTValue == 0 && $reports->VATValue == 0) {
                        echo '';
                    } else {
                        echo '
                                                                                                         <tr class="tblborder">
                                                                                                            <td class="tblborder">' .
                            $key++ .
                            ' </td>
                                                                                                            <td class="tblborder"> ' .
                            $reports->wht_payee .
                            ' </td>
                                                                                                            <td class="tblborder"> ' .
                            $reports->wht_bank .
                            '</td>
                    
                                                                                                            <td class="tblborder"> ' .
                            $reports->wht_accountNo .
                            ' </td>
                                                                                                            <td class="tblborder" align="right">' .
                            number_format($reports->WHTValue, 2, '.', ',') .
                            ' </td>
                                                                                                            <td class="tblborder"></td>
                                                                                                            <td class="tblborder"> FIRS Remittance</td>
                                                                                                            <td><a href="' .
                            $url .
                            '" class="btn btn-success btn-xs hidden-print" target="_blank">View Voucher</a></td>
                                                                                                            <td width="50"></td>
                                                                                                        </tr>
                                                                                                        <tr class="tblborder">
                                                                                                            <td class="tblborder">' .
                            $key++ .
                            ' </td>
                                                                                                            <td class="tblborder"> ' .
                            $reports->vat_payee .
                            ' </td>
                                                                                                            <td class="tblborder"> ' .
                            $reports->vat_bank .
                            ' </td>
                    
                                                                                                            <td class="tblborder"> ' .
                            $reports->vat_accountNo .
                            ' </td>
                                                                                                            <td class="tblborder" align="right">' .
                            number_format($reports->VATValue, 2, '.', ',') .
                            ' </td>
                                                                                                            <td class="tblborder"></td>
                                                                                                            <td class="tblborder">FIRS Remittance</td>
                                                                                                            <td><a href="' .
                            $url .
                            '" class="btn btn-success btn-xs hidden-print" target="_blank">View Voucher</a></td>
                                                                                                            <td width="50"> </td>
                                                                                                        </tr>
                                                                                                        ';
                    }
                    
                    ?>
                @endforeach
                <tr class="tblborder">
                    <td class="tblborder" colspan="4"><strong>Total</strong></td>
                    <td class="tblborder" align="right"><strong>{{ number_format($sum + $whtsum + $vatsum, 2) }}
                        </strong></td>
                    <td class="tblborder" colspan="4"></td>

                </tr>
            </table>
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <br>

                    <td class="no-border" colspan="5">
                        <div align="center">

                        </div><br />
                    </td>

                </tr>
                <tr>
                    <td class="no-border" align="left"><strong>ALL DUE PROCESS COMPLIED WITH</strong></td>
                </tr>
                <tr>
                    <td class="no-border" width="385">
                        <div align="left"><strong>Authorized Signatory</strong><br />
                        </div>
                    </td>
                    <td class="no-border" width="167">
                        <div align="left"><br />
                        </div>
                    </td>
                    <td class="no-border" width="1" rowspan="9">&nbsp;</td>
                    <td class="no-border" colspan="2">
                        <div align="left"><strong>Submitted For Confirmation by</strong><br />
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="no-border" align="left">Name: <select class="type">
                            <option value="">Select One</option>
                            @foreach ($sigA as $list)
                                <option value="{{ $list->id }}">{{ $list->Name }}</option>
                            @endforeach

                        </select></td>
                    <td class="no-border" rowspan="2"><img src="{{ asset('Images/sch.jpg') }}" /></td>
                    <td class="no-border" align="left">Name: </td>
                    <td class="no-border" width="181" rowspan="2" align="left">
                        <div align="left"><img src="{{ asset('Images/sch.jpg') }}" /></div>
                    </td>
                </tr>
                <tr>
                    <td class="no-border" align="left" valign="top">Signature: <br />
                        Date:</td>
                    <td class="no-border" width="448" align="left" valign="top">Signature:<br />
                        Date:</td>
                </tr>
                <tr>
                    <td class="no-border" align="left">Tel No: <span class="sign1"></span></td>
                    <td class="no-border">&nbsp;</td>
                    <td class="no-border" colspan="2" align="left">Tel No: <span class="sign2"></span></td>
                </tr>
                <tr>
                    <td class="no-border" align="left" width=385><strong>Authorized Signatory</strong></td>
                    <td class="no-border">&nbsp;</td>
                    <td class="no-border" colspan="2">
                        <div align="left"><strong>Confirmed Before Me</strong><br />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="no-border" align="left">Name: <select class="type">
                            <option value="">Select One</option>
                            @foreach ($sigB as $list)
                                <option value="{{ $list->id }}">{{ $list->Name }}</option>
                            @endforeach

                        </select>
                    </td>
                    <td class="no-border" rowspan="2"><img src="{{ asset('Images/sch.jpg') }}" /></td>
                    <td class="no-border" align="left">Name:</td>
                    <td class="no-border" rowspan="2" align="left"> <img
                            src="{{ asset('Images/sch.jpg') }}" /> </td>
                </tr>
                <tr>
                    <td class="no-border" valign="top" align="left">Signature:<br />
                        Date:</td>
                    <td class="no-border" valign="top" align="left">Signature:<br />
                        Date:</td>
                </tr>
                <tr>
                    <td class="no-border" align="left">Tel No: <span class="sign3"></span>
                    </td>
                    <td class="no-border">&nbsp;</td>
                    <td class="no-border" colspan="2" align="left">Tel No:</td>
                </tr>

            </table>
            </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div><a href="{{ url()->previous() }}" class="hidden-print btn btn-success">Back</a></div>
                </td>
            </tr>

            </table>



            <!-- Modal HTML -->
            <form action="{{ url('/es/mandate') }}" method="post">
                {{ csrf_field() }}
                <div id="approveModal" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Confirmation</h4>
                            </div>
                            <div class="modal-body">

                                <input type="hidden" name="id" id="tid" value="" />
                                <input type="hidden" name="batch" id="batch" value="" />



                                <div class="form-group" style="margin-bottom:10px;">
                                    <div class="col-sm-122">
                                        <label class="control-label"><b>Enter Remarks</b></label>
                                    </div>
                                    <div class="col-sm-122">
                                        <textarea name="instruction" id="instruction" class="form-control" placeholder="e.g Pay a sum amount of XXXXX"> </textarea>
                                    </div>


                                </div>


                                <div class="clearfix"></div>

                                <input type="submit" name="submit" value="Reject"
                                    class="btn btn-danger pull-right hidden-print"
                                    style="margin-top:10px;margin-left:20px;">
                                <input type="submit" name="submit" value="Approve"
                                    class="btn btn-success pull-right hidden-print" style="margin-top:10px;">

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--///// end modal -->


            <script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>

            <script src="https://funds.njc.gov.ng/assets/js/bootstrap.min.js"></script>

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

                            url: murl + '/epay/signatory',
                            type: "post",
                            data: {
                                'signid': id,
                                _token: '{{ csrf_token() }}'
                            },

                            success: function(datas) {
                                console.log(datas.phone);
                                //alert(datas.phone);
                                $('.sign1').html(datas.phone);

                            }
                        });
                    });


                    $(".selectname2").on('change', function() {

                        var id = $(this).val();
                        //alert(id);
                        $token = $("input[name='_token']").val();
                        $.ajax({

                            url: murl + '/epay/signatory',
                            type: "post",
                            data: {
                                'signid': id,
                                _token: '{{ csrf_token() }}'
                            },

                            success: function(datas) {
                                console.log(datas.phone);
                                //alert(datas.phoneno);
                                $('.sign3').html(datas.phone);

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
                            url: murl + '/epay/signatory',
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


                $(document).ready(function() {

                    $(".pro").click(function() {
                        $("#approveModal").modal('show');
                        var id = $(this).attr('val');
                        var batch = $(this).attr('id');
                        //alert(batch);
                        $('#tid').val(id);
                        $('#batch').val(batch);

                    });

                });
            </script>

</body>

</html>
