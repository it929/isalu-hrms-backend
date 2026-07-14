@extends('layouts.layout')

@section('content')
<div class="box box-default" style="border: none;">
    <div class="box-body box-profile" id="printLetter" style="margin:0 5px;">
        <div class="row">
            <div class="col-md-12"><h1 class="text-success text-center">ISALU HOSPITAL LIMITED</h1></div>

        </div>

        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-4 fromAddr" style="margin-top: 10px;">
                The Permanent Secretary,<br>
                Office of Establishment and<br>
                Management Services,<br>
                Federal Secretatriat,<br>
                Abuja.
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4 align-right" id="njcAddr" style="margin-right: 0px !important;"><span class="pull-right text-success"><em>Form SCN 118</em></span> <br>
                ISALU HOSPITAL LIMITED,<br>
                Three Arms Zone, <br>
                ISALU HOSPITAL LIMITED Complex,<br>
                PMB 483, Abuja.<br>
                09-6705701<br>
                Ref...................................
                Date.................................

            </div>
        </div>

        <div class="row statusBody">
            <div class="text-center">Please note and publish in the next issue of the Gazette that:</div>

            <div class="col-md-12" style="font-size:16px; margin-bottom:7px;">
                <p>NAME: <strong>{{strtoUpper($staff->surname. ' '. $staff->first_name)}}</strong></p>
                <p>DEPARTMENT: <strong>{{strtoUpper($staff->staffDept)}}</strong></p>
                <p>STATUS: <strong>{{strtoUpper($gazetteStatus->status_name)}}</strong></p>
                <p>Has/been: </p>
                <p>To The: </p>
                <p>As: </p>
                <p>With effect from:</p>

            </div>

        </div>

        <div class="row">
            <div class="col-md-12">

                <p class="pull-right" style="margin-top: 20px;">
                    For: Secretary <br>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <p>.............................................................................................................................................................................................................................................................</p><br>
                <p>P.R.B.<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To be published in Gazette No.........................................................................................................Of.......................................................................................</p><br>

                    <div class="pull-right" style="margin-bottom: 12px;">Signed:.........................................<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;For: P.R.B</div>



                    .............................................................................................................................................................................................................................................................<br>
                    <p>To Secretary SCN<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Above noted:</p>

                        <div class="pull-right" style="margin-bottom: 12px;">Signed:.........................................<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;For: P.R.B</div>


                    .............................................................................................................................................................................................................................................................<br>
                    <p>Original to P and G<br>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                Duplication to be returned to SCN after action in P. and G. And P.R.B
            </div>
        </div>
    </div>
</div>

<div>
    <button type="btn" class="btn btn-primary align-left" onclick="printLetter()">Print Gazette</button>


    <button type="Submit" form="submitGazette" class="btn btn-primary align-right">Create Gazette</button>
    <form action="{{url('create-staff-gazette')}}" method="POST" id="submitGazette">
        @csrf
        <input type="hidden" name="fileNo" value="{{$staff->ID}}">
        <input type="hidden" name="gazetteStatus" value="{{$gazetteStatus->id}}">
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
            'float:left;'+
          '}' +
          '.statusBody {' +
            'padding-top:150px;'+
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
