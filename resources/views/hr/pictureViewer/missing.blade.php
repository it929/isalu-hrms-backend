@extends('layouts.layout')

@section('pageTitle')
 FEDERAL GOVERNMENT OF NIGERIA
	PAYMENT VOUCHER
@endsection

@section('content')
<div align="left" style="padding:0 2%;">
    <form method="post" action="{{ url('/approve') }}">
        <div class="row">
            <div class="col-md-12">
                <div align="center"><h2><strong>ISALU HOSPITAL LIMITED PAYROLL</strong></h2></div>
            </div>
        </div>
        <hr />
      <div align="left">
            <div>PAYROLL P.V. No: </div>
      </div>
      <br />
      <div align="left"> 
            <div>MINISTRY/DEPARTMENT: ISALU HOSPITAL LIMITED, {{$curDivision}} </div>
      </div>
      <br />
      <div align="left">
            <div>BANK: {{$bankName->bank}} DIVISION</div>
      </div>
      <br />
      <div align="center">
            <div><strong>All Staff With Missing Picture</strong></div>
      </div>
      <br />
      <div class="row">
        {{ csrf_field() }}
        <div class="col-md-12">
          
          <table class="table table-responsive table-bordered table-striped">
              <thead>
                    <tr>
                        <th><div align="center">FILE NO</div></th>
                        <th><div align="center">STAFF FULL NAME</div></th>
                        <th><div align="center">GRADE</div></th>
                        <th><div align="center">STEP</div></th>
                        <th><div align="center">PICTURE</div></th>
                    </tr>
              <thead>
              <tbody>
               @foreach($displayPicture as $display)
                    <tr>
                        <td class="text-center">{{$display -> fileNo}}</td>
                        <td class="text-left">{{$display -> surname .' '. $display -> first_name .' '. $display -> othernames}}</td>
                        <td class="text-center">{{$display -> grade}}</td>
                        <td class="text-center">{{$display -> step}}</td>
                        <td class="text-center"><img src="{{asset('passport/0.png')}}" height="40"  /> </td>
                    </tr>
             @endforeach
                    <tr>
                        <td colspan="6">
                        <div align="left">
                            {{'TOTAL STAFF: ' . $total}}
                        </div>
                        </td>
                    </tr>
              </tbody>
        </table>
    </div>
    </div>
    <br/>
    <br/>
    </form>
  </div><!--end main div=center-->
@endsection










