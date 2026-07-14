@extends('layouts.layout')
@section('pageTitle')
@endsection
@section('content')
<!-- /.box-header -->
<div class="box-body">
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
        <b><p class="profile-username text-center text-success">ISALU HOSPITAL LIMITED
        </p></b>
      <b><p class="profile-username text-center text-success">PAYROLL</p></b>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
          <b>File NO</b> <b class="pull-right">{{$result->fileNo}}</b>
        </li>
        <li class="list-group-item">
          <b>Name</b> <b class="pull-right">{{$result->surname}} {{$result->first_name}} {{$result->othernames}}</b>
        </li>
        <li class="list-group-item">
          <b>Bank</b> <b class="pull-right">{{$result->bank}}</b>
        </li>
        <li class="list-group-item">
          <b>Bank Group</b> <b class="pull-right">{{$result->bankGroup}}</b>
        </li>
        <li class="list-group-item">
          <b>Employee Type</b> <b class="pull-right">{{$result->employee_type}}</b>
        </li>
        <li class="list-group-item">
          <b>Arrears Type</b> <b class="pull-right">New Appointment</b>
        </li>
        <li class="list-group-item">
          <b>Arrears Duration</b> <b class="pull-right">{{$month_diff." months ".$day_diff. " days of ".$daysOfMonth}} </b>
        </li>
      </ul>
    </div> <!-- /.col -->
  </div> <!-- end row -->
  <div class="row">
   <div class="col-md-10 col-md-offset-1">
   <b><h4 class="profile-username text-center text-success">{{$mth}} {{$year}} </h4></b>
          <?php $res= "variation advice no..................on GL ".$result->oldGrade." Step ".$result->OldStep;
            $resp= "Salary ".$result->type." w.e.f ".date("d-m-Y", strtotime($result->appointment_date))." vide "; 
          ?> 
      {{$resp}}<p>
        {{$res}}
    </div>
  </div>

  <div class="row" style="margin: 0px 20px;">
    
    <!--<div class="col-md-10 col-md-offset-1"></div>-->
    <table class="table table-bordered">
      <tr>
        <td><h3 class="profile-username text-center"><b>Basic Salary</b></h3>
     <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldBasic , 2, '.', ',')}}</a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldBasic/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldBasic * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>
        <td><h3 class="profile-username text-center"><b>Peculiar</b></h3>
     <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldPeculiar , 2, '.', ',')}}</a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldPeculiar/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldPeculiar * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>
      </tr>

      <tr>
        <td><h3 class="profile-username text-center" ><b>Pension</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldPension , 2, '.', ',')}}</a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldPension/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldPension * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>
        <td><h3 class="profile-username text-center" ><b>Tax</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldTax , 2, '.', ',')}}</a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldTax/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldTax * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>
      </tr>

      <tr>
      <td>
        <h3 class="profile-username text-center" ><b>NHF</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldNhf , 2, '.', ',')}}</a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldNhf/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldNhf * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul>
      </td>
      <td>
        <h3 class="profile-username text-center" ><b>union Dues</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldUnionDues , 2, '.', ',')}}
                 </a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldUnionDues/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldUnionDues * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul>
      </td>
      </tr>

       <tr>
      <td>
        <h3 class="profile-username text-center" ><b>Leave Grant</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
                 <b>Amount</b> <a class="pull-right text-muted">{{number_format($result->oldLeave_bonus , 2, '.', ',')}}</a>
        </li>
        <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{ $daytotal = (($result->oldLeave_bonus/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php $mthtotal=  ($result->oldLeave_bonus * $month_diff); ?> {{number_format($mthtotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>Total basic arrears</b><b> <a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul>
      </td>
      <td>
     
      </td>
      </tr>

    </table>
  
    
  </div>
</div> <!-- //end box-body -->
@endsection
@section('styles')
@endsection