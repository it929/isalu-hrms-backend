@extends('layouts.layout')

@section('pageTitle')
  Control Variable
@endsection

@section('content')

  <div align="center" style="background-color:#9f9f9f; color:#fff; padding:5px;">
    <strong>ISALU HOSPITAL LIMITED</strong>
    <br />
  </div>

  <table id="example" class="table table-striped table-responsive table-condensed table-border" cellspacing="0" width="100%">
		<thead style="background-color:#c3c3c3; color:#fff;">
            <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Use of Govt Vehicle</th>
                <th>Nicn Coop</th>
                <th>Ctls Labour</th>
                <th>Ctls Fed Sec</th>
				<th>Fed. Housing Loan</th>
                <th>Motor Adv</th>
                <th>Bicycle Adv</th>
                <th>Tax</th>
                <th>NHF</th>
			    <th>Union Dues</th>
            </tr>
        </thead>
        <tfoot style="background-color:#c3c3c3; color:#fff;">
            <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Use of Govt Vehicle</th>
                <th>Nicn Coop</th>
                <th>Ctls Labour</th>
                <th>Ctls Fed Sec</th>
				<th>Fed. Housing Loan</th>
                <th>Motor Adv</th>
                <th>Bicycle Adv</th>
                <th>Tax</th>
                <th>NHF</th>
				<th>Union Dues</th>
            </tr>
        </tfoot>
        
        <tbody>
				
            @foreach($staffDetails as $details)
                <tr>
                    <td>{{$details -> month}}</td>
                    <td>{{$details -> year}}</td>
                    <td>{{$details -> ugv}}</td>
                    <td>{{$details -> nicnCoop}}</td>
                    <td>{{$details -> ctlsLab}}</td>
                    <td>{{$details -> ctlsFed}}</td>
                    <td>{{$details -> fedHousing}}</td>
                    <td>{{$details -> motorAdv}}</td>
                    <td>{{$details -> bicycleAdv}}</td>
                    <td>{{$details -> tax}}</td>
                    <td>{{$details -> nhf}}</td>
                    <td>{{$details -> unionDues}}</td>
                </tr>
		    @endforeach
				 
        </tbody>
        
    </table>

    <div align="center">
        <a href="{{ url('/variable/create') }}" title="Go Back" class="btn btn-default"> Go Back </a>
    </div>
    <br />
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable();
        } );
    </script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js">

@endsection