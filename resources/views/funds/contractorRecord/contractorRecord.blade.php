 @extends('layouts.layout')

 @section('pageTitle')
 @endsection

 @section('content')
     <div class="box-body">
         <div class="box box-default">
             <div class="box-body box-profile">

                 <div class="box-body">
                     <div class="row">
                         <div class="col-xs-2"><img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                                 style="width:100%; height:auto;"></div>
                         <div class="col-xs-8">
                             <div>
                                 <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong>
                                 </h3>
                                 <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED, THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h4>
                                 <h4 class="text-center text-success"><strong>Contractor Records</strong></h4>
                             </div>
                         </div>
                         <div class="col-xs-2"><img src="{{ asset('Images/coat.jpg') }}" class="responsive"></div>
                     </div <div class="box-body">
                     <div class="row">
                         <div class="col-sm-12">
                             @include('funds.Share.message')
                             <h4 class="noprint"></h4>
                             <div class="noprint box-body">
                                 <div class="col-md-3 noprint">
                                     <label class="control-label noprint">Contractor</label>
                                     <select class="form-control" id="contractorList" name="contractorList">
                                         <option value=''>-Select Contractor-</option>
                                         @foreach ($contractor as $con)
                                             <option value="{{ $con->id }}"
                                                 {{ $companyId == $con->id ? 'selected' : '' }}>
                                                 {{ $con->contractor }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-3">
                                     <label>Contract Status</label>
                                     <select class="form-control" id="statusList" name="statusList">
                                         <option value=''>-Select-</option>
                                         <option value="1" {{ $statusId == 1 ? 'selected' : '' }}>Approved</option>
                                         <option value="3" {{ $statusId == 3 ? 'selected' : '' }}>Pending</option>
                                         <option value="2" {{ $statusId == 2 ? 'selected' : '' }}>Denied</option>
                                     </select>
                                 </div>
                                 <div class="col-md-2 col-md-offset-1">
                                     <label>To</label>
                                     <input type="text" class="form-control" id="datepicker1" name="datepicker1"
                                         value={{ $picker1 }} />
                                 </div>
                                 <div class="col-md-2">
                                     <label>From</label>
                                     <input type="text" class="form-control" id="datepicker2" name="datepicker2"
                                         value={{ $picker2 }} />
                                 </div>




                             </div>
                         </div>
                         <table id="mytable" class="table table-bordered" cellpadding="10">
                             <thead>
                                 <tr>
                                     <th>F/N</th>
                                     <th>Beneficiary</th>
                                     <th>Contract Description</th>
                                     <th>Contract Value</th>
                                     <th>Total Paid</th>
                                     <th>Date Awarded</th>
                                     <th class="noprint">Action</th>

                                 </tr>
                             </thead>
                             <tbody>
                                 @php
                                     $ttval = 0;
                                     $ttpaid = 0;
                                 @endphp
                                 @foreach ($getContractorTable as $list)
                                     <tr>
                                         <td>{{ $list->fileNo }}</td>
                                         <td>{{ $list->contractor }}</td>
                                         <td>{{ $list->ContractDescriptions }}</td>
                                         <td style="text-align:right;">₦{{ number_format($list->contractValue, 2) }}</td>
                                         <td style="text-align:right;">₦{{ number_format($list->grosspayment, 2) }}</td>
                                         <td>{{ $list->dateAward }}</td>
                                         <td class="noprint"> <a
                                                 href="{{ url('/contractor-record/view/') }}/{{ $list->ContID }}"
                                                 type="button" class="btn btn-primary fa fa-eye"> View</a> </td>
                                         @php
                                             $ttval += $list->contractValue;
                                             $ttpaid += $list->grosspayment;
                                         @endphp
                                     </tr>
                                 @endforeach

                             </tbody>
                             <tfoot>
                                 <tr>
                                     <td></td>
                                     <td></td>
                                     <th>Total</th>
                                     <td style="text-align:right; padding:10px">₦{{ number_format($ttval, 2) }}</td>
                                     <td style="text-align:right;padding:10px;">₦{{ number_format($ttpaid, 2) }}</td>
                                     <td></td>
                                 </tr>
                             </tfoot>
                         </table>

                     </div>


                 </div>
             </div>
         </div>

         <form id="SearchContract" method="post" action="{{ url('/contractor-record') }}">
             {{ csrf_field() }}
             <input type="hidden" id="getCompany" name="getCompany" />
             <input type="hidden" id="getStatus" name="getStatus" />
             <input type="hidden" id="getTime1" name="getTime1" />
             <input type="hidden" id="getTime2" name="getTime2" />
         </form>
     @endsection

     @section('styles')
         <style type="text/css">
             .table,
             tr,
             th,
             td {
                 border: #9f9f9f solid 1px !important;
                 font-size: 12px !important;

             }

             @media print {
                 .noprint {
                     display: none;
                 }
             }

             @media screen {}

             @media print {
                 .hidden-print {
                     display: none !important
                 }

                 .dt-buttons,
                 .dataTables_info,
                 .dataTables_paginate,
                 .dataTables_filter {
                     display: none !important
                 }
             }

             .table tr td {}
         </style>
         <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
     @endsection

     @section('scripts')
         <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
         <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
         <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
         <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
         <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
         <script type="text/javascript">
             $('#contractorList').change(function() {
                 $('#getCompany').val($('#contractorList').val());
                 $('#getStatus').val($('#statusList').val());
                 $('#getTime1').val($('#datepicker1').val());
                 $('#getTime2').val($('#datepicker2').val());
                 $('#SearchContract').submit();
             });

             $('#statusList').change(function() {
                 $('#getCompany').val($('#contractorList').val());
                 $('#getStatus').val($('#statusList').val());
                 $('#getTime1').val($('#datepicker1').val());
                 $('#getTime2').val($('#datepicker2').val());
                 $('#SearchContract').submit();
             });

             $(document).ready(function() {
                 $('#mytable').DataTable({
                     dom: 'Bfrtip',
                     buttons: [{
                         extend: 'print',
                         customize: function(win) {
                             $(win.document.body)
                                 .css('font-size', '10pt')
                                 .prepend(
                                     ''
                                 );

                             $(win.document.body).find('table')
                                 .addClass('compact')
                                 .css('font-size', 'inherit');
                         }
                     }]
                 });
             });




             $("#datepicker1").datepicker({
                 dateFormat: "yy-mm-dd",
                 onSelect: function(dateText) {
                     $('#getCompany').val($('#contractorList').val());
                     $('#getStatus').val($('#statusList').val());
                     $('#getTime1').val($('#datepicker1').val());
                     $('#getTime2').val($('#datepicker2').val());
                     $("#SearchContract").submit();
                 }

             });

             $("#datepicker2").datepicker({
                 dateFormat: "yy-mm-dd",
                 onSelect: function(dateText) {
                     $('#getCompany').val($('#contractorList').val());
                     $('#getStatus').val($('#statusList').val());
                     $('#getTime1').val($('#datepicker1').val());
                     $('#getTime2').val($('#datepicker2').val());
                     $("#SearchContract").submit();
                 }
             });
         </script>
     @endsection
