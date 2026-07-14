<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>ISALU HOSPITAL LIMITED</title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

  <!-- AdminLTE Skins. Choose a skin from the css/skins
  folder instead of downloading all of them to reduce the load. -->

  @yield('styles')

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script> var murl = "{{ url('/')}}"; </script>
</head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED...::...PeCard Report</title>
<style type="text/css">

	@media print
	{    
		.no-print, .no-print *
		{
			display: none !important;
		}
	}

</style>
<body>
	<div class="row">
		<div class="col-md-12">

			<div align="center"><h3 class="text-success text-center">FEDERAL GOVERNMENT OF NIGERIA
			</h3> <h2>PERSONAL EMOLUMENT RECORD</h2>
		</div>

				{{ session('division') }}
			@endif  DIVISION</h3>

			<?php  
			$fileno = $details->fileNo; 
			?>
			

			<img class="profile-user-img img-responsive  pull-right" src="{{URL('/passport/'.$fileno.'.jpg') }}" alt="{{$fileno}}">

			<table class="table" border="1" >  

				<tr>
					<td>
						<table class="table table-hover" border="1" cellpadding="0" cellspacing="0" >
							<tr>
								<td width="44%">Min/Dept<br />Control No</td>
								<td width="56%">JR:1</td>
							</tr>
							<tr>
								<td>File No</td>
								<td>  {{$fileno}}
								</td>
							</tr>
							<tr>
								<td>Grade</td>
								<td>{{ $getLevel->grade }}</td>
							</tr>
							<tr>
								<td>Step</td>
								<td>{{ $getLevel->step }}</td>
							</tr>
							<tr>
								<td>Bank</td>
								<td>{{ $details->bank }} </td>
							</tr>
							<tr>
								<td>Account No</td>
								<td>{{ $details->AccNo }} </td>
							</tr>
							<tr>
								<td>Name</td>
								<td>{{ $details->surname }} </td>
							</tr>
							<tr>
								<td>Rank</td>
								<td>
									{{ $details->rank }}

								</td>
							</tr>
							<tr>
								<td>Commenced</td>
								<td> {{ date("d-m-Y", strtotime($details->appointment_date)) }} </td>
							</tr>
							<tr>
								<td>DOB</td>
								<td>{{ date("d-m-Y", strtotime($details->dob)) }} </td>
							</tr>
							<tr>
								<td>Children</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>Residential Address</td>
								<td valign="top"> {{ $details->home_address }} </td>
							</tr>
							<tr>
								<td>Leave Address</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>Employee Type</td>
								<td> {{ $details->employee_type }}  Staff</td>
							</tr>
							<tr>
								<td>Arrears this year</td>
								<td>
									@foreach ($arr as $arrears)
									<a href = "{{url('pecard/working/'. $year.'/'.$details->fileNo.'/'.$arrears )}}">{{ $arrears }}</a>
									<?php  echo " "; ?>
									@endforeach
								</td>
							</tr>
							<tr>
								<td>New appointment</td>
								<td>
									@foreach($app as $arrear)
									<a href = "{{url('pecard/newapp-working/'. $year.'/'.$details->fileNo.'/'.$arrear )}}">{{ $arrear }}</a>
									<?php  echo " "; ?>
									@endforeach
								</td>
							</tr>
							
						</table>
					</td>
					<td >
						<table width="100%"  border="1" cellpadding="0" cellspacing="0">
							<tr>
								<td width="300"><strong>MONTH</strong></td>
								<td width="100" align="center"><strong>JAN/{{$year}}</strong></td>
								<td width="100" align="center"><strong>FEB/{{$year}}</strong></td>
								<td width="100" align="center"><strong>MAR/{{$year}}</strong></td>
								<td width="100" align="center"><strong>APR/{{$year}}</strong></td>
								<td width="100" align="center"><strong>MAY/{{$year}}</strong></td>
								<td width="100" align="center"><strong>JUN/{{$year}}</strong></td>
								<td width="100" align="center"><strong>JUL/{{$year}}</strong></td>
								<td width="100" align="center"><strong>AUG/{{$year}}</strong></td>
								<td width="100" align="center"><strong>SEP/{{$year}}</strong></td>
								<td width="100" align="center"><strong>OCT/{{$year}}</strong></td>
								<td width="100" align="center"><strong>NOV/{{$year}}</strong></td>
								<td width="100" align="center"><strong>DEC/{{$year}}</strong></td>
							</tr>
							<tr>
								<td>Salary</td>
								<td align="right">{{number_format($result['JANUARY']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['FEBRUARY']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['MARCH']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['APRIL']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['MAY']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JUNE']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JULY']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['AUGUST']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['SEPTEMBER']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['OCTOBER']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['NOVEMBER']['basic_salary'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['DECEMBER']['basic_salary'], 2, '.', ',')}}
								</td>
							</tr>
							<tr>
								<td>Acting Allowance</td>
								<td align="right">
									<?php $sum=0;
									$sum=$result['JANUARY']['actingAllow']+$result['JANUARY']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['FEBRUARY']['actingAllow']+$result['FEBRUARY']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right">
									<?php $sum=0;
									$sum=$result['MARCH']['actingAllow']+ $result['MARCH']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right">
									<?php $sum=0;
									$sum=$result['APRIL']['actingAllow']+ $result['APRIL']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['MAY']['actingAllow']+ $result['MAY']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['JUNE']['actingAllow']+ $result['JUNE']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right">
									<?php $sum=0;
									$sum=$result['JULY']['actingAllow']+ $result['JULY']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['AUGUST']['actingAllow']+ $result['AUGUST']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right">
									<?php $sum=0;
									$sum=$result['SEPTEMBER']['actingAllow']+ $result['SEPTEMBER']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['OCTOBER']['actingAllow']+ $result['OCTOBER']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['NOVEMBER']['actingAllow']+ $result['NOVEMBER']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right"> 
									<?php $sum=0;
									$sum=$result['DECEMBER']['actingAllow']+ $result['DECEMBER']['arrearsBasic'];?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
							</tr>
							<tr>
								<td>Gross Emolument for month</td>
								<td align="right">
									{{number_format($result['JANUARY']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['FEBRUARY']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['MARCH']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['APRIL']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['MAY']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JUNE']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JULY']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['AUGUST']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['SEPTEMBER']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['OCTOBER']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['NOVEMBER']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['DECEMBER']['cumEmolu'], 2, '.', ',')}}
								</td>
							</tr>
							<tr>
								<td>Cumulative Emolument</td>
								<td align="right">
									{{number_format($result['JANUARY']['cumEmolu'], 2, '.', ',')}}
								</td>
								<td align="right">
									@if($result['FEBRUARY']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}
									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['MARCH']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}
									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['APRIL']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}
									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right"> 
									@if($result['MAY']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}
									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['JUNE']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}
									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}    
									@endif             
								</td>
								<td align="right">
									@if($result['JULY']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}
									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu']+$result['JULY']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['AUGUST']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu']+$result['JULY']['cumEmolu']+$result['AUGUST']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['SEPTEMBER']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu']+$result['JULY']['cumEmolu']+$result['AUGUST']['cumEmolu']+$result['SEPTEMBER']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['OCTOBER']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu']+$result['JULY']['cumEmolu']+$result['AUGUST']['cumEmolu']+$result['SEPTEMBER']['cumEmolu']+$result['OCTOBER']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif   
								</td>
								<td align="right">
									@if($result['NOVEMBER']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu']+$result['JULY']['cumEmolu']+$result['AUGUST']['cumEmolu']+$result['SEPTEMBER']['cumEmolu']+$result['OCTOBER']['cumEmolu']+$result['NOVEMBER']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['DECEMBER']['cumEmolu']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['cumEmolu']+$result['FEBRUARY']['cumEmolu']+$result['MARCH']['cumEmolu']+$result['APRIL']['cumEmolu']+$result['MAY']['cumEmolu']+$result['JUNE']['cumEmolu']+$result['JULY']['cumEmolu']+$result['AUGUST']['cumEmolu']+$result['SEPTEMBER']['cumEmolu']+$result['OCTOBER']['cumEmolu']+$result['NOVEMBER']['cumEmolu']+$result['DECEMBER']['cumEmolu'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
							</tr>
							<tr>
								<td>Tax due to date</td>
								<td align="right">
									<?php 
									$sum=$result['JANUARY']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
								</td>
								<td align="right">
									@if($result['FEBRUARY']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['MARCH']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['APRIL']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['MAY']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['MAY']['tax'];
									?>

									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['JUNE']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['JULY']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax']+$result['JULY']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}      

									@endif
								</td>

								<td align="right">
									@if($result['AUGUST']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax']+$result['JULY']['tax']+$result['AUGUST']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right"> 
									@if($result['SEPTEMBER']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax']+$result['JULY']['tax']+$result['AUGUST']['tax']+$result['SEPTEMBER']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right"> 
									@if($result['SEPTEMBER']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax']+$result['JULY']['tax']+$result['AUGUST']['tax']+$result['SEPTEMBER']['tax']+$result['OCTOBER']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>

								<td align="right">  

									@if($result['NOVEMBER']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax']+$result['JULY']['tax']+$result['AUGUST']['tax']+$result['SEPTEMBER']['tax']+$result['OCTOBER']['tax']+$result['NOVEMBER']['tax'];
									?>
									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
								<td align="right">
									@if($result['DECEMBER']['tax']==0)    
									{{number_format(0, 2, '.', ',')}}

									@else
									<?php 
									$sum=$result['JANUARY']['tax']+$result['FEBRUARY']['tax']+$result['MARCH']['tax']+$result['APRIL']['tax']+$result['MAY']['tax']+$result['JUNE']['tax']+$result['JULY']['tax']+$result['AUGUST']['tax']+$result['SEPTEMBER']['tax']+$result['OCTOBER']['tax']+$result['NOVEMBER']['tax']+$result['DECEMBER']['tax'];
									?>

									{{number_format($sum, 2, '.', ',')}}
									@endif
								</td>
							</tr>

							<tr>
								<td>Tax this month</td>
								<td align="right">
									{{number_format($result['JANUARY']['tax'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['FEBRUARY']['tax'], 2, '.', ',')}}        
								</td>
								<td align="right">
									{{number_format($result['MARCH']['tax'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['APRIL']['tax'], 2, '.', ',')}}    
								</td>
								<td align="right">
									{{number_format($result['MAY']['tax'], 2, '.', ',')}}

								</td>
								<td align="right">
									{{number_format($result['JUNE']['tax'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JULY']['tax'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['AUGUST']['tax'], 2, '.', ',')}}

								</td>
								<td align="right"> 
									{{number_format($result['SEPTEMBER']['tax'], 2, '.', ',')}}  
								</td>
								<td align="right">
									{{number_format($result['OCTOBER']['tax'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['NOVEMBER']['tax'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['DECEMBER']['tax'], 2, '.', ',')}}
								</td>
							</tr>
							<tr>
								<td>NHF</td>
								<td align="right">
									{{number_format($result['JANUARY']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['FEBRUARY']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['MARCH']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['APRIL']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['MAY']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JUNE']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['JULY']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['AUGUST']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['SEPTEMBER']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['OCTOBER']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['NOVEMBER']['nhf'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['DECEMBER']['nhf'], 2, '.', ',')}}
								</td>
							</tr>
							<tr>
								<td>Pension</td>
								<td align="right"> 
									{{number_format($result['JANUARY']['pension'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['FEBRUARY']['pension'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['MARCH']['pension'], 2, '.', ',')}}

								</td>
								<td align="right">
									{{number_format($result['APRIL']['pension'], 2, '.', ',')}}

								</td>
								<td align="right"> 
									{{number_format($result['MAY']['pension'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['JUNE']['pension'], 2, '.', ',')}}
								</td>
								<td align="right"> 
									{{number_format($result['JULY']['pension'], 2, '.', ',')}}
								</td>
								<td align="right">      {{number_format($result['AUGUST']['pension'], 2, '.', ',')}}

								</td>
								<td align="right">
									{{number_format($result['SEPTEMBER']['pension'], 2, '.', ',')}}

								</td>
								<td align="right"> 
									{{number_format($result['OCTOBER']['pension'], 2, '.', ',')}}

								</td>
								<td align="right">         
									{{number_format($result['NOVEMBER']['pension'], 2, '.', ',')}}
								</td>
								<td align="right">
									{{number_format($result['DECEMBER']['pension'], 2, '.', ',')}}
								</td>

							</tr>
							<tr>
								<td>Union Dues</td>
								<td align="right">  {{number_format($result['JANUARY']['unionDues'], 2, '.', ',')}}

								</td>
								<td align="right"> 
									{{number_format($result['FEBRUARY']['unionDues'], 2, '.', ',')}}

								</td>
								<td align="right">  {{number_format($result['MARCH']['unionDues'], 2, '.', ',')}}

								</td>
								<td align="right">   {{number_format($result['APRIL']['unionDues'], 2, '.', ',')}}

								</td>
								<td align="right">  {{number_format($result['MAY']['unionDues'], 2, '.', ',')}}

								</td><td align="right">  {{number_format($result['JUNE']['unionDues'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['JULY']['unionDues'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['AUGUST']['unionDues'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['SEPTEMBER']['unionDues'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['OCTOBER']['unionDues'], 2, '.', ',')}}
							</td>
							<td align="right">
								{{number_format($result['NOVEMBER']['unionDues'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['DECEMBER']['unionDues'], 2, '.', ',')}}

							</td>
						</tr>
						<tr>
							<td>Use of Govt. Vehicle</td>

							<td align="right">     {{number_format($result['JANUARY']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">    {{number_format($result['FEBRUARY']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['MARCH']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['APRIL']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['MAY']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['JUNE']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['JULY']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['AUGUST']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">{{number_format($result['SEPTEMBER']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">    {{number_format($result['OCTOBER']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">   {{number_format($result['NOVEMBER']['ugv'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['DECEMBER']['ugv'], 2, '.', ',')}}
							</td>
						</tr>
						<tr>
							<td>Nicn Coop</td>

							<td align="right">    {{number_format($result['JANUARY']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">    {{number_format($result['FEBRUARY']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['MARCH']['nicncoop'], 2, '.', ',')}}
							</td>
							<td align="right">     {{number_format($result['APRIL']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">      {{number_format($result['MAY']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">   {{number_format($result['JUNE']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['JULY']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">   {{number_format($result['AUGUST']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['SEPTEMBER']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['OCTOBER']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['NOVEMBER']['nicncoop'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['DECEMBER']['nicncoop'], 2, '.', ',')}}

							</td>
						</tr>
						<tr>
							<td>CTLS Labour</td>
							<td align="right"> {{number_format($result['JANUARY']['ctlsLab'], 2, '.', ',')}}
							</td>
							<td align="right">  {{number_format($result['FEBRUARY']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right">     {{number_format($result['MARCH']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right">      {{number_format($result['APRIL']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['MAY']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['JUNE']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['JULY']['ctlsLab'], 2, '.', ',')}}
							</td>
							<td align="right">  {{number_format($result['AUGUST']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['SEPTEMBER']['ctlsLab'], 2, '.', ',')}}
							</td>
							<td align="right">
								{{number_format($result['OCTOBER']['ctlsLab'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['NOVEMBER']['ctlsLab'], 2, '.', ',')}}
							</td>
							<td align="right">   {{number_format($result['DECEMBER']['ctlsLab'], 2, '.', ',')}}

							</td>
						</tr>
						<tr>
							<td>CTLS Fed. Sec.</td>
							<td align="right"> 
								{{number_format($result['JANUARY']['ctlsFed'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['FEBRUARY']['ctlsFed'], 2, '.', ',')}}              
							</td>
							<td align="right">   {{number_format($result['MARCH']['ctlsFed'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['APRIL']['ctlsFed'], 2, '.', ',')}}
							</td>
							<td align="right"> {{number_format($result['MAY']['ctlsFed'], 2, '.', ',')}}
							</td>
							<td align="right"> {{number_format($result['JUNE']['ctlsFed'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['JULY']['ctlsFed'], 2, '.', ',')}}
							</td>
							<td align="right"> {{number_format($result['AUGUST']['ctlsFed'], 2, '.', ',')}}
							</td>
							<td align="right"> 
								{{number_format($result['SEPTEMBER']['ctlsFed'], 2, '.', ',')}}
							</td>
							<td align="right">{{number_format($result['OCTOBER']['ctlsFed'], 2, '.', ',')}}
							</td>
							<td align="right">
								{{number_format($result['NOVEMBER']['ctlsFed'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['DECEMBER']['ctlsFed'], 2, '.', ',')}}

							</td>
						</tr>
						<tr>
							<td>Surcharge</td>
							<td align="right"> 
								{{number_format($result['JANUARY']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['FEBRUARY']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['MARCH']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['APRIL']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['MAY']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['JUNE']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['JULY']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['AUGUST']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right"> 
								{{number_format($result['SEPTEMBER']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right">
								{{number_format($result['OCTOBER']['surcharge'], 2, '.', ',')}}

							</td>
							<td align="right">  {{number_format($result['NOVEMBER']['surcharge'], 2, '.', ',')}}
							</td>
							<td align="right">    {{number_format($result['DECEMBER']['surcharge'], 2, '.', ',')}}
							</td>
						</tr>
						<tr>
							<td>Phone Charges</td>
							<td align="right">  {{number_format($result['JANUARY']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['FEBRUARY']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['MARCH']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['APRIL']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['MAY']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['JUNE']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['JULY']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['AUGUST']['phoneCharges'], 2, '.', ',')}}
							</td>
							<td align="right"> 
								{{number_format($result['SEPTEMBER']['phoneCharges'], 2, '.', ',')}}
							</td>
							<td align="right"> {{number_format($result['OCTOBER']['phoneCharges'], 2, '.', ',')}}
							</td>
							<td align="right"> {{number_format($result['NOVEMBER']['phoneCharges'], 2, '.', ',')}}

							</td>
							<td align="right"> {{number_format($result['DECEMBER']['phoneCharges'], 2, '.', ',')}}

							</td>
						</tr>
						<tr>
							<td>Personal Assistant Deduction</td>

							<td align="right"> {{number_format($result['JANUARY']['pa_deduct'], 2, '.', ',')}}

							</td>

							<td align="right">   {{number_format($result['FEBRUARY']['pa_deduct'], 2, '.', ',')}}
							</td>

							<td align="right">  {{number_format($result['MARCH']['pa_deduct'], 2, '.', ',')}}

							</td>

							<td align="right">  {{number_format($result['APRIL']['pa_deduct'], 2, '.', ',')}}

							</td>

							<td align="right">   {{number_format($result['MAY']['pa_deduct'], 2, '.', ',')}}

							</td>

							<td align="right">    {{number_format($result['JUNE']['pa_deduct'], 2, '.', ',')}}

							</td>

							<td align="right"> {{number_format($result['JULY']['pa_deduct'], 2, '.', ',')}}

							</td>

							<td align="right"> 
								{{number_format($result['AUGUST']['pa_deduct'], 2, '.', ',')}}


								<td align="right">   {{number_format($result['SEPTEMBER']['pa_deduct'], 2, '.', ',')}}

								</td>

								<td align="right">     {{number_format($result['OCTOBER']['pa_deduct'], 2, '.', ',')}}

								</td>

								<td align="right">   {{number_format($result['NOVEMBER']['pa_deduct'], 2, '.', ',')}}

								</td>

								<td align="right">   {{number_format($result['DECEMBER']['pa_deduct'], 2, '.', ',')}}

								</td>

							</tr>
							<tr>
								<td>Federal Housing Loan</td>

								<td align="right">   {{number_format($result['JANUARY']['fedhousing'], 2, '.', ',')}}
								</td>
								<td align="right">    {{number_format($result['FEBRUARY']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right"> {{number_format($result['MARCH']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right">  {{number_format($result['APRIL']['fedhousing'], 2, '.', ',')}}
								</td>
								<td align="right">    {{number_format($result['MAY']['fedhousing'], 2, '.', ',')}}
								</td>
								<td align="right">   {{number_format($result['JUNE']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right">{{number_format($result['JULY']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right">{{number_format($result['AUGUST']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right">  {{number_format($result['SEPTEMBER']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right">       {{number_format($result['OCTOBER']['fedhousing'], 2, '.', ',')}}
								</td>

								<td align="right">  {{number_format($result['NOVEMBER']['fedhousing'], 2, '.', ',')}}

								</td>
								<td align="right">   {{number_format($result['DECEMBER']['fedhousing'], 2, '.', ',')}}

								</td>
							</tr>
							<tr>
								<td>Total Deductions</td>
								<td align="right">   {{number_format($result['JANUARY']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">  {{number_format($result['FEBRUARY']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">  {{number_format($result['MARCH']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">   {{number_format($result['APRIL']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right"> {{number_format($result['MAY']['totalDeduct'], 2, '.', ',')}}

								</td>
								<td align="right">    {{number_format($result['JUNE']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right"> {{number_format($result['JULY']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">   {{number_format($result['AUGUST']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">   {{number_format($result['SEPTEMBER']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">  {{number_format($result['OCTOBER']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right"> {{number_format($result['NOVEMBER']['totalDeduct'], 2, '.', ',')}}
								</td>
								<td align="right">     {{number_format($result['DECEMBER']['totalDeduct'], 2, '.', ',')}}


								</tr>
								<tr>
									<td>Net Pay</td>
									<td align="right">  {{number_format($result['JANUARY']['netpay'], 2, '.', ',')}}

									</td>

									<td align="right">      {{number_format($result['FEBRUARY']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['MARCH']['netpay'], 2, '.', ',')}}
									</td>
									<td align="right">   {{number_format($result['APRIL']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">    {{number_format($result['MAY']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['JUNE']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['JULY']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['AUGUST']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['SEPTEMBER']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['OCTOBER']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">{{number_format($result['NOVEMBER']['netpay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['DECEMBER']['netpay'], 2, '.', ',')}}
									</td>
								</tr>
								<tr>
									<td>Basic Allowance</td>

									@if($details->employee_type == 'HEALTH')

									<td align="right">  {{number_format($result['JANUARY']['motorBasicAll'] + $result['JANUARY']['callDuty'] + $result['JANUARY']['hazard'], 2, '.', ',')}}
									</td>
									<td align="right">
										{{number_format($result['FEBRUARY']['motorBasicAll'] + $result['JANUARY']['callDuty'] + $result['JANUARY']['hazard'], 2, '.', ',')}}
									</td>
									<td align="right">
										{{number_format($result['MARCH']['motorBasicAll'] + $result['JANUARY']['callDuty'] + $result['JANUARY']['hazard'], 2, '.', ',')}}
									</td>
									<td align="right">  {{number_format($result['APRIL']['motorBasicAll'] + $result['APRIL']['callDuty'] + $result['APRIL']['hazard'], 2, '.', ',')}}
									</td>
									<td align="right"> 
										{{number_format($result['MAY']['motorBasicAll'] + $result['MAY']['callDuty'] + $result['JANUARY']['hazard'], 2, '.', ',')}}          
									</td>
									<td align="right">   {{number_format($result['JUNE']['motorBasicAll'] + $result['JUNE']['callDuty'] + $result['JUNE']['hazard'], 2, '.', ',')}}

									</td>
									<td align="right"> 
										{{number_format($result['JULY']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right"> {{number_format($result['AUGUST']['motorBasicAll'] + $result['AUGUST']['callDuty'] + $result['AUGUST']['hazard'], 2, '.', ',')}}
									</td>
									<td align="right">{{number_format($result['SEPTEMBER']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right">{{number_format($result['OCTOBER']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right">{{number_format($result['NOVEMBER']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right">     {{number_format($result['DECEMBER']['motorBasicAll'], 2, '.', ',')}}
									</td>
									@else
									<td align="right">  {{number_format($result['JANUARY']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right">
										{{number_format($result['FEBRUARY']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right">
										{{number_format($result['MARCH']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right">  {{number_format($result['APRIL']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right"> 
										{{number_format($result['MAY']['motorBasicAll'], 2, '.', ',')}}          
									</td>
									<td align="right">   {{number_format($result['JUNE']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right"> 
										{{number_format($result['JULY']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right">         {{number_format($result['AUGUST']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right">    {{number_format($result['SEPTEMBER']['motorBasicAll'], 2, '.', ',')}}
									</td>
									<td align="right">       {{number_format($result['OCTOBER']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right">       {{number_format($result['NOVEMBER']['motorBasicAll'], 2, '.', ',')}}

									</td>
									<td align="right">     {{number_format($result['DECEMBER']['motorBasicAll'], 2, '.', ',')}}
									</td>

									@endif
								</tr>
								<tr>
									<td>Gross Pay</td>
									<td align="right"> 
										{{number_format($result['JANUARY']['grosspay'], 2, '.', ',')}}
									</td>
									<td align="right"> {{number_format($result['FEBRUARY']['grosspay'], 2, '.', ',')}}
									</td>
									<td align="right">  {{number_format($result['MARCH']['grosspay'], 2, '.', ',')}}
									</td>

									<td align="right">  {{number_format($result['APRIL']['grosspay'], 2, '.', ',')}}
									</td>

									<td align="right">    {{number_format($result['MAY']['grosspay'], 2, '.', ',')}}

									</td>
									<td align="right"> {{number_format($result['JUNE']['grosspay'], 2, '.', ',')}}
									</td>
									<td align="right">       {{number_format($result['JULY']['grosspay'], 2, '.', ',')}}

									</td>
									<td align="right">      {{number_format($result['AUGUST']['grosspay'], 2, '.', ',')}}
									</td>
									<td align="right"> {{number_format($result['SEPTEMBER']['grosspay'], 2, '.', ',')}}

									</td>
									<td align="right">   {{number_format($result['OCTOBER']['grosspay'], 2, '.', ',')}}

									</td>
									<td align="right">  {{number_format($result['NOVEMBER']['grosspay'], 2, '.', ',')}}
									</td>
									<td align="right"> {{number_format($result['DECEMBER']['grosspay'], 2, '.', ',')}}

									</td>
								</tr>
								<tr>
									<td>Total net Emoluments</td>
									<td align="right">      {{number_format($result['JANUARY']['totalEmolu'], 2, '.', ',')}}
									</td>
									<td align="right">    {{number_format($result['FEBRUARY']['totalEmolu'], 2, '.', ',')}}

									</td>
									<td align="right">     {{number_format($result['MARCH']['totalEmolu'], 2, '.', ',')}}

									</td>
									<td align="right">        {{number_format($result['APRIL']['totalEmolu'], 2, '.', ',')}}

									</td>

									<td align="right">  {{number_format($result['MAY']['totalEmolu'], 2, '.', ',')}}

									</td>
									<td align="right">     {{number_format($result['JUNE']['totalEmolu'], 2, '.', ',')}}

									</td>
									<td align="right">       {{number_format($result['JULY']['totalEmolu'], 2, '.', ',')}}
									</td>
									<td align="right">   {{number_format($result['AUGUST']['totalEmolu'], 2, '.', ',')}}
									</td>

									<td align="right">           {{number_format($result['SEPTEMBER']['totalEmolu'], 2, '.', ',')}}
									</td>

									<td align="right">         {{number_format($result['OCTOBER']['totalEmolu'], 2, '.', ',')}}
									</td>

									<td align="right">    {{number_format($result['NOVEMBER']['totalEmolu'], 2, '.', ',')}}
									</td>

									<td align="right">     {{number_format($result['DECEMBER']['totalEmolu'], 2, '.', ',')}}
									</td>
								</tr>

							</table></td>
						</tr>
					</table>
					<div class="row">
						<div class="col-md-11 col-md-offset-1">
							<a href="{{ url('/pecard') }}">Go back</a>
						</div>
					</div>
					<div >

					</div>
				</div>
			</div>
		</body>
		</html>