@extends('layouts.layout')

@section('content')
<div class="box box-default" style="border: none;">
    <div class="box-body box-profile" id="printLetter" style="margin:0 5px;">
        <div class="row">
            <div class="col-md-4 offset-4 pull-right"><h4 class="text-success text-center">ISALU HOSPITAL LIMITED, ABUJA</h4></div>
        </div>
        <div class="row">
            <div class="col-md-12 text-success" style="font-weight:bold;"><h3 class="text-center">MEMORANDUM</h3><br>
                <p>TO: <strong>{{strtoUpper($user->surname. ' '.$user->first_name)}}</strong></p>
                <p>FROM: ASSISTANT CHIEF ADMIN. OFFICER</p>
                <p>SUBJECT: NOMINATION TO ATTEND WORKSHOP</p>
                <p>DATE: {{date('d-M-Y', strtotime(date('d-M-Y')))}}</p>
            </div>
        </div>

        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-4" style="margin-top: 10px;">

            </div>
        </div>

        <div class="row">
            <div class="text-center"><h2>NOMINATION TO ATTEND WORKSHOP ON <br> {{strtoUpper($training->title)}} </h2></div>

            <div class="col-md-12" style="font-size:16px; margin-bottom:7px;">
                <p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I am directed to write and inform you that the secretary has approved your nomination to attend
                    the above named workshop been orgnized by the council in collaboration with <strong>{{strtoUpper($training->consultant)}}</strong> (consultant)</p>

                <p>FROM: <span style="font-weight: bold;">{{$training->training_date}}</span></p>
                <p>TO: <span style="font-weight: bold;">{{$training->training_end_date}}</span></P>
                <p>Time: <span style="font-weight: bold;">{{$training->training_time}}</span></p>
                <p>Venue: <span style="font-weight: bold;">{{$training->venue}}</span></p>

                <p>2. You are further advised that on resumption from the workshop, you are to submit
                    a report and a photocopy of your certificate of attendance to the office of the Training Officer. This workshop is compulsory.</p>

                <p>3. Congratulations</p>
            </div>

        </div>

        <div class="row text-center" style="margin-top: 30px;">
            From...<br>
            For:<Span style="font-size:18px;">Secretary</Span>
        </div>

    </div>
</div>
<div>
    <button type="btn" class="btn btn-primary" onclick="printLetter()">Print Letter</button>
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
