@extends('layouts.layout')
@section('pageTitle')
    Staff Control Variable
@endsection



@section('content')

    <div id="editModal" class="modal fade">
        <div class="modal-dialog box box-default" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Staff Control Variable</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form-horizontal" id="editpartModal" name="editpartModal" role="form" method="POST"
                    action="">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group" style="margin: 0 10px;">
                            <div class="col-sm-12">
                                <label class="control-label">Amount</label>
                                <input type="text" value="{{ $amount }}" name="amount-edit" id="mmt"
                                    class="form-control" placeholder="e.g 11000"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*?)\..*/g, '$1');">
                            </div>

                            <div class="col-sm-12">
                                <label class="control-label">Remarks</label>
                                <input type="text" name="remarks" id="remarks" required class="form-control">
                            </div>

                            <input type="hidden" id="edit-hidden" name="edit-hidden" value="">
                            <input type="hidden" id="courtid1" name="court" value="">
                            <input type="hidden" id="divid1" name="division" value="">
                            <input type="hidden" name="fileNofordelete" value="{{ $fileNo }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="Submit" class="btn btn-success">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                </form>
            </div>

        </div>
    </div>


    <div id="DeleteModal" class="modal fade">
        <div class="modal-dialog box box-default" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete Variable</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form-horizontal" id="deletevariableModal" role="form" method="POST" action="">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group" style="margin: 0 10px;">
                            <div class="col-sm-12">
                                <label class="col-sm-9 control-label"><b>Are you sure you want to delete this
                                        record?</b></label>
                            </div>
                            <input type="hidden" id="deleteid" name="deleteid" value="">
                            <input type="hidden" id="courtid" name="court" value="">
                            <input type="hidden" id="divid" name="division" value="">
                            <input type="hidden" name="fileNofordelete" value="{{ $fileNo }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="Submit" class="btn btn-success">Yes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    </div>

                </form>
            </div>

        </div>
    </div>


    <div class="panel panel-success">
          <div class="panel-heading  hidden-print">
                <h3 class="panel-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
        <div class="panel-body box-profile">


            <div class="col-md-12">
                @if (count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @if ($error != '')
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        <p>{{ $error }}</p>
                    </div>
                @endif
                @if ($success != '')
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong> <br />
                        {{ $success }}
                    </div>
                @endif
                @if (session('err'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Input Error!</strong> <br />
                        {{ session('err') }}
                    </div>
                @endif

            </div>


            <div class="panel-body">
                @include('funds.Share.message')

                <form class="form-horizontal" id="mainform" name="mainform" role="form" method="post"
                    action="">
                    {{ csrf_field() }}
                    <div class="row">
                            <div class="col-md-12">
                                <!-- /.row -->
                                {{-- <div class="form-group"> --}}

                                    @if ($CourtInfo->courtstatus == 1)
                                        <div class="col-md-3">
                                            <label class="control-label">Court</label>
                                            <select required class="form-control" id="court"
                                                onchange="getDivisions()" name="court">
                                                <option value="">-select Court</option>
                                                @foreach ($courtList as $list)
                                                    <option value="{{ $list->id }}"
                                                        {{ $court == $list->id ? 'selected' : '' }}>
                                                        {{ $list->court_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" id="court" name="court"
                                            value="{{ $CourtInfo->courtid }}">
                                    @endif

                                    @if ($CourtInfo->divisionstatus == 1 && Auth::user()->is_global == 1)
                                        <div class="col-md-3">
                                            <label class="control-label">Division</label>
                                            {{-- <select required class="form-control" id="division" name="division" onchange="getStaff()" > --}}
                                            <select required class="form-control" id="division" name="division">
                                                <option value="">-select Division </option>
                                                @foreach ($courtdivision as $list)
                                                    <option value="{{ $list->divisionID }}"
                                                        {{ $division == $list->divisionID ? 'selected' : '' }}>
                                                        {{ $list->division }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="col-md-3">
                                                <label>Division</label>
                                                <input type="text" class="form-control" id="divisionName"
                                                    name="divisionName" value="{{ $curDivision->division }}" readonly>
                                        </div>
                                        <input type="hidden" id="division" name="division"
                                            value="{{ Auth::user()->divisionID }}">
                                        <!--<input type="hidden" id="division" name="division" value="{{ $CourtInfo->divisionid }}">-->
                                    @endif

                                    <div class="col-md-3">
                                        <input type="hidden" id="fileNo" name="fileNo"
                                            value="{{ $fileNo }}">
                                        <label class="control-label">Staff Names Search</label>
                                        <input type="text" id="userSearch" autocomplete="off" list="enrolledUsers"
                                            class="form-control" onchange="StaffSearchReload()">
                                        <datalist id="enrolledUsers" name="userSearch">

                                            @foreach ($courtstaff as $b)
                                                <option value="{{ $b->ID }}">
                                                    {{ $b->fileNo }}:{{ $b->surname }} {{ $b->first_name }}
                                                    {{ $b->othernames }}</option>
                                            @endforeach
                                        </datalist>

                                    </div>


                                    <div class="col-md-3">
                                        <label class="control-label">File Number</label>
                                        <input required type="text" value="{{ $staff->fileNo }}" name="sname"
                                            readonly="readonly" class="form-control">
                                    </div>

                                     <div class="col-md-3">
                                        <label class="control-label">Staff name</label>
                                        <input required type="text"
                                            value="{{ $staff->surname }} {{ $staff->first_name }} {{ $staff->othernames }}"
                                            name="sname" readonly="readonly" class="form-control">
                                    </div>

                                {{-- </div> --}}
                            </div>
                            <!-- /.col -->
                        <!-- /.row -->
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{-- <div class="col-md-3">
                                        <label class="control-label">File Number</label>
                                        <input required type="text" value="{{ $staff->fileNo }}" name="sname"
                                            readonly="readonly" class="form-control">
                                    </div> --}}

                                    {{-- <div class="col-md-4">
                                        <label class="control-label">Staff name</label>
                                        <input required type="text"
                                            value="{{ $staff->surname }} {{ $staff->first_name }} {{ $staff->othernames }}"
                                            name="sname" readonly="readonly" class="form-control">
                                    </div> --}}

                                    @if ($staff->fileNo != '')
                                        <div class="col-md-4">
                                            <label for="staffBank">Staff Bank</label>
                                            <input type="Text" name="staffBank" id="staffBank" class="form-control"
                                                readonly
                                                value="@if ($staff != '') {{ $staff->bank }} @endif" />
                                        </div>

                                        <div class="col-md-4">
                                            <label for="netPay">Last Net Emolument <i
                                                    class="fa fa-exclamation blinking"></i> </label>
                                            <input type="text" name="netPay" id="netPay" class="form-control"
                                                style="background-color: red; color: #fff; font-weight: 900" readonly
                                                value="@if ($staffLastNetEmolument != '') {{ number_format($staffLastNetEmolument->NetPay, 2, '.', ',') }} @endif" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <!-- /.row -->
                                <div class="form-group">
                                    <div class="col-md-6">
                                        <label class="control-label">Variable Type</label>
                                        <select required name="cvtype" class="form-control" id="cvtype"
                                            onchange="Reload()">
                                            <option value="">-select Type</option>
                                            @foreach ($EarningDeductionType as $desc)
                                                <option value="{{ $desc->ID }}"
                                                    {{ $desc->ID == $cvtype ? 'selected' : '' }}>{{ $desc->Particular }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="control-label">Description</label>
                                        <select required name="cvdesc" class="form-control" id="cvdesc">
                                            <option value="">-select Description</option>
                                            @foreach ($cvdesc as $desc)
                                                {{-- <option  value="{{$desc->ID}}" {{ ($desc->ID == $cvdesc ) ? "selected" : ""}} >{{ $desc->description }}</option> --}}
                                                <option value="{{ $desc->ID }}">{{ $desc->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="control-label">Amount</label>
                                        <input required type="number" step="0.01" value="{{ $amount }}"
                                            name="amount" id="amount" class="form-control" placeholder="e.g 11000"
                                            onkeyup='TargetRevalidate()'>
                                    </div>

                                    @php
                                        $limitcheck = '';
                                        if ($hiddenlimit == 1) {
                                            $limitcheck = 'checked';
                                        }
                                        $recyclecheck = '';
                                        if ($hiddenrecycle == 1) {
                                            $recyclecheck = 'checked';
                                        }
                                    @endphp
                                    <div class="col-md-2">
                                        <label for="" class="control-label"></label>
                                        <div class="checkbox" onclick="ClickLimit()">
                                            <label><input type="checkbox" {{ $limitcheck }}> No Limit</label>
                                        </div>
                                        <input type="hidden" id="hiddenlimit" name="hiddenlimit"
                                            value="{{ $hiddenlimit }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="" class="control-label"></label>
                                        <div id="recycle" class="checkbox" onclick="ClickRecycle()">
                                            <label><input type="checkbox" {{ $recyclecheck }}> One-Time</label>
                                        </div>
                                        <input type="hidden" id="hiddenrecycle" name="hiddenrecycle"
                                            value="{{ $hiddenrecycle }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="control-label">Target Amount</label>
                                        <input required type="number" step="0.01" value="{{ $tamount }}"
                                            name="tamount" class="form-control" placeholder="e.g 11000" id="tamount">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="" class="control-label"></label>
                                        <button type="submit" class="btn btn-success form-control" name="add">
                                            <i class="fa fa-btn fa-plus"></i> Add
                                        </button>
                                    </div>

                                </div>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>

                </form>

                <hr />
                <div class="row">
                    <div class=" col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-highlight">
                                <thead>
                                    <tr bgcolor="#c7c7c7">
                                        <th>S/N</th>
                                        <th>CV Description</th>
                                        <th>Amount</th>
                                        <th>Target Amount</th>
                                        <th>Target Balance</th>
                                        <th>With Limit</th>
                                        <th>Last processed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                @php $i = 1; @endphp
                                <tbody>

                                    @foreach ($tablecontent as $list)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $list->description }} @if ($list->remarks)
                                                    ({{ $list->remarks }})
                                                @endif
                                            </td>
                                            <td>{{ number_format($list->amount, 2, '.', ',') }}</td>
                                            <td>
                                                @if ($list->targetAmount != '' && $list->recycling == 0)
                                                    {{ number_format($list->targetAmount, 2, '.', ',') }}
                                                @else
                                                    Not Applicable
                                                @endif
                                            </td>
                                            <td>
                                                @if ($list->targetAmount != '' && $list->recycling == 0)
                                                    {{ number_format($list->targetAmount - $list->totaloffset, 2, '.', ',') }}
                                                @else
                                                    Not Applicable
                                                @endif
                                            </td>
                                            <td>
                                                @if ($list->recycling == 1)
                                                    No
                                                @else
                                                    yes
                                                @endif
                                            </td>
                                            <td>
                                                @if ($list->lastperiod != '')
                                                    {{ $list->lastperiod }}
                                                @else
                                                    Not yet processed
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" style="cursor: pointer;"
                                                    onclick="editfunc('{{ $list->ID }}','{{ $list->description }}', '{{ $list->courtID }}', '{{ $list->divisionID }}', '{{ $list->amount }}', '{{ $list->remarks }}')">
                                                    <i class="fa fa-btn fa-pencil"></i> Edit
                                                </button>

                                                <button class="btn btn-sm btn-danger" style="cursor: pointer;"
                                                    onclick="deletefunc('{{ $list->ID }}','{{ $list->description }}', '{{ $list->courtID }}', '{{ $list->divisionID }}')">
                                                    <i class="fa fa-btn fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



@endsection

@section('styles')
    <style type="text/css">
        .blinking {
            color: red;
            animation: blink 1s infinite;
        }

        .modal-dialog {
            width: 13cm
        }

        .modal-header {

            background-color: #006600;

            color: #FFF;

        }

        #partStatus {
            width: 2.5cm
        }
    </style>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script>
        function editfunc(x, y, z, a, n, r) {
            document.getElementById('edit-hidden').value = x;
            document.getElementById('deleteid').value = null;
            document.getElementById('courtid1').value = z;
            document.getElementById('divid1').value = a;
            document.getElementById('mmt').value = n;
            document.getElementById('remarks').value = r;

            $("#editModal").modal('show')
        }

        function deletefunc(x, y, z, a) {
            //$('#deleteid').val() = x;
            document.getElementById('edit-hidden').value = null;
            document.getElementById('deleteid').value = x;
            document.getElementById('courtid').value = z;
            document.getElementById('divid').value = a;
            $("#DeleteModal").modal('show');
        }

        function getDivisions() {
            document.getElementById('fileNo').value = "";
            if ($('#court').val() !== "") {
                $('#form1').submit();
            }
        }

        function getStaff() {
            document.forms["mainform"].submit();
        }

        function getTable() {
            if ($('#fileNo').val() !== "") {
                $('#form1').submit();
            }
        }

        function checkForm() {
            var court = $('#court').val();
            division = $('#division').val();
            fileno = $('#fileNo').val();
            fname = $('#fname').val();
            oname = $('#oname').val();
            sname = $('#sname').val();
            desc = $('#cvdesc').val();
            amount = $('#amount').val();
            if (court == "") {
                alert('You have empty fields!');
            } else {
                if (division == "") {
                    alert('You have empty fields');
                } else {
                    if (fileno == "") {
                        alert('you have empty fields!');
                    } else {
                        if (fname == "") {
                            alert('you have empty fields!');
                        } else {
                            if (oname == "") {
                                alert('you have empty fields');
                            } else {
                                if (sname == "") {
                                    alert('you have empty fields!');
                                } else {
                                    if (desc == "") {
                                        alert('you have empty fields!');
                                    } else {
                                        if (amount == "") {
                                            alert('you have empty fields!');
                                        } else {
                                            $('#form1').submit();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return false;
        }

        function Reload() {
            document.forms["mainform"].submit();
            return;
        }

        function StaffSearchReload() {
            document.getElementById('fileNo').value = document.getElementById('userSearch').value;
            document.forms["mainform"].submit();
            return;
        }

        function ClickLimit() {
            if (document.getElementById('hiddenlimit').value == 1) {

                document.getElementById('tamount').removeAttribute('disabled');
                document.getElementById('hiddenlimit').value = 0;
            } else {
                document.getElementById('tamount').setAttribute('disabled', 'disabled');
                document.getElementById('hiddenlimit').value = 1;
                //document.getElementById('recycle').setAttribute('disabled', 'disabled').off('click');
                for (div of document.getElementById('recycle')) {
                    let children = div.children;
                    for (child of children) {
                        child.disabled = true;
                    }
                }
            }
            return;
        }

        function ClickRecycle() {
            if (document.getElementById('hiddenrecycle').value == 1) {
                document.getElementById('hiddenrecycle').value = 0;
            } else {
                document.getElementById('hiddenrecycle').value = 1;
                document.getElementById('tamount').value = document.getElementById('amount').value;
            }
            return;
        }

        function TargetRevalidate() {

            if (document.getElementById('hiddenrecycle').value == 1) {
                document.getElementById('tamount').value = document.getElementById('amount').value;
            }

            // Get the values of the amount and netPay inputs
            var amount = parseFloat(document.getElementById('amount').value);
            var netPay = parseFloat(document.getElementById('netPay').value.replace(/,/g, ''));
            let cvtype = document.getElementById('cvtype').value;

            // if (cvtype == '2') {
            //     // Check if amount is greater than netPay
            //     // if (!isNaN(amount) && !isNaN(netPay) && amount > netPay) {
            //     //     Swal.fire({
            //     //         icon: 'error',
            //     //         title: 'Invalid Amount',
            //     //         text: 'The entered amount cannot be greater than the last net emolument.',
            //     //         confirmButtonText: 'OK',
            //     //         timer: 3000
            //     //     });
            //     //     document.getElementById('amount').value = ""; // Optionally clear the input
            //     // }
            // }

            return;
        }
    </script>


    {{-- ///////////////////////////////////// --}}

    <script type="text/javascript">
        $(document).ready(function() {
            // alert('danger')

            $('select[name="division"]').on('change', function() {
                var division_id = $(this).val();
                // alert(division_id)

                if (division_id) {
                    $.ajax({
                        url: "{{ url('/division/staff/ajax') }}/" + division_id,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {

                            var d = $('datalist[name="userSearch"]').html('');
                            $.each(data, function(key, value) {
                                $('datalist[name="userSearch"]').append(
                                    `<option value=${value.ID}>
                                ${value.fileNo} : ${value.surname}  ${value.first_name}  ${value.othernames}  </option>`
                                );
                            });
                        }
                    });
                } else {
                    alert('danger')
                }

            }); // end sub category

        });
    </script>
    {{-- ///////////////////////////////////// --}}
@stop
