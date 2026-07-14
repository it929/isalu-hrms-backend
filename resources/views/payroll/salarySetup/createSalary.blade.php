@extends('layouts.layout')
@section('pageTitle')
  
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
  .rowz
  {
    display: inline-block;
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
  fieldset { 
    display: block;
    margin-left: 2px;
    margin-right: 2px;
    padding-top: 0.35em;
    padding-bottom: 0.625em;
    padding-left: 0.75em;
    padding-right: 0.75em;
    border: 2px solid #eee;
}
</style>

@section('content')

 

<div class="box box-default" style="border-top: none; background:#FFF;">
 
</div>

    <div style="margin: 10px 20px;">
      <div align="center">
        <h3><b>{{strtoupper('ISALU HOSPITAL LIMITED')}}</b></h3>
        
      </div>
          
      <br />
      @if (count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Error!</strong> 
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                  </div>
                @endif
    @if(session('err'))
      <div class="col-sm-12 alert alert-warning alert-dismissible hidden-print" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
      </button>
      <strong>Error!</strong> 
      {{ session('err') }} 
      </div>                        
   @endif
    @if(session('msg'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Success!</strong> 
            {{ session('msg') }}
          </div>                        
          @endif

  </div>

<div class="box-body" style ="background: #FFF;">

 <form method="post" action="{{url('/salary/create')}}" >
{{ csrf_field() }}



<div class="row" style="padding: 1px 12px; margin-bottom: 10px;">
  <div class="col-md-8 col-md-offset-2" style="background: #eee; padding: 10px 15px">
           
@if ($CourtInfo->courtstatus==1)
        <div class="col-md-5" style="padding: 1px;">
              <div class="form-group">
                <label>Select Court</label>
                <select name="court" id="court" class="form-control input-lg" style="font-size: 13px;">
                  <option value="">Select Court</option>
                  @foreach($courts as $court)
                   @if($court->id == session('court'))
                  <option value="{{$court->id}}" selected="selected">{{$court->court_name}}</option>
                  @else
                  <option value="{{$court->id}}" @if(old('court') == $court->id) selected @endif>{{$court->court_name}}</option>
                  @endif
                  @endforeach
                </select>
                 
              </div>
            </div>
          @else
            <input type="hidden" id="court" name="court" value="{{$CourtInfo->courtid}}">
          @endif
               <div class="col-md-2" style="padding: 1px;">
                <div class="form-group">
                  <label class="control-label">Employee Type</label>
                 
                    <select class="form-control input-lg" id="employeeType" name="employeeType">
                    
                    <option value="">Select</option>
                    @foreach($emptype as $type)
                  
                     @if($type->id == session('employeeType'))
                     <option value="{{$type->id}}" selected="selected" @if(old('employeeType') == $type->id) selected @endif>{{ $type->employmentType }}</option>
                     @else
                     <option value="{{$type->id}}" @if(old('employeeType') == $type->id) selected @endif>{{ $type->employmentType }}</option>
                     @endif
                      
                      
                      @endforeach
                    </select>
                 
                </div>
              </div>
            <div class="col-md-2" style="font-size: 13px;">
              <div class="form-group">
                <label>Grade</label>
                  <select name="grade" id="grade" class="form-control input-lg">

                  <option value=""></option>
                  
                   @for($i=1;$i<=17;$i++)
                   
                     @if(session('grade') == $i)
                    <option selected="selected" value="{{$i}}">{{$i}}</option>
                     
                     @else
                     <option value="{{$i}}" @if(old('grade') == $i) selected @endif>{{$i}}</option>
                    @endif
                    @endfor
                  
                </select>
              </div>
            </div>
            
            <div class="col-md-2" style="padding: 1px;font-size: 13px;">
              <div class="form-group">
                <label>Step</label>
                  <select name="step" class="form-control input-lg" id="step">

                  <option value=""></option>
                  @for($i=1;$i<=17;$i++)
                   
                     @if(session('step') == $i)
                    <option selected="selected" value="{{$i}}">{{$i}}</option>
                     
                     @else
                     <option value="{{$i}}" @if(old('step') == $i) selected @endif>{{$i}}</option>
                    @endif
                    @endfor
                </select>
                   
              </div>
            </div>
            <div class="col-md-1" style="padding: 1px;">
              <div class="form-group" style="padding-top: 23px;">
                
                <input type="submit" name="submit" id="display" class="btn btn-default input-lg" value="Display" />  
              </div>
            </div>
  </div>
</div>

<!--salary input form -->


<div class="panel">
<div class="form-horizontal">
@if(session('verify') == 'update')
          
            
                <h3 class="text-center">EARNINGS</h3> 
       
                <div class="col-md-1">
                </div>
                 <div class="row">
                 
                  <div class="col-md-2">
                   <label >Basic Salary</label>
                   <input type="text" class="form-control" name="basic" id="" value="@if(count($errors) > 0){{old('basic')}}@elseif($scale !=''){{$scale->amount}} @endif ">
                   <input type="hidden" class="form-control" name="id" id="" value="@if($scale != ''){{$scale->ID}} @endif">
                  </div>
                
                
                  
                  <div class="col-md-2">
                    <label >Leave Bonus</label>
                    <input type="text" class="form-control" name="leaveBonus" id="" value=" @if(count($errors) > 0){{old('leaveBonus')}}@elseif($scale !=''){{$scale->leave_bonus}} @endif">
                  </div>
                
                  <div class="col-md-2">
                    <label>Peculiar</label>
                    <input type="text" class="form-control popover-button-default" name="peculiar" value="@if(count($errors) > 0){{old('peculiar')}}@elseif($scale !=''){{$scale->peculiar}} @endif">
                  </div>
                
                  <div class="col-md-2">
                   <label>Housing</label>
                    <input type="text" class="form-control popover-button-default" name="housing" value="@if(count($errors) > 0){{old('housing')}}@elseif($scale !=''){{$scale->housing}} @endif">
                  </div>
               
                  <div class="col-md-2">
                   <label>Transport</label>
                    <input type="text" class="form-control popover-button-default" name="transport" value="@if(count($errors) > 0){{old('transport')}}@elseif($scale !=''){{$scale->transport}} @endif">
                  </div>
                
                <div class="col-md-1">
                </div>

                 <div class="col-md-2">
                   <label>Utility</label>
                    <input type="text" class="form-control popover-button-default" name="utility" value="@if(count($errors) > 0){{old('utility')}}@elseif($scale !=''){{$scale->utility}} @endif">
                  </div>
                

                 <div class="col-md-2">
                   <label>Furniture</label>
                    <input type="text" class="form-control popover-button-default" name="furniture" value="@if(count($errors) > 0){{old('furniture')}}@elseif($scale !=''){{$scale->furniture}} @endif">
                  </div>
                

                 <div class="col-md-2">
                   <label>Meal</label>
                    <input type="text" class="form-control popover-button-default" name="meal" value="@if(count($errors) > 0){{old('meal')}}@elseif($scale !=''){{$scale->meal}}@endif">
                  </div>
               

                 <div class="col-md-2">
                   <label>Driver</label>
                    <input type="text" class="form-control popover-button-default" name="driver" value="@if(count($errors) > 0){{old('driver')}}@elseif($scale !=''){{$scale->driver}} @endif">
                  </div>
                

                 <div class="col-md-2">
                   <label>Servant</label>
                    <input type="text" class="form-control popover-button-default" name="servant" value="@if(count($errors) > 0){{old('servant')}}@elseif($scale !=''){{$scale->servant}} @endif">
                  </div>          
                </div>
                 
                 <div class="col-md-1">
                </div>
                
                  <br>
                  <h3 class="text-center" style="position: center">DEDUCTIONS</h3>
                  <br>
                   <div class="col-md-4">
                </div>
                <div class="row"> 
                  <div class="col-md-2">
                    <label>Tax</label>
                      <input type="text" class="form-control" name="tax" value="@if(count($errors) > 0){{old('tax')}}@elseif($scale !=''){{$scale->tax}} @endif">
                    </div>
                  

                  <div class="col-md-2">
                    <label>Pension</label>
                      <input type="text" class="form-control" name="pension" value="@if(count($errors) > 0){{old('pension')}}@elseif($scale !=''){{$scale->pension}} @endif">
                    </div>
                  
                 		<div class="col-md-4">
                    </div>

                  <div class="col-md-2">
                    <label>NHF</label>
                      <input type="text" class="form-control" name="nhf" value="@if(count($errors) > 0){{old('nhf')}}@elseif($scale !=''){{$scale->nhf}} @endif">
                    </div>
                  

                  <div class="col-md-2">
                    <label>Union Dues</label>
                      <input type="text" class="form-control" name="unionDues" value="@if(count($errors) > 0){{old('unionDues')}}@elseif($scale !=''){{$scale->unionDues}} @endif">
                    </div>
                   </div>
                 
                    </br>
                   <div class="col-md-2" style="text-align: center; margin-left:0px auto;">
                     <input type="submit" class="btn btn-success" id="btn" name="submit" value="Update">
                   </div>
               

  @endif              
                
  
@if(session('verify') == 'add_new')
 
     
         <h3 class="text-center">EARNINGS</h3>  
          
            <div class="col-md-1">
            </div>
            <div class="row">
                <div class="col-md-2">
                  <label>Basic Salary</label>
                    <input type="text" class="form-control" name="basic" id="" value="{{old('basic')}}">
                    <input type="hidden" class="form-control" name="id" id="">
                </div>
                
                <div class="col-md-2">
                  <label>Leave Bonus</label>
                    <input type="text" class="form-control" name="leaveBonus" id="" value="{{old('leaveBonus')}}">
                  </div>
                
                <div class="col-md-2">
                  <label>Peculiar</label>
                    <input type="text" class="form-control popover-button-default" name="peculiar" value="{{old('peculiar')}}">
                </div>
                
                <div class="col-md-2">
                  <label>Housing</label>
                    <input type="text" class="form-control popover-button-default" name="housing" value="{{old('housing')}}">
                </div>
                

                <div class="col-md-2">
                  <label>Transport</label>
                    <input type="text" class="form-control popover-button-default" name="transport" value="{{old('transport')}}">
                </div>
            <div class="col-md-1">
            </div>
              
                	
                <div class="col-md-2">
                  <label>Utility</label>
                    <input type="text" class="form-control popover-button-default" name="utility" value="{{old('utility')}}">
                </div>
                

                <div class="col-md-2">
                  <label>Furniture</label>
                    <input type="text" class="form-control popover-button-default" name="furniture" value="{{old('furniture')}}">
                </div>
                

                <div class="col-md-2">
                  <label>Meal</label>
                    <input type="text" class="form-control popover-button-default" name="meal" value="{{old('meal')}}">
                </div>
                

                <div class="col-md-2">
                  <label>Driver</label>
                    <input type="text" class="form-control popover-button-default" name="driver" value="{{old('driver')}}">
                </div>
                

                <div class="col-md-2">
                  <label>Servant</label>
                    <input type="text" class="form-control popover-button-default" name="servant" value="{{old('servant')}}">
                </div>                    
            </div>
               <div class="col-md-1">
                </div> 
                  <br>
                <h3 class="text-center">DEDUCTIONS</h3>
                  <br>
              <div class="col-md-4">
                </div>    
               <div class="row">
                 <div class="col-md-2 col-lg-2">
                   <label>Tax</label>
                     <input type="text" class="form-control" name="tax" value="{{old('tax')}}">
                  </div>
                

                <div class="col-md-2 col-lg-2">
                  <label>Pension</label>
                    <input type="text" class="form-control" name="pension" value="{{old('pension')}}">
                  </div>
                
                <div class="col-md-4">
                </div>

                <div class="col-md-2 col-lg-2">
                  <label>NHF</label>
                    <input type="text" class="form-control" name="nhf" value="{{old('pension')}}">
                  </div>
                

                <div class="col-md-2 col-lg-2">
                  <label>Union Dues</label>
                    <input type="text" class="form-control" name="unionDues" value="{{old('unionDues')}}">
                  </div>
             </div>   
                </br>
                 <div class="col-md-2" style="text-align: center; margin-left:0px auto;">
                    <input type="submit" class="btn btn-success" id="btn" name="submit" value="Submit">
                 </div>
            
                

  
  @endif

   </div>
   </div>
<!-- end salary input form -->

</form>
</div><!-- end box-body-->

@endsection


@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script type="text/javascript">


   $(document).ready(function(){
   //$('#salary').hide();
    $("#salary").click(function(){
    // $('#display').show();
    // $('#salary').show();
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
  
  }
});

   

});
 });



  $(function(){
  $('.form-control').bind('input', function(){
    $(this).val(function(_, v){
     return v.replace(/\s+/g, '');
    });
  });
});
</script> 

<script type="text/javascript">
$(document).ready(function()
{

$("#court").on('change',function(){
$("#btn").prop('disabled', true);
  });
  $("#employeeType").on('change',function(){
$("#btn").prop('disabled', true);
  });
  $("#grade").on('change',function(){
$("#btn").prop('disabled', true);
  });
  $("#step").on('change',function(){
$("#btn").prop('disabled', true);
  });


  $('#display').click(function()
  {
     $("#btn").prop('disabled', false);
  });

});
</script>


@stop

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">

  <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@stop
