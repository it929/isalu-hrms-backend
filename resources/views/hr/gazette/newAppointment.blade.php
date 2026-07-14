@extends('layouts.layout')

@section('content')
<div class="box box-default" style="border: none;">
    <div class="box-body box-profile" id="printLetter" style="margin:0 5px;">

        <div class="row" id="njcAddr">
            <div class="col-md-4">

            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4 align-right" style="margin-right: 0px !important;"><span class="pull-right text-success"><em>Form SCN 116</em></span> <br><span class="text-success" style="font-weight:bold; font-size:18px;">
                ISALU HOSPITAL LIMITED,<br></span>
                <span class="text-success" style="font-weight:bold;">
                ISALU HOSPITAL LIMITED COMPLEX,<br>
                THREE ARMS ZONE,<br>
                CENTRAL DISTRICT,<br>
                P.M.B. 483,<br>
                ABUJA-NIGERIA.
                </span>

            </div>

        </div>

        <div class="row fromAddr">
            <div class="col-md-4">
                <h4>Ref: No. SCN/P.</h4> <hr style="margin-top: 0px !important;">

                The Permanent Secretary,<br>
                Office of Establishment and<br>
                Management Services,<br>
                Federal Secretatriat,<br>
                Abuja.
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <h3 class="text-center">RECOMMENDATION - NEW APPOINTMENT</h3>

            <div class="col-md-12 text-wrap" style="font-size:16px; margin-bottom:7px;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I am directed to forward herewith the appointment papers of {{strtoUpper($staff->surname.' '.$staff->othernames)}} who was
                considered for appointment as {{$staff->staffDesignation}} on a salary of <span>&#8358;</span>{{number_format($salary->amount, 2)}} per annum
                On Grade Level {{$staff->grade}} with effect from {{$staff->resumption_date ?? '.......................................'}}
                in of his/her possessing...................................................................................................In accordance with
                the scheme of service.
            </div>

            <div class="col-md-12" style="font-size:16px; margin-bottom:7px;">
                <p>2. The Department considered him/her to the post of the vacancy position as stated below:</p>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(i)     Approved Establishment Strength &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(ii)    Staff Strength               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;       -<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(iii)   Vancany                       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      -
            </div>
            <div class="col-md-12" style="font-size:16px;">
                <p>3. It is hereby confirmed that the work and conduct of the officer is satisfactory:</p>

                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Attached herewith the appropriate action are:</p>

                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(a)     2 Photostat copies of Letter of Appointment <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(b)     2 Photostat copies of Educational Certificate <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(c)     A certified true copy of Record of Service Gen.60 <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(d)     A Medical Certificate of Fitness Gen.75
            </div>

        </div>

        <div class="row text-center mt-5">
            <em>for:</em><Span style="font-size:18px;">Secretary</Span>
        </div>
    </div>

</div>
    <div>
        <button type="btn" class="btn btn-primary align-left" onclick="printLetter()">Print Gazette</button>


        <button type="Submit" form="submitGazette" class="btn btn-primary align-right">Create Gazette</button>
        <form action="{{url('create-staff-gazette')}}" method="POST" id="submitGazette">
            @csrf
            <input type="hidden" name="fileNo" value="{{$staff->ID}}">
            <input type="hidden" name="gazetteStatus" value="{{$gazetteStatus}}">
        </form>
    </div>
@endsection

<script>
    function printLetter(){

      var divToPrint = document.querySelector('#printLetter');
      var htmlToPrint = '' +
          '<style type="text/css">' +
          '.text-center {' +
          'text-align:center;' +
          'padding:0.5em;' +
          '}' +
          '#njcAddr {' +
            'float:right;'+
          '}' +
          '.fromAddr {' +
            'padding-top:110px;'+
          '}' +
          '.text-success {' +
          'color:green;' +
          '}' +
          '</style>';
      htmlToPrint += divToPrint.outerHTML;
      newWin = window.open("");
      newWin.document.write(htmlToPrint);
      newWin.print();
      newWin.close();
    }
  </script>
