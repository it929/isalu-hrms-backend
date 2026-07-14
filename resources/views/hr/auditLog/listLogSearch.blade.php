@extends('layouts.layout')
@section('pageTitle')
@endsection

<style type="text/css">
.table, .table tr td, .table tr th
{
border:1px solid #333;
}
.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
    border-top: 1px solid #333;
}
</style>

@section('content')
<div class="box box-default" style="border-top: none;">
  <div style="margin: 10px 20px;"> @if(session('err'))
    <div class="alert alert-warning alert-dismissible hidden-print" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span> </button>
      <strong>Error!</strong> {{ session('err') }} </div>
    @endif </div>
  <div class="box-body">
    <div class="row" >
      <div class="col-xs-2"><img src="{{asset('Images/logo.jpg')}}" class="responsive"></div>
      <div class="col-xs-8">
        <div>
          <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
          <h4 class="text-center text-success"><strong>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA`</strong></h4>
         <h4 class="text-center text-success"><strong>Audit Log</strong></h4>
        </div>
      </div>
      <div class="col-xs-2"><img src="{{asset('Images/coat.jpg')}}" class="responsive"></div>
    </div>

    <div class="row" style="margin-top: 20px;" >
      <div class="col-md-12">
      <div class="col-md-12">
      <div class="form-group" class="hidden-print">
            </div>
      </div>
      </div>
    </div>

    <!-- date picker -->


    <div class="row" style="margin-top:5px; padding-top: 15px; padding-left: 30px; padding-right: 30px;">

        <form action="{{url('/auditlog/search')}}" method="post">
            {{ csrf_field() }}
            <div class="col-md-12 hidden-print" style="padding-top: 0px; margin-top: 5px; font-size: 15px;">

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date" >User  {{session('userses')}}</label>
                            <select name="user" class="form-control input-lg">
                                <option value="">Select</option>
                                @foreach($users as $list)
                                <option value="{{$list->id}}" @if($list->id == Session::get('user_ses')) selected @endif >{{$list->name}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date">Start Date</label>
                            <input type="text" name="dateFrom" id="getFrom" class="form-control input-lg" required  value="{{ Session::get('date_from') }} {{old('dateFrom')}}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date">End Date</label>
                            <input type="text" name="dateTo" id="getTo" class="form-control input-lg" required  value="{{ Session::get('date_to') }} {{old('dateTo')}}">
                        </div>
                    </div>


                    <div class="col-sm-2">
                        <div class="form-group">
                            <label >&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-success input-lg" style="border: #333; border-radius: 0; outline: none !important; margin-left: -25px; padding: 8px;"><i class="fa fa-search"></i> Display</button>
                            </div>
                        </div>
                    </div>
                </div><!-- End Row -->
            </div>
        </form>

        <!-- end date picker -->


        <div class="col-md-12 table-responsive" style="padding-top: 0px; margin-top: 5px; font-size: 15px;">
        <table class="table table-responsive" style="font-size: 15px;border-top: 1px solid #333;">

          <tr  style="border-top: 1px solid #333;">
          @php
          $key = 1;
          @endphp
          <th>S/N</th>
          <th>DATE</th>
          <th>User</th>
          <th>Action Performed</th>
          </tr>
          @if($getLog != '')
           @foreach($getLog as $list)
           <tr>
           <td>{{($getLog->currentpage()-1) * $getLog->perpage() + $key++}}</td>
           <td>{{date( 'd-m-Y H:i:s', strtotime($list->date) )}}</td>
           <td>{{$list->name}}</td>
           <td>{{$list->operation}}</td>
           </tr>
           @endforeach
            @else
            <tr>
           <td colspan ="3">
               <div class="text-center text-danger">No record available<</div>
           </td>

           </tr>
            @endif
        </table>
        <div class="hidden-print ">{{$getLog->links()}}</div>
      </div>
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->

</div>
@endsection
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/daterangepicker.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@endsection
@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/daterangepicker.js') }}" type="text/javascript"></script>

<script type="text/javascript">
  $(function() {
      $('#searchName').attr("disabled", true);
      $("#autocomplete").autocomplete({
        serviceUrl: murl + '/data/searchUser',
        minLength: 2,
        onSelect: function (suggestion) {
            $('#fileNo').val(suggestion.data);
            $('#searchName').attr("disabled", false);
            //showAll();
        }
      });
  });
</script>

<script type="text/javascript">
  $( function() {
	    $("#getFrom").datepicker({
	    	changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd MM, yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
				$("#getFrom").val(dateFormatted);
        	},
		});

  } );

$( function() {
	    $("#getTo").datepicker({
	    	changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd MM, yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
				$("#getTo").val(dateFormatted);
        	},
		});

  } );

 </script>

@endsection

@section('stypes')
<style type="text/css">
    @import url(https://fonts.googleapis.com/css?family=Open+Sans);
    .table, .table tr td, .table tr th
    {
        border: 1px solid #333;
    }
    .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td, .table tr th {
        border-top: 1px solid #333;
    }
body{
  background: #f2f2f2;
  font-family: 'Open Sans', sans-serif;
}

.search {
  width: 100%;
  position: relative;
}

.searchTerm {
  float: left;
  width: 100%;
  border: 3px solid #00B4CC;
  padding: 5px;
  height: 20px;
  border-radius: 5px;
  outline: none;
  color: #9DBFAF;
}

.searchTerm:focus{
  color: #00B4CC;
}

.searchButton {
  position: absolute;
  right: -50px;
  width: 40px;

  height: 36px;
  border: 1px solid #00B4CC;
  background: #00B4CC;
  text-align: center;
  color: #fff;
  border-radius: 5px;
  cursor: pointer;
  font-size: 20px;
}

/*Resize the wrap to see the search bar change!*/
.wrap{
  width: 30%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.table, .table tr td, .table tr th
{
border:1px solid #333;
}
</style>
@stop

@section('styles')
<style>
  .textbox {
    border: 1px;
    background-color: #33AD0A;
    outline:0;
    height:25px;
    width: 275px;
  }

  .autocomplete-suggestions{
    color:#fff;
    font-size: 13px;
  }
</style>
@endsection
