@extends('layouts.layout')
@section('pageTitle')
User Area
@endsection

@section('content')
<div class="box-body">
<?php $url=$_SERVER['HTTP_HOST']; ?>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-solid">
				<div class="box-header with-border">
					<h3 class="text-center">ISALU HOSPITAL LIMITED HUMAN RESOURCE APPLICATION</h3>
					<p class="text-red lead text-center">If you need assistance, please send a mail to support@supremecourt.gov.ng Thank you</p>
				</div>
				<!-- /.box-header -->
				<div class="box-body">

				</div>
				<!-- /.box-body -->
			</div>
			<!-- /.box -->
		</div>
		<?php //echo bcrypt('12345'); ?>
	</div>
</div>
@endsection

