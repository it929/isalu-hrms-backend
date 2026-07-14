@extends('layouts.layout')
@section('pageTitle')
@endsection

@section('content')
<div class="box box-default" style="border-top: none;">
  <div style="margin: 10px 20px;"> @if(session('err'))
    <div class="alert alert-warning alert-dismissible hidden-print" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span> </button>
      <strong>Error!</strong> {{ session('err') }} </div>
    @endif </div>
  <div class="box-body">
    <div class="row">
      <div class="col-xs-2"><img src="{{asset('Images/logo.jpg')}}" class="responsive"></div>
      <div class="col-xs-8">
        <div>
          <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
          
        </div>
      </div>
      <div class="col-xs-2"><img src="{{asset('Images/coat.jpg')}}" class="responsive"></div>
    </div>
    <div class="row">
      <div class="col-md-4">
      <h5 class="text-success"><b style="color: #222;"></h5>
      <h5 class="text-success"><b style="color: #222;"></h5>
      <h5 class="text-success"><b style="color: #222;"></h5>

      </div>
      <div class="col-md-4">
      
      </div>
    </div>
    <div class="row" style="margin-top:5px; padding-top: 15px; ">
      <div class="col-md-12" style="padding-top: 0px; margin-top: 5px; ">
        <table class="table table-striped table-condensed table-bordered">
          <thead>
          <tr>
          <th>SN</th>
            <th>BANK</th>
            <th>BENEFICIARY</th>
            <th>AMOUNT</th>
            </tr>
              </thead>
          <tbody>
          
          @php $key = 1; @endphp
          <?php $cum = 0.00;  $staffbank = ''; 
          $cumlast =0;
          $cumlasts =0;
          ?>
          @foreach ($group as $list )
          <?php
          $c = $list->NetPay + $list->PEC;
          $cum += $list->NetPay + $list->PEC;
          
          
          //$cumlast = $cum - $list->nhf;
          
          //$prev_year = $list->year-1;
         
          
                   
          if ($list->bk != $staffbank) {
          
          echo '
         <tr>
          <td colspan="4" style=" padding:8px; border:none; background-color:#f2f2f2; color:#333; display="block"><b>'.$c++.'</b></td>
          </tr>
          ';
                      
        

         
          }
           $staffbank = $list->bk;
          ?>
          <tr>
            <td>{{$key++}}</td>
            <td> {{$list->staffbank}} </td>
            <td>{{$list->name}}</td>
            <td>{{ number_format($list->NetPay,2) }}</td>
            
          </tr>
          @endforeach
          
            </tbody>
          
        </table>
        <div class="hidden-print"></div>
      </div>
    </div>
    <!-- /.col --> 
  </div>
  <!-- /.row --> 
  
</div>
@endsection
@section('scripts') 
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script> 
<!-- autocomplete js--> 
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script> 
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script> 
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
@endsection

@section('stypes')
<style type="text/css">
    @import url(https://fonts.googleapis.com/css?family=Open+Sans);

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
</style>
@stop
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">
@endsection
@section('styles')
<style> 
  .textbox { 
    border: 1px;
    background-color: #33AD0A; 
    outline:0; 
    height:25px; 
    width: 275px; 
  } 
  $('.autocomplete-suggestions').css({
    color: '#0f3'
  });

  .autocomplete-suggestions{
    color:#fff;
    font-size: 13px;
  }
</style>
@endsection