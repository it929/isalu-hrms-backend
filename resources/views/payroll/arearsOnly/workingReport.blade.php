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
      <table class="table table-condensed">
        <tr>
          <td><strong>FILE NO <span class="pull-right">{{$query->fileNo}}</span></strong></td>
        </tr>
         <tr>
          <td><strong>Name <span class="pull-right">{{$query->name}} </span></strong></td>
        </tr>
        <tr>
          <td><strong>Bank <span class="pull-right">{{$query->bank}}</span></strong></td>
        </tr>
       
        <tr>
          <td><strong>Employee Type <span class="pull-right">{{$query->employee_type}}</span></strong></td>
        </tr>
        <tr>
          <td><strong>Arrears Type <span class="pull-right">{{$query->type}}</span></strong></td>
        </tr>
        <tr>
          <td><strong>Arrears Duration <span class="pull-right">{{ $month_diff." months ".$day_diff. " days out of ".$daysOfMonth." days" }}</span></strong></td>
        </tr>
      </table>
    </div>
    <!-- /.col -->
  </div>
  <div class="row">
   <div class="col-md-10 col-md-offset-1">
        <b><p class="profile-username text-center text-success">{{$query->month}} {{$query->year}} </h4></b></p>
           <?php $res= "variation advice no..................on GL ".$query->oldGrade." Step ".$query->OldStep." To GL ".$query->newGrade." Step ".$query->newStep;
            $resp= "Promotion Arrears  w.e.f ".date("d-m-Y", strtotime($query->dueDate))." vide "; 
          ?> 
        {{$resp}}<br>
        {{$res}}
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      
    <table  cellspacing="9" cellpadding="15" class="table table-bordered">
      <tr>
        <td><h3 class="profile-username text-center"><b>Basic Salary</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
          <b>New</b> <a class="pull-right text-muted">{{number_format($query->newBasic, 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Old</b> <a class="pull-right text-muted">-{{number_format($query->oldBasic, 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Amount</b> <a class="pull-right text-muted"><?php $amt = ($query->newBasic - $query->oldBasic);?> {{number_format($amt , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{  $daytotal = (($amt/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php  $mthtotal=  ($amt * $month_diff);?> {{number_format($mthtotal , 2, '.', ',')}} </a>
        </li>
          <li class="list-group-item">
          <b>&nbsp;</b> <b><a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>
        <td><h3 class="profile-username text-center"><b>Peculiar</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
          <b>New</b> <a class="pull-right text-muted">{{number_format($query->newPeculiar , 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Old</b> <a class="pull-right text-muted">-{{number_format($query->oldPeculiar , 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Amount</b> <a class="pull-right text-muted"><?php $amt = ($query->newPeculiar - $query->oldPeculiar);?> {{number_format($amt , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{  $daytotal = (($amt/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php  $mthtotal=  ($amt * $month_diff);?> {{number_format($mthtotal , 2, '.', ',')}} </a>
        </li>
          <li class="list-group-item">
          <b>&nbsp;</b> <b><a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>
      </tr>

      <tr>
        <td><h3 class="profile-username text-center"><b>Pension</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
          <b>New</b> <a class="pull-right text-muted">{{number_format($query->newPension , 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Old</b> <a class="pull-right text-muted">-{{number_format($query->oldPension , 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Amount</b> <a class="pull-right text-muted"><?php $amt = ($query->newPension - $query->oldPension);?> {{number_format($amt , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{  $daytotal = (($amt/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php  $mthtotal=  ($amt * $month_diff);?> {{number_format($mthtotal , 2, '.', ',')}} </a>
        </li>
          <li class="list-group-item">
          <b>&nbsp;</b> <b><a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
          </li>
      </ul></td>
        <td><h3 class="profile-username text-center"><b>Tax</b></h3>
      <ul class="list-group list-group-unbordered">
        <li class="list-group-item">
          <b>New</b> <a class="pull-right text-muted">{{number_format($query->newTax, 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Old</b> <a class="pull-right text-muted">-{{number_format($query->oldTax, 2, '.', ',')}} </a>
        </li>
        <li class="list-group-item">
          <b>Amount</b> <a class="pull-right text-muted"><?php $amt = ($query->newTax - $query->oldTax);?> {{number_format($amt , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>For {{$day_diff}} days</b> <a class="pull-right text-muted"><?php if ($day_diff==0) {
          $daytotal=0;
          }else{  $daytotal = (($amt/$daysOfMonth) * $day_diff);}?> {{number_format($daytotal , 2, '.', ',')}}</a>
        </li>
          <li class="list-group-item">
          <b>X {{$month_diff}} months</b> <a class="pull-right text-muted"><?php  $mthtotal=  ($amt * $month_diff);?> {{number_format($mthtotal , 2, '.', ',')}} </a>
        </li>
          <li class="list-group-item">
          <b>&nbsp;</b> <b><a class="pull-right text-muted"><?php echo number_format(($daytotal + $mthtotal), 2, '.', ','); ?></a></b>
        </li>
      </ul></td>      
      </tr> 

      
    
    </table>
  
    </div>
  </div> <!-- end row -->
</div>
  <!-- /.row -->
<!--row two-->
@endsection
@section('styles')
@endsection