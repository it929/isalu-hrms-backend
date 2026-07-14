@extends('layouts.layout')
@section('pageTitle')
NHF Reports
@endsection
@section('content')
<form method="post" action="{{ url('/nhf-report/list') }}">
  <div class="box-body" style="background:#FFF;">
    <div class="row">
      {{ csrf_field() }}    
      <h2 class="text-center">ISALU HOSPITAL LIMITED</h2>
           <h3 class="text-center">MONTHLY NHF CONTRIBUTION SCHEDULE</h3>
        <div class="col-md-12">
            <div class="" style="margin-bottom:20px;">
                <div class="init">
                    <strong>EMPLOYER NAME:</strong> <span> SUPREME COURT</span>
                </div>
                <div class="init">
                    <strong>EMPLOYER NHF NUMBER:</strong> <span> </span>
                </div>
                <div class="initinit">
                    <strong>MONTH/YEAR:</strong> <span> {{$month}}/{{$year}}  </span>
                </div>
                <div class="init">
                    <strong>AMOUNT PAID:</strong> <span>{{number_format($totalSum,2)}} </span>
                </div>
            </div> 
        
        <table class="table table-striped table-condensed table-bordered table-reponsive">
                  <thead class="text-gray-b">
                        <tr>
                           <th>S/N</th>
                           <th>CONTRIBUTOR'S <br/> NHF No.</th>
                           <th>CONTRIBUTOR'S <br/> LAST NAME</th>
                           <th>CONTRIBUTOR'S <br/> FIRST NAME</th>
                           <th>CONTRIBUTOR'S <br/> MIDDLE NAME</th>
                           <th>CONTRIBUTOR'S <br/> STAFF ID</th>
                           <th>STAFF <br/> MOBILE <br/> NUMBER</th>
                           <th>STAFF <br/> EMAIL ADDRESS</th>
                           <th>BASIC SALARY</th>
                           <th>CONTRIBUTION <br/> AMOUNT</th>
                           <th>REMARK</th>
                        </tr>
                  </thead>
                  <tbody>
                    @php
		          $i=1;
		          @endphp
		           
                    @foreach($nhf as $list)
                        <tr>
                            
                          <td>{{ $i++ }}</td>
                          <td>{{$list->nhfNo}}</td>
                          <td>{{$list->surname}}</td>
                          <td>{{$list->first_name}}</td>
                          <td>{{$list->othernames}}</td>
                          <td>{{$list->fileNo}}</td>
                          <td>{{$list->phone}}</td>
                          <td>{{$list->email}}</td>
                          <td>{{number_format($list->Bs,2)}}</td>
                          <td class="text-right">{{number_format($list->NHF,2)}}</td>
                          <td>{{$list->month}} {{$list->year}} NHF Contribution</td>
                          
                        </tr>
                      @endforeach
                   <tr>
                       <td colspan="9"><strong>TOTAL:</strong></td>
                       <td class="text-right"><strong>{{number_format($totalSum,2)}}</strong></td>
                       <td></td>
                   </tr>
                  </tbody>
              </table>

      
        </div>
      </div>
    </div><!-- /.col -->
  </div><!-- /.row -->
</form>
@endsection
@section('styles')
<style>
    .init
    {
        line-height:30px;
    }
</style>
@endsection

@section('scripts')

<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>

<script type="text/javascript">

$(document).ready(function(){
 
$("#court").on('change',function(e){
   e.preventDefault();
  var id = $(this).val();
//alert(id);
  $token = $("input[name='_token']").val();
 $.ajax({
  headers: {'X-CSRF-TOKEN': $token},
  url: murl +'/session/court',
 
  type: "post",
  data: {'courtID':id},
  success: function(data){
  location.reload(true);
  //console.log(data);
  }
});

});
 });
</script>


@endsection

