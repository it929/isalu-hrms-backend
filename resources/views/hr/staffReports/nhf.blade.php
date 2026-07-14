  @extends('layouts.layout')
@section('pageTitle')
   NHF
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

    <div class="box-header with-border hidden-print">
        <h3 class="box-title"><b>@yield('pageTitle')</b> <i class="fa fa-arrow-right"></i>  <span id='processing'><strong><em>NHF Staff Report.</em></strong></span></h3>
    </div>

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
                </a>
            </span>-->
        </form>
    </div>

    <div style="margin: 10px 20px;">
      <div align="center">
        <h3><b>{{strtoupper('ISALU HOSPITAL LIMITED')}}</b></h3>

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

  <div class="box-body">
<div></div>
 <form method="post" action="{{url('/staff/nhf/search')}}">
          {{ csrf_field() }}
<div class="row" style="padding: 1px 12px; margin-bottom: 20px;">
  <div class="col-md-12" style="background: #eee; padding: 10px 15px">
           <div class="col-md-3" style="padding: 1px;;">
              <div class="form-group">
                <label>Select Court</label>
                <select name="Court" id="court" class="form-control input-lg" style="font-size: 13px;">
                  <option value="">Select Court</option>
                  @foreach($courts as $court)
                   @if($court->id == session('current_court'))
                  <option value="{{$court->id}}" selected="selected">{{$court->court_name}}</option>
                  @else
                  <option value="{{$court->id}}">{{$court->court_name}}</option>
                  @endif
                  @endforeach
                </select>

              </div>
            </div>
            <div class="col-md-2" style="font-size: 13px;">
              <div class="form-group">
                <label>Division</label>
                 <select name="division" id="division" class="form-control input-lg" style="font-size: 13px;">
                  <option value="">Select Division</option>
                  @foreach($division as $d)
                  <option value="{{$d->divisionID}}">{{$d->division }}</option>
                  @endforeach
                </select>

              </div>
            </div>
            <div class="col-md-3" style="font-size: 13px;">
              <div class="form-group">
                <label>Section</label>
                  <select name="section" id="dept" class="form-control input-lg" style="font-size: 13px;">
                  <option value="">Select Section</option>
                   @foreach($department as $d)
                  <option value="{{$d->id}}">{{$d->department }}</option>
                  @endforeach
                </select>

              </div>
            </div>
            <div class="col-md-2" style="padding: 1px;font-size: 13px;font-weight: 100;">
              <div class="form-group">
                <label>File Number</label>
                <input type="text" name="staffNo" id="staffNo" class="form-control input-lg" value="{{ old('fileNo') }}" style="font-size: 13px;"/>
                <input type="hidden" name="fileNo" id="fileNo" class="form-control input-lg" style="font-size: 13px;"/>
              </div>
            </div>
            <div class="col-md-1" style="padding: 1px;font-size: 13px;">
              <div class="form-group">
                <label>Grade</label>
                  <select name="grade" class="form-control input-lg">

                  <option value=""></option>
                  <?php
                   for($i=1;$i<=17;$i++)
                   {
                    echo '<option value="'.$i.'">'.$i.'</option>';
                   }
                  ?>
                </select>

              </div>
            </div>
            <div class="col-md-1" style="padding: 1px;">
              <div class="form-group" style="padding-top: 23px;">

                <input type="submit" name="submit" id="fileNo" class="btn btn-default input-lg" value="Display" />
              </div>
            </div>
  </div>
</div>
</form>

    <div class="row">
      {{ csrf_field() }}

      <div class="col-md-12">
        <table class="table table-striped table-condensed table-bordered ">
          <thead>
          <tr>
              <th>S/N</th>
              <th width="250" class="">STAFF NO</th>
              <th>STAFF NAME</th>
              <th>GRADE</th>
              <th>STEP</th>
              <th>NHF NUMBER</th>
              <th>COURT</th>

              </tr>
          </thead>
          <tbody>
          @php $key = 1; @endphp
          @foreach($staff as $s)
          <tr>
              <td>{{($staff->currentpage()-1) * $staff->perpage() + $key ++}}</td>
              <td>{{$s->fileNo}}</td>
              <td>{{$s->surname}} {{$s->first_name}} {{$s->othernames}}</td>
              <td>{{$s->grade}}</td>
              <td>{{$s->step}}</td>
              <td>{{$s->nhfNo}}</td>
              <td>{{$s->court_name}}</td>


              </tr>
            @endforeach
          </tbody>
        </table>

        <div align="left">
        <form method="post" action="{{url('/export/nhf')}}">
        {{ csrf_field() }}
          <input type="submit" name="export" value="Export To Excel" class="btn btn-primary input-lg">
          <!--<input type="submit" name="export" value="Export To PDF" class="btn btn-primary input-lg">
        </form>
        </div>


        <div class="hidden-print"></div>
      </div>
    </div><!-- /.col -->
  </div><!-- /.row -->
</div>


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
      url: murl +'/new-staff/getcourt',
      type: "post",
      data: {'courtID': $('#court').val(), '_token': $('input[name=_token]').val()},

      success: function(data){

    $('#division').empty();
     $('#division').append( '<option value="">Select Division</option>')
        $.each(data, function(index, obj){
        $('#division').append( '<option value="'+obj.divisionID+'">'+obj.division+'</option>');
        });

      }
    })
  });}) ();


 (function () {
  $('#court').change( function(){
    //$('#processing').text('Processing. Please wait...');
    $.ajax({
      url: murl +'/new-staff/getdepartments',
      type: "post",
      data: {'courtID': $('#court').val(), '_token': $('input[name=_token]').val()},

      success: function(data){

    $('#dept').empty();
        $('#dept').append( '<option value="">Select Section</option>' );
        $.each(data, function(index, obj){
        $('#dept').append( '<option value="'+obj.id+'">'+obj.department+'</option>' );
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







