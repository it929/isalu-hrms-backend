@extends('layouts.layout')

@section('content')
<div class="box box-default" style="border: none;">
    <div class="box-body box-profile" id="printLetter" style="margin:0 5px;">
        <div class="row">
            @includeIf('Share.message')

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
            <div class="col-md-4 align-right" id="njcAddr" style="margin-right: 0px !important;"><span class="pull-right text-success"><em>Form SCN 119C</em></span> <br>
                ISALU HOSPITAL LIMITED,<br>
                Three Arms Zone, <br>
                ISALU HOSPITAL LIMITED Complex,<br>
                PMB 483, Abuja.<br>
                09-6705701<br>
                Ref....................................

            </div>
        </div>

        <div class="row statusBody">
            <div class="text-center">Please note the following in your records and publish in the next issue of the official Gazette</div>
            <h5 class="text-center" style="font-weight: bold;">OFFICIAL MANUSCRIPT <br> PROMOTION</h5>

            <div class="col-md-12" style="font-size:16px; margin-bottom:7px;">
                &nbsp;&nbsp;&nbsp;&nbsp;Date: {{date('d-M-Y')}}

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Court/SCN</th>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Current Rank</th>
                            <th>Date of Upgrading & Advancement</th>
                        </thead>

                        <form action="{{url('save-promoted-staff-gazette')}}" method="POST" id="submitGazette">
                            @csrf
                            <tbody>
                                @php $key = 1; @endphp
                                @forelse ($promoted as $key => $item)
                                    <tr>
                                        <td></td>
                                        <td>{{$key + 1}}</td>
                                        <td>{{$item->surname. ' '. $item->first_name. ' '. $item->othernames}}</td>
                                        <td>{{$item->currentRank}}</td>
                                        <td>{{date('d-M-Y', strtotime($item->updated_at))}}</td>
                                        <input type="hidden" value="{{$item->currentRank}}" name="rank[]">
                                        <input type="hidden" value="{{$item->staffid}}" name="staffid[]">
                                        <input type="hidden" value="{{$item->updated_at}}" name="upgradeAdvanceDate[]">
                                    </tr>
                                @empty
                                    <tr><em><h4><strong class="text-danger"> No promoted staff pending for gazetting. </strong></h4></em></tr>
                                @endforelse

                            </tbody>
                        </form>
                    </table>
                </div>

            </div>



        </div>

        <div class="row">
            <div class="col-md-12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;With the publication on Gazette Branch, please complete the following sectionand return the duplicate
                copy to this office as soon as possible through the Central Records Office

                <p class="pull-right" style="margin-top: 20px;">
                    For: Secretary <br>
                    ISALU HOSPITAL LIMITED
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <p>To be published in the official Gazette No..................................................................................................................................................................</p><br>
                <p>Signed......................................................................................................................................................................................(P & G Branch) </p><br>

                <div class="pull-left">Note in records:</div> <div class="pull-right">Signed....................................</div>
            </div>

        </div>
    </div>
</div>

<div>
    <button type="btn" class="btn btn-primary align-left" onclick="printLetter()">Print Gazette</button>


    <button type="Submit" form="submitGazette" class="btn btn-primary align-right">Create Gazette</button>


</div>

@endsection

<script>
    function printLetter(){

      var divToPrint = document.querySelector('#printLetter');
      var htmlToPrint = '' +
          '<style type="text/css">' +
            'table th, table td {' +
            'border:1px solid #000;' +
            'padding:0.5em;' +
            '}' +
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
