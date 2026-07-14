@extends('layouts.layout')
@section('pageTitle')
   {{ strtoupper('create contractor voucher') }}
@endsection
@section('content')





    <div id="vim" class="modal fade">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">All comments</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form class="form-horizontal" id="deletevariableModal"
                    role="form" method="POST" action="">
                    {{ csrf_field() }}
            <div class="modal-body">
                <div class="form-group" style="margin: 0 10px;">
                    <div class="col-sm-12" id="z-space">

                    </div>
                </div>
            </div>
            </form>
                <div class="modal-footer">
                    <!--<button type="Submit" class="btn btn-success" id="putedit"></button>-->
                    <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                </div>


            </div>

          </div>
        </div>


<div class="box box-default">
  <div class="box-body box-profile">
    <div class="box-header with-border hidden-print">
      <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
    </div>
    <div class="box-body">
      <div class="row">
        <div class="col-md-12"><!--1st col-->
                    @if ($warning<>'')
                      <div class="alert alert-dismissible alert-danger">
                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                      <strong>{{$warning}}</strong>
                      </div>
                      @endif
                      @if ($success<>'')
                      <div class="alert alert-dismissible alert-success">
                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                      <strong>{{$success}}</strong>
                      </div>
                      @endif
                      @if ($error<>'')
                      <div class="alert alert-dismissible alert-danger">
                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                      <strong>{{$error}}</strong>
                      </div>
                      @endif
                      @if (count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

    <!-- <div align="center">
      <h3><b><div>{{strtoupper('ISALU HOSPITAL LIMITED')}}</div></b></h3>
      <div><h4><b>{{strtoupper('ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA')}}</b></h4></div>
    </div> -->
  <form class="form-horizontal" role="form" action="" method="post" id="form1" enctype="multipart/form-data">
        {{ csrf_field() }}
          <div class="col-md-4">
          	<a class="btn btn-warning btn-sm" href="{{ URL::previous() }}">Back</a><br>
              @php
              $getBalance = (int) $getBalance;
              $old = (int) old('totalamount');
              @endphp
                <label class="control-label"><small>Enter Sum Total Amount if making part payment</small></label>
                <input type="text" class="form-control" readonly id="totalamount" value="{{number_format(($getBalance) ? $getBalance : $old)}}" name="totalamount" placeholder="Enter total amount (Optional)" >
                <input type="hidden" name="selectedid" value="{{ $selected }}">
                <input type="hidden" name="contracttype2" value="{{ $contr5 }}">
            </div>

            <div class="col-md-4">
                <label class="control-label">&nbsp&nbsp</label><br>

                <button  class="btn btn-info btn-xs " onclick="return viewInstruct('{{$instructions}}')" >Read Instruction</button>
                @php
        		$path = base_path('../'). env('UPLOAD_PATH', '') .'/' . $sel_id.'.'.$file_ex;
    		@endphp

                @if($sel_id != "")
	    		@if(file_exists($path))
	                  <a class="btn btn-primary btn-xs " target="blank" href="/pro/file/{{$sel_id.'.'.$file_ex}}" >Download file</a>
	                @endif
                @endif
            </div>


                   <input type="hidden" name="selectedid" id="selectedid" value="{{($selectedid) ? $selectedid : old('selectedid')}}">
        <div class="col-md-12"><!--2nd col-->
          <!-- /.row -->
          <div class="form-group">

            <div class="col-md-4">
                <label class="control-label">Contract type</label>
                <input type="text" id="contracttype1" name="contracttype1" value="{{($staticcontr->contractType) ? $staticcontr->contractType : old('contracttype1')}}" readonly=""  class="form-control">
            </div>


          <div class="col-md-4">
                <label class="control-label">Allocation type {{session('alloc')}}</label>
                @if($economicCode_as !== "")
                <input type="text"  class="form-control"  readonly value="{{ $alloc3 }}" >
                <input type="hidden" class="form-control" id="allocationtype1" name="allocationtype1" placeholder="" readonly value="{{ $alloc5 }}" >
                @else
                <select onchange="return getEconomics()" class="form-control" id="allocationtype1" name="allocationtype1" placeholder="" {{ ($economicCode_as == "") ? "" : "readonly" }}>
                  <option value="">Select Allocation</option>
                  @foreach($allocationlist as $list)
                    <option value="{{$list->ID}}" {{($list->ID == $alloc1 || $list->ID == old('allocationtype1')) ? "selected" : ""}}>{{$list->allocation}}</option>
                  @endforeach
                </select>
                @endif
            </div>


            <div class="col-md-4">
                <label class="control-label">Economic code</label>
                @if($economicCode_as !== "")
                  <input type="text"  class="form-control"  readonly value="{{ $econ3 }}" >
                  <input type="hidden" name="economicCode1" id="economicCode1" class="form-control"  readonly value="{{ $economicCode_as }}" >
                @else
                <select name="economicCode1" id="economicCode1" class="form-control" >
                  <option >Select Economic Code</option>
                  @php
                  	if(old('economicCode1') !== ""){
                  		$caser = old('economicCode1');
                  	} elseif($economiccode1 !== ""){
				$caser = $economiccode1;
                  	}else {
                  		$caser = "";
                  	}
			//dd($ECONOMAIN);
                  @endphp

                  @foreach($ECONOMAIN as $list)
                  <option  value="{{ $list->ID }}" @if(old('economicCode1') == $list->ID) {{('selected')}} @endif>({{ $list->description }}) {{$list->economicCode}}</option>
                  @endforeach
                  </select>
                @endif

            </div>
            <div class="col-md-3">
                <br>
                <label class="text-danger">If you are taking funds from another vault please indicate by filling the form below</label>
                <!--<span class="btn btn-info" onclick="return showF()" id="show-btn"> <i class="fa fa-arrow-down"></i> </span>
                <span class="btn btn-warning" style="visibility: hidden;" onclick="return hideF()" id="hide-btn"><i class="fa fa-arrow-up"></i></span> -->
            </div>
        </div>
            </div>

            <div class="col-md-12" id="second-form" ><!--2nd col-->
          <!-- /.row -->
          <div class="form-group">
            <div class="col-md-6">
                <label class="control-label">Second Allocation type</label>
                <select class="form-control" id="secallocationtype" name="secallocationtype" placeholder="" onchange="return getEconomics2()">
                  <option>Select Allocation</option>
                  @foreach($allocationlist as $list)
                    <option value="{{$list->ID}}" {{($list->ID == $alloc2 || $list->ID == old('secallocationtype')) ? "selected" : ""}}>{{$list->allocation}}</option>
                  @endforeach
                </select>
            </div>


            <div class="col-md-6">
                <label class="control-label">Second Economic code</label>
                <select name="sececonomicCode" id="sececonomicCode" class="form-control"   >
              <option value="">Select Economic Code</option>
              @foreach($econocode2 as $list)
            <option  value="{{$list->ID}}" {{($list->ID == $economiccode2 || $list->ID == old('sececonomicCode')) ? "selected" : ""}}>({{$list->description}}) {{$list->economicCode}}</option>
              @endforeach
          </select>
            </div>

        </div>
            </div>

            <div class="col-md-12" ><!--2nd col-->
          <!-- /.row -->
          <div class="form-group">






        </div>
            </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->


  <!--<div align="center">
    <h3><b><div>{{strtoupper('VOUCHER ENTRY')}}</div></b></h3>
  </div>-->


            <div class="row">
      <div class="col-md-12">

        <table class="table table-striped table-condensed table-bordered ">
          <thead style="background: #fdfdfd;">
            <tr class="input-lg">
                  <th width="100" rowspan="2" class="text-center">DATE</th>
                  <th width="600"  rowspan="2" class="text-center">CONTRACTOR</th>
                  <th width="200" class="text-center">DR. </th>
                  <th width="200" class="text-center">CR. </th>
                </tr>
                <tr class="input-lg">
                  <th width="140" class="text-center"> &#8358; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>k</b></th>
                  <th width="140" class="text-center"> &#8358; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>k</b> </th>
                </tr>
              </thead>
              <tbody>
                  <tr class="input-lg">
                <th><span class="hidden-print">{{date('d/m/Y')}}</span></th>
                <th>
                  <div class="row">
                      <div class="col-sm-12">
                        <input type="text" name="beneficiaryName" readonly id="beneficiaryName" class="form-control hidden-print " style="border:1px solid #333;" value="{{ ($contractor) ? $contractor : old('beneficiaryName')}}" placeholder="Beneficiary Name" >
                        <input type="hidden" name="companyid" value="{{($companyidhid)?$companyidhid:old('companyid')}}">
                      </div>

                </div>
                </th>
                <th>
                @php
                	if(old('amount') == "" && $getBalanceas == ""){
                	$mone = $getBalance;
                	}

                	if(old('amount') != ""){
                	$mone = old('amount');
                	}

                	if($getBalanceas != ""){
                	$mone = $getBalanceas;
                	}
                	//dd($request['amount']);
                @endphp
                <span class="">
                  <input type="text" onchange="calc()" onkeyup="calc()"  name="amount" id="netAmount" class="form-control " autocomplete="off" placeholder="Gross Amount" value="{{$mone}}" >
                  <span id="errmsg" class="text-danger"></span>
                  </span>
                </th>
                <th></th>
                </tr>
                <tr class="input-lg">
                <th class="text-center"><span class="hidden-print">&#10004;</span></th>
                <th>
                  <span class="hidden-print">
                    <select name="vatselect" id="vatselect" class="form-control hidden-print " style="border: none; width: 15%; float: left;"  value="{{old('vatselect')}}" >

                  <option value="0" >0</option>
                  <option value="1" {{($vatpas == 1 || 1 == old('vatselect')) ? "selected" : ""}}>1%</option>
                  <option value="2" {{($vatpas == 2 || 2 == old('vatselect')) ? "selected" : ""}}>2%</option>
                  <option value="3" {{($vatpas == 3 || 3 == old('vatselect')) ? "selected" : ""}}>3%</option>
                  <option value="4" {{($vatpas ==4 || 4 == old('vatselect')) ? "selected" : ""}}>4%</option>
                  <option value="5" {{($vatpas == 5 || 5 == old('vatselect')) ? "selected" : ""}}>5%</option>
                  <option value="10" {{($vatpas == 10 || 10 == old('vatselect')) ? "selected" : ""}}>10%</option>
                  <option value="15" {{($vatpas == 15 || 15 == old('vatselect')) ? "selected" : ""}}>15%</option>
                  <option value="20" {{($vatpas == 20 || 20 == old('vatselect')) ? "selected" : ""}}>20%</option>
                   </select> &nbsp; VAT Payable &#60; Cash Book &#62;
                   </span>
              </th>

                <!-- <span class="hidden-print">5% VAT Payable &#60; Cash Book &#62; </span></th>-->
                <th></th>
                <th><span class="hidden-print">
                  <input type="text" id="vat" value="{{($vatvas) ? $vatvas : old('vat')}}" name="vat" readonly class="form-control" style="border: none; background: white" ></span>
                </th>
                </tr>
                <tr class="input-lg">
                <th class="text-center"><span class="hidden-print">&#10004;</span></th>
                <th> <span class="hidden-print">
                <select name="whtOrTax" id="WithholdingTax" class="form-control hidden-print" value="{{old('whtOrTax')}}"  style="border: none; width: 15%; float: left;" >
                <option value="0" selected="selected">0</option>
                <option value="1" {{($whtpas == 1 || 1 == old('whtOrTax')) ? "selected" : ""}}>1%</option>
                  <option value="2" {{($whtpas == 2 || 2 == old('whtOrTax')) ? "selected" : ""}}>2%</option>
                  <option value="3" {{($whtpas == 3 || 3 == old('whtOrTax')) ? "selected" : ""}}>3%</option>
                  <option value="4" {{($whtpas ==4 || 4 == old('whtOrTax')) ? "selected" : ""}}>4%</option>
                  <option value="5" {{($whtpas == 5 || 5 == old('whtOrTax')) ? "selected" : ""}}>5%</option>
                  <option value="10" {{($whtpas == 10 || 6 == old('whtOrTax')) ? "selected" : ""}}>10%</option>
                  <option value="15" {{($whtpas == 15 || 7 == old('whtOrTax')) ? "selected" : ""}}>15%</option>
                  <option value="20" {{($whtpas == 20 || 8 == old('whtOrTax')) ? "selected" : ""}}>20%</option>
              </select> &nbsp; Withholding Tax Payable &#60; Cash Book &#62; </span> </th>
                <th></th>
                <th><span class="hidden-print">
                    <input type="text" id="tax" name="tax" value="{{($whtvas) ? $whtvas : old('tax')}}" class="form-control" style="border: none; background: #f9f9f9" >
                  </span>
                </th>
                </tr>
                <tr class="input-lg">
                <th class="text-center"><span class="hidden-print">&#10004;</span></th>
                <th><span class="hidden-print">Amount Payable to individual/contractor &#60; Cash Book &#62; </span> </th>
                <th></th>
                <th><div class="hidden-print" id="grossAmount" style="border-bottom: 1px solid #000;">{{($amtpayble) ? $amtpayble : old('amtpayable')}}</div></th>
                </tr>
                <tr class="input-lg">
                <th></th>
                <th></th>
                <th></th>
                <th><span class="hidden-print" id="totalTaxVat"></span></th>
                <input type="hidden" name="amtpayable" value="{{($amtpayble) ? $amtpayble : old('amtpayable')}}" id="amtpayable">
                </tr>
              </tbody>
        </table>


      <table class="table table-striped table-condensed">
        <thead style="background: #fff;">
          <!--<tr class="input-lg">
                <td><h4>Voucher Type:</h4></td>
                <th>
                  <div class="row">
                    <div class="col-md-6">
                      <small>Contract/Non Contract Voucher</small>
                      <select name="voucherType" class="form-control" value="{{old('voucherType')}}" >
                        <option value="Contract">Yes. I want to generate Contract Voucher</option>
                        <option value="Other Voucher">No. I want to generate other Voucher</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                        <small>Adjustment/Normal Voucher </small>
                        <select name="adjustmentVoucher" class="form-control" value="{{old('adjustmentVoucher')}}" >
                          <option value="0">Yes. Normal Voucher</option>
                          <option value="1">Yes. Adjustment Voucher</option>
                        </select>
                    </div>
                  </div>
                </th>
            </tr>-->
            <tr class="input-lg hidden-print">
                <th valign="center" width="100"><h4>VAT Payee: </h4></th>
                <th width="600">
                  <div class="row">
                  <div class="col-sm-12">
                    <select disabled name="vatPayeeID" class="form-control" id="vatPayeeID" onchange="return getAddrvat()" style="border: 1px thin #f9f9f9;"
                    >
                    <option value="">select</option>
                          @foreach($vatwhttable as $list)
                              <option value="{{$list->ID}}" {{ ($list->ID == $vatpayeeas || $list->ID == old('vatPayeeID')) ? "selected" : "" }}>{{ $list->payee }}</option>
                          @endforeach
                    </select>
                  </div>

                  </div>
                </th>
            </tr>

            <tr class="input-lg hidden-print">
                <th valign="center" width="100"><h4>WHT Payee: </h4></th>
                <th width="600">
                  <div class="row">
                    <div class="col-sm-12">
                      <select disabled name="whtPayeeID" id="whtPayeeID" class="form-control" onchange="return getAddrwht()" style="border: 1px thin #f9f9f9;"  >
                          <option value="">select</option>
                          @foreach($vatwhttable as $list)
                              <option value="{{$list->ID}}" {{ ($list->ID == $whtpayeeas || $list->ID == old('whtPayeeID')) ? "selected" : "" }}>{{ $list->payee }}</option>
                          @endforeach
                      </select>
                    </div>

                  </div>
                </th>
            </tr>
          <tr class="input-lg">
                <td valign="top" width="100"><h4>Narration:</h4></td>
                <th width="600">
                  <input type="hidden" name="paymentdesc" value="{{($paymentdesc) ? $paymentdesc: old('paymentdesc')}}">
                  <textarea name="narration"  class="form-control input-lg hidden-print" style="border: 1px thin #f9f9f9; height: 100px;">{{($narration) ? $narration : old('narration')}}</textarea>
                </th>
            </tr>
            <!--<tr>
              <td valign="top" width="100"><h4> Funds Allocation </h4></td>
              <td>

              <select name="funds_allocation" class="form-control hidden-print" />
                  <option value="general" selected="selected">General </option>
                  <option value="cjn"> CJN </option>

            </select>
              </td>
            </tr>-->
            <tr class="input-lg">
                <th valign="center" width="100"><h4>FILENO</h4></th>
                <th><input type="text" name="filenoas" class="form-control hidden-print" readonly style="border: 1px thin #f9f9f9;" value="{{ ($filenoas) ? $filenoas : old('fileno') }}" ></th>
            </tr>
            <tr class="input-lg">
                <th valign="center" width="100"><h4>PVNO</h4></th>
                <th><input type="text" name="pvno" class="form-control hidden-print" autocomplete="off" style="border: 1px thin #f9f9f9;" value="{{($pvnoas)? $pvnoas : old('pvno')}}" ></th>
            </tr>
            <tr class="input-lg">
                <th valign="center" width="100"><h4>Prepared By:</h4></th>
                <th><input type="text" name="preparedBy" readonly="readonly" class="form-control hidden-print" style="border: 1px thin #f9f9f9;" value="{{($currentuser)? $currentuser : old('preparedBy')}}" ></th>
            </tr>
            <tr class="input-lg">
                <th valign="center" width="100"><h4>Liability</h4></th>
                <th><select name="liabilityBy"  class="form-control hidden-print" style="border: 1px thin #f9f9f9;" >
                    <option value=""></option>
                    @foreach($liabilityby as $list)
                      <option value="{{$list->username}}" {{ ($liabilityByas == $list->username || $list->username == old('liabilityBy'))? "selected" : "" }}>{{ $list->name }}</option>
                    @endforeach
                </select></th>
            </tr>
             <tr class="input-lg">
                <th valign="center" width="100"><h4>Date: </h4></th>
                <th><span class="hidden-print">
                  <input readonly type="text" value="{{($todayDateas) ? $todayDateas : old('todayDate')}}" readonly="readonly" name="todayDate" id="todayDate" class="form-control col-lg-4"  placeholder="Select Date">
                </span></th>
            </tr>



          </thead>
      </table>
      <center><button class="btn btn-success" onclick="return submitVoucher()">Create Voucher</button></center>
      <input type="hidden" name="finalsubmit" id="finalsubmit" value="">
      </div>
    </div><!-- /.col -->
  </div>
            </div>

          <hr />
        </div>
       </form>
  </div>
</div>








@endsection
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@stop
@section('styles')
<style type="text/css">
    .modal-dialog {
width:10cm
}

.modal-header {

background-color: #006600;

color:#FFF;

}

</style>
@endsection

@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<script>
$(document).ready(function(){
$('#allocationtype1').change(function()
{
var id = $this.val();
$.ajax({
  url: murl +'/session set',
  type: "post",
  data: {'nameID': id, '_token': $('input[name=_token]').val()},
  success: function(data){
    console.log(data);

  }
});
});
});
</script>
<script>

    function editfunc(a,b,c,d,e,f,g,h,i,j)
    {
    $(document).ready(function(){
        $('#contractor').val(a);
        $('#phone').val(b);
        $('#email').val(c);
        $('#address').val(d);
        $('#bank').val(e);
        $('#account').val(f);
        $('#sortcode').val(g);
        $('#tin').val(h);
        $('#C_id').val(i);
        $('#status').val(j);
        $("#editModal").modal('show');
     });
    }

    function delfunc(a)
  {
  $(document).ready(function(){
  $('#conID').val(a);
  $("#delModal").modal('show');
  });
  }

  function getEconomics(){

    var all = document.getElementById('allocationtype1').value;
    var con =  document.getElementById('contracttype1').value;
    var frm = document.getElementById('form1');

    if(all !== "" && con !== ""){
      return frm.submit();
    }
  }



  function getBalance(){
    var eco = document.getElementById('economicCode1').value;
    var frm = document.getElementById('form1');
    if(eco !== ""){
      return frm.submit();
    }
  }

  function viewInstruct(list){
    //<label class="control-label"><i id="vi"></i></label>
       if(list !== ""){
        var a = JSON.parse(list);
        //console.log(a);
        space = document.getElementById('z-space');
        space.innerHTML = '';
        for(i = 0; i < a.length; i++){
          space.innerHTML += '<p><b id="vi">'+ a[i].comment +'</b> - <small class="text-warning"> <i>'+a[i].name+', posted '+ a[i].date_added+' at '+ a[i].time +'</i></small></p><br>';
        }
       }
       $('#vim').modal('show');
       return false;
  }

  function setID(id){
      document.getElementById('selectedid').value = id;
      //var all = document.getElementById('allocationtype1').value;
      //var con =  document.getElementById('economicCode1').value;
      document.getElementById('finalsubmit').value = 'gettingstuff1';
      return document.getElementById('form1').submit();

  }

  function submitVoucher(){
      	//console.log(document.getElementById('form1'));
      	//return false;
       	document.getElementById('finalsubmit').value = 'complete';
      	return document.getElementById('form1').submit();
  }

  function getEconomics2(){
    var all = document.getElementById('secallocationtype').value;
    var con =  document.getElementById('contracttype1').value;
    var frm = document.getElementById('form1');
    if(all !== "" && con !== ""){
      return frm.submit();
    }
  }

  function getAddrvat(){
      console.log(document.getElementById('vatPayeeID').value);
      return false;
  }


   function getAddrwht(){
      console.log(document.getElementById('whtPayeeID').value);
      return false;
  }

</script>

    </script>
    <script>
///////////////////////DATE/////////////////////////////////
  $( function(){
      $("#todayDate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    });
  ///////////////////////DATE/////////////////

    //$('#netAmount').focus();

    $('#companyGetLookUp').change( function()
  {
    $('#beneficiaryName').val($('#companyGetLookUp').val());
  });



  vt = document.getElementById("vatPayeeID");
  wh = document.getElementById("whtPayeeID");

  if($("#vatselect").val() !== "0"){
  	vt.disabled = false;
  }

  if($("#WithholdingTax").val() !== "0"){
  	wh.disabled = false;
  }

  if($("#vatselect").val() == "0"){
  	vt.disabled = true;
  	vt.value = "";
  }

  if($("#WithholdingTax").val() == "0"){
  	wh.disabled = true;
  	wh.value = "";
  }



  $("#vatselect ,#WithholdingTax").change( function() {

  vt = document.getElementById("vatPayeeID");
  wh = document.getElementById("whtPayeeID");

  if($("#vatselect").val() !== "0"){
  	vt.disabled = false;
  }

  if($("#WithholdingTax").val() !== "0"){
  	wh.disabled = false;
  }

  if($("#vatselect").val() == "0"){
  	vt.disabled = true;
  	vt.value = "";
  }

  if($("#WithholdingTax").val() == "0"){
  	wh.disabled = true;
  	wh.value = "";
  }


  var amount = $("#netAmount").val();
  if( amount ==""){

  //alert error when amount is empty , vat and tax not applicable to zero amount
  alert("amount cant be empty");
  $("#netAmount").focus();

  }
  else{

        var vat_rate = $("#vatselect").val();
  var tax_rate = $("#WithholdingTax").val();

  console.log( 'vat rate', vat_rate );
        console.log( 'tax rate', tax_rate );

  //we have amount not set to empty , calculate the vat and the tax
  //display it and set it to the respective elements
  //calculate vat value
  var vat = (vat_rate /100) * amount;

  //calculate tax value
   var tax = ( tax_rate / 100 ) * amount;


  $("#vat").val(vat);

  //display the tax to the user
  $("#tax").val(tax);

         //calculate net payable
          var netpay = Number( amount) - ( Number ( vat ) + Number( tax) ) ;
            $("#grossAmount").html( netpay);
            $("#amtpayable").val(netpay);

  }

  })

function calc(){
    var amount = $("#netAmount").val();
  if( amount ==""){

  //alert error when amount is empty , vat and tax not applicable to zero amount
  alert("amount cant be empty");
  $("#netAmount").focus();

  }
  else{

        var vat_rate = $("#vatselect").val();
  var tax_rate = $("#WithholdingTax").val();

  console.log( 'vat rate', vat_rate );
        console.log( 'tax rate', tax_rate );

  //we have amount not set to empty , calculate the vat and the tax
  //display it and set it to the respective elements
  //calculate vat value
  var vat = (vat_rate /100) * amount;

  //calculate tax value
   var tax = ( tax_rate / 100 ) * amount;


  $("#vat").val(vat);

  //display the tax to the user
  $("#tax").val(tax);
  //$("#netAmount").val(amount);

         //calculate net payable
          var netpay = Number( amount) - ( Number ( vat ) + Number( tax) ) ;
            $("#grossAmount").html( netpay);
            $("#amtpayable").val(netpay);


}
  function showF(){
    document.getElementById('second-form').style.display = 'block';
    document.getElementById('show-btn').style.visibility = 'hidden';
    document.getElementById('hide-btn').style.visibility = 'visible';
}

}
  function hideF(){
    document.getElementById('second-form').style.display = 'none';
    document.getElementById('hide-btn').style.visibility = 'hidden';
    document.getElementById('show-btn').style.visibility = 'visible';
  }
  </script>


@stop
