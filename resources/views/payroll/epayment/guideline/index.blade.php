@extends('layouts.layout')
@section('pageTitle')
   e-Payment Guideline
@endsection

<style type="text/css">
  .form-control
  {
    font-size: 13px;

  }
  .col-md-12
  {
    padding: 0px 5px;
  }
  .table tr td
  {
    font-size: 13px;
    padding:13px;
    font-family: Verdana, Geneva, sans-serif;
  }
  .table tr th
  {
    padding: 15px;
    font-size: 13px;
    text-transform: uppercase;
    font-family: Verdana, Geneva, sans-serif;
    color: #262626;
    background: #eee;
  }
  .input-lg
  {
    padding: 5px !important;
  }
</style>

@section('content')


    <div class="box box-default" style="border-top: none;">
        <form action="{{url('/manpower/view/central')}}" method="post">
            {{ csrf_field() }}
            <div class="box-header with-border hidden-print">
            <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            <span class="pull-right" style="margin-right: 30px;">
                <div style="float: left;">
                <div class="wrap">
                
                </div>
                </div>
            </span>
        </form>
       
            <span class="hidden-print">
                 <!--<span class="pull-right" style="margin-left: 5px;">
                  <div style="float: left; width: 100%; margin-top: -20px;">
                     <button type="submit" class=" btn btn-default" style="padding: 6px; border-radius: 0px;">Staff Due for Increment Today</button>
                  </div>
                  <input type="hidden" id="monthDay"  name="monthDay" value="{{date('Y-m-d')}}">
                  <input type="hidden" id="fileNo"  name="fileNo" value="">
                  <input type="hidden" id="filterDivision"  name="filterDivision" value="">
                </span>
                <a href="{{url('/map-power/view/central')}}" title="Refresh" class="pull-right">
                  <i class="fa fa-refresh"></i> Refresh
                </a>-->
            </span>
        {{-- </form> --}}
    </div>

    <div style="margin: 10px 20px;">
        <div align="center">
            
            @include('layouts._companyInfoPartial')
        </div>
            
        <br />
            @if(session('err'))
                <div class="col-sm-12 alert alert-warning alert-dismissible hidden-print" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong> 
                {{ session('err') }} 
                </div>                        
            @endif

    </div>

    <div id="contain1" class="box-body panel">
        <div> 
           <h4>IMPLEMENTATION OF e-PAYMENT GUIDELINE FOR ISALU HOSPITAL LIMITED</h4>
           <p>The Manager,</p>
           <p>
                @if ($bank_name != NULL )
                {{$bank_name}},
                 @endif
            </p>
           <p>{{$curr_date}}</p>
        </div>
        <br><br>
        <form method="post" action="{{url('/epayment/guideline/retrieve')}}" class="hidden-print">
                {{ csrf_field() }}
            <div class="row" style="padding: 1px 12px; margin-bottom: 20px;">
                <div class="col-md-12" style="background: #eee; padding: 10px 15px">
                    <div class="col-md-3" style="padding: 1px;">
                        <div class="form-group">
                            <label >Select a Year</label>
                            <select name="year" id="section" class="form-control">
                                <option value="">Select Year</option>
                                @for($i=2022; $i<=2050; $i++)
                                    <option value="{{$i}}" @if(session('year') == $i) selected @endif>{{$i}}</option>
                                @endfor
                            </select>
                            
                        </div>
                    </div>
                    <div class="col-md-3" style="font-size: 13px;">
                        <div class="form-group">
                            <label> Select a Month </label>
                            <select name="month" id="section" class="form-control">
                                <option value="">Select Month </option>
                                <option value="JANUARY" @if(session('month') == 'JANUARY') selected @endif>January</option>
                                <option value="FEBRUARY" @if(session('month') == 'FEBRUARY') selected @endif>February</option>
                                <option value="MARCH" @if(session('month') == 'MARCH') selected @endif>March</option>
                                <option value="APRIL" @if(session('month') == 'APRIL') selected @endif>April</option>
                                <option value="MAY" @if(session('month') == 'MAY') selected @endif>May</option>
                                <option value="JUNE" @if(session('month') == 'JUNE') selected @endif>June</option>
                                <option value="JULY" @if(session('month') == 'JULY') selected @endif>July</option>
                                <option value="AUGUST" @if(session('month') == 'AUGUST') selected @endif>August</option>
                                <option value="SEPTEMBER" @if(session('month') == 'SEPTEMBER') selected @endif>September</option>
                                <option value="OCTOBER" @if(session('month') == 'OCTOBER') selected @endif>October</option>
                                <option value="NOVEMBER" @if(session('month') == 'NOVEMBER') selected @endif>November</option>
                                <option value="DECEMBER" @if(session('month') == 'DECEMBER') selected @endif>December</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" style="font-size: 13px;">
                        <div class="form-group">
                            <label> Select a Bank </label>
                            <select name="bankName" id="bankName" class="form-control">
                                <option value="">Select Bank </option>
                                @foreach($allbanklist as $list)
                                    <option value="{{$list->bankID}}" @if(session('bankName') == $list->bankID) selected @endif>
                                        {{$list->bank}} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" style="padding: 1px;">
                        <div class="form-group" style="padding: 20px;">
                            <input type="submit" name="submit" id="" class="form-control btn btn-default input-md" value="Display" />  
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            {{ csrf_field() }}

            <div class="col-md-12">
                <table id="mytable" class="table table-striped table-condensed table-bordered ">
                    <thead>
                        <tr>
                            <th class="">S/N</th>
                            <th class="">Division</th>
                            {{-- <th class="">BANK</th> --}}
                            <th class="">Total(#)</th>
                            <th>PURPOSE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $key = 1; 
                            $totalSum = 0;
                        @endphp
                            @foreach($myDivisions as $myDivision)
                                <tr>
                                    <th>{{$key++}}</th>
                                    <th style="text-align: left"> {{$myDivision->division}} </th>
                                    {{-- <th>
                                        {{$myDivision->bank}}
                                    </th> --}}
                                    <th style="text-align: right">
                                        {{number_format($myDivision->myEarn, 2, '.', ',')}}
                                    </th>
                                    <th>{{$myDivision->month}} SALARY</th>
                                </tr>
                            @endforeach

                            <tr>
                                <th style="background-color: white; font-weight:800; font-size:20px" colspan="2" >Grand Total</th>
                                {{-- <th></th>
                                <th></th>
                                <th></th> --}}
                                <th style="font-weight:800; font-size:20px; text-align: right">
                                    {{ $grandTotal}}</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="4" style="background-color: white;">
                                    <div class="row" style="margin-top: 30px">
                                        <div class="col-md-6" style="padding: 10px">
                                             <p>
                                                <strong>AUTHORIZED SIGNATORY:</strong> ____________________ 
                                             </p>
                                             <p>
                                                <strong>NAME:</strong> ____________________________________
                                             </p>
                                        </div>
                                        <div class="col-md-6" style="padding: 10px">
                                            <p>
                                                <strong>AUTHORIZED SIGNATORY:</strong> ____________________ 
                                             </p>
                                             <p>
                                                <strong>NAME:</strong> ____________________________________
                                             </p>
                                        </div>
                                     </div>
                                </th>
                            </tr>
                    </tbody>
                    
                </table>
                
                {{-- <div class="pagination"> {{ $staff->links() }}</div> --}}
                {{-- <br><br> --}}
                <div align="left">
                    {{-- <form method="post" action="{{url('/epayment/guideline')}}" class="hidden-print"> --}}
                        {{ csrf_field() }}
                        {{-- <input type="submit" name="export" value="Export To Excel" class="btn btn-primary input-lg"> --}}
                        {{-- <input type="submit" onclick="print()" name="export" value="Print" class="btn btn-primary btn-md"> --}}
                    {{-- </form> --}}

                    <a href="javascript:void(0)"  class="btn btn-success print-window" id="print-window">Print</a> 
                </div>

            </div><!-- /.col -->
        </div><!-- /.row -->
    </div> <!-- box-body -->


{{-- ////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
@section('styles')
    <style>

        @media print {
            body {
            zoom: 90%;
          }
        @page{
            /* size: A4; */
            margin-top: 70px;
            margin-bottom: 20px;
          }

          .table td{
            border: #030303 solid 1px !important;
            padding: 2px;
            font-size: 11px;
            }

            #print-window {
                display: none;
            }
            .table th{
            border: #030303 solid 1px !important;
            }

        /* table {
            page-break-before: always;
            font-size: 100px;} */
        tr{page-break-inside: avoid;
        page-break-after: auto;}
        table { page-break-after:auto;
        width:100% }

          td    { page-break-inside:avoid; page-break-after:auto }



           * { color: black; background: white; }
           table { font-size: 100%; }
        }
        table tr th
        {
          text-align:center;
        }


    </style>

@endsection


@section('scripts')
    <script>
        $('.print-window').click(function() {
            var element = document.getElementById("contain1");
            element.classList.remove("panel");
            window.print();
            element.classList.add("panel");
        });

        // function UpdateData()
        // {
        //     var court= document.getElementById('court').value ;
        //     $.get('/get-division?court_id='+court, function(data){
        //         $('#division').empty();
        //         $('#division').append( '<option value="">-Select Division-</option>' );
        //         $.each(data, function(index, obj){
        //             $('#division').append( '<option value="'+obj.id+'">'+obj.divname+'</option>' );
        //         });
        //     });
        // }

    </script>
@stop

{{-- ////////////////////////////////////////////////////////////////////////////////////////////////////// --}}




















<!-- Bootsrap Modal for Conversion and Advancemnet-->
<form method="post" action="">
    {{ csrf_field() }}
    <div id="advModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Candidate Due For Conversion/Advancement</h4>
                    <p id="message"></p>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Post for Consideration</label>
                        <input type="text" name="postConsidered" id="postcon" class="form-control"  >
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" id="type" class="form-control type ">
                        
                        <option value="Promotion">Promotion</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>New Grade Level</label>
                        <select name="newGrade" id="newGrade" class="form-control grade" >
                        <option value="">Select New Grade</option>
                        <option value="1" {{ (old("grade") == "1" ? "selected":"") }}>1</option>
                        <option value="2" {{ (old("grade") == "2" ? "selected":"") }}>2</option>
                        <option value="3" {{ (old("grade") == "3" ? "selected":"") }}>3</option>
                        <option value="4" {{ (old("grade") == "4" ? "selected":"") }}>4</option>
                        <option value="5" {{ (old("grade") == "5" ? "selected":"") }}>5</option>
                        <option value="6" {{ (old("grade") == "6" ? "selected":"") }}>6</option>
                        <option value="7" {{ (old("grade") == "7" ? "selected":"") }}>7</option>
                        <option value="8" {{ (old("grade") == "8" ? "selected":"") }}>8</option>
                        <option value="9" {{ (old("grade") == "9" ? "selected":"") }}>9</option>
                        <option value="10" {{ (old("grade") == "10" ? "selected":"") }}>10</option>
                        <option value="11" {{ (old("grade") == "11" ? "selected":"") }}>11</option>
                        <option value="12" {{ (old("grade") == "12" ? "selected":"") }}>12</option>
                        <option value="13" {{ (old("grade") == "13" ? "selected":"") }}>13</option>
                        <option value="14" {{ (old("grade") == "14" ? "selected":"") }}>14</option>
                        <option value="15" {{ (old("grade") == "15" ? "selected":"") }}>15</option>
                        <option value="16" {{ (old("grade") == "16" ? "selected":"") }}>16</option>
                        <option value="17" {{ (old("grade") == "17" ? "selected":"") }}>17</option>
                        </select>
                        <input type="hidden" name="fileNo" id="fileNo" class="form-control file-number" >
                    </div>

                    <div class="form-group">
                        <label>New Step</label>
                        <select name="newStep" id="newStep" class="form-control step" >
                        <option value="">Select New Grade</option>
                        <option value="1" {{ (old("grade") == "1" ? "selected":"") }}>1</option>
                        <option value="2" {{ (old("grade") == "2" ? "selected":"") }}>2</option>
                        <option value="3" {{ (old("grade") == "3" ? "selected":"") }}>3</option>
                        <option value="4" {{ (old("grade") == "4" ? "selected":"") }}>4</option>
                        <option value="5" {{ (old("grade") == "5" ? "selected":"") }}>5</option>
                        <option value="6" {{ (old("grade") == "6" ? "selected":"") }}>6</option>
                        <option value="7" {{ (old("grade") == "7" ? "selected":"") }}>7</option>
                        <option value="8" {{ (old("grade") == "8" ? "selected":"") }}>8</option>
                        <option value="9" {{ (old("grade") == "9" ? "selected":"") }}>9</option>
                        <option value="10" {{ (old("grade") == "10" ? "selected":"") }}>10</option>
                        <option value="11" {{ (old("grade") == "11" ? "selected":"") }}>11</option>
                        <option value="12" {{ (old("grade") == "12" ? "selected":"") }}>12</option>
                        <option value="13" {{ (old("grade") == "13" ? "selected":"") }}>13</option>
                        <option value="14" {{ (old("grade") == "14" ? "selected":"") }}>14</option>
                        <option value="15" {{ (old("grade") == "15" ? "selected":"") }}>15</option>
                        <option value="16" {{ (old("grade") == "16" ? "selected":"") }}>16</option>
                        <option value="17" {{ (old("grade") == "17" ? "selected":"") }}>17</option>
                        </select>
                        <input type="hidden" name="fileNo" id="fileNo" class="form-control file-number" >
                    </div>

                    <div class="form-group">
                        <label>Effective Date</label>
                        <input type="text" name="effectiveDate" id="effectiveDate" class="form-control effectiveDate" >

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary adv" id="adv">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- //// Bootsrap Modal for Conversion and Advancemnet-->



@endsection






@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script type="text/javascript">


   $(document).ready(function(){
  
    $("table tr td .promote").click(function(){
      var fileNo = $(this).attr('id');
        $("#advModal").modal('show');
        $(".file-number").val(fileNo);
    });
});


  $(function() {
      $("#staffNo").autocomplete({
        serviceUrl: murl + '/report/search',
        minLength: 10,
        onSelect: function (suggestion) {
            $('#fileNo').val(suggestion.data);
           
        }
      });
  });

  $("#searchDate").datepicker({
    changeMonth: true,
    changeYear: true,
    yearRange: '1910:2090', // specifying a hard coded year range
    showOtherMonths: true,
    selectOtherMonths: true,
    dateFormat: "dd MM, yy",
    onSelect: function(dateText, inst){
      var theDate = new Date(Date.parse($(this).datepicker('getDate')));
      var dateFormatted = $.datepicker.formatDate('yy-mm-d', theDate);
       $('#fileNo').val($.datepicker.formatDate('yy-m-d', theDate));
    },
  });

</script>


<script type="text/javascript">
 (function () {
  $('#court').change( function(){
    //$('#processing').text('Processing. Please wait...');
    $.ajax({
     // url: murl +'/new-staff/getcourt',
      type: "post",
      data: {'courtID': $('#court').val(), '_token': $('input[name=_token]').val()},

      success: function(data){
      
    $('#division').empty(); 
     $('#division').append( '<option value="">Select Division</option>')
        $.each(data, function(index, obj){
       // $('#division').append( '<option value="'+obj.divisionID+'">'+obj.division+'</option>');
        });
        
      }
    })  
  });}) ();


 (function () {
  $('#court').change( function(){
    //$('#processing').text('Processing. Please wait...');
    $.ajax({
     // url: murl +'/new-staff/getdepartments',
      type: "post",
      data: {'courtID': $('#court').val(), '_token': $('input[name=_token]').val()},

      success: function(data){
     
    $('#dept').empty(); 
        $('#dept').append( '<option value="">Select Section</option>' );
        $.each(data, function(index, obj){
        //$('#dept').append( '<option value="'+obj.id+'">'+obj.department+'</option>' );
        });
        
      }
    })  
  });}) ();


</script>

<script type="text/javascript">
  $(document).ready(function(){
  
$("#court").on('change',function(){
  var id = $(this).val();

  $token = $("input[name='_token']").val();
 $.ajax({
  headers: {'X-CSRF-TOKEN': $token},
  url: "{{ url('/court/setsession') }}",

  type: "post",
  data: {'courtID':id},
  success: function(data){
  location.reload(true);
  }
});

   

});
 });
</script> 


@stop

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">

  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@stop







