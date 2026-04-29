@extends('layouts.layout')

@section('pageTitle')
    New Appointment
@endsection

@section('content')

    <div style="padding-bottom: 20px;">
        <div class="panel panel-default">
            <div class="panel-heading with-border hidden-print">
                <h3 class="panel-title">
                    <b>@yield('pageTitle')</b>
                    <i class="fa fa-arrow-right"></i>
                    <span id='processing'>
                        <strong><em>Approve Newly Employed Staff Salary.</em></strong>
                    </span>
                </h3>
            </div>

            @if (session('message'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span> </button>
                    <strong>Successful!</strong> {{ session('message') }}
                </div>
            @endif
            @if (session('error_message'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span> </button>
                    <strong>Error!</strong> {{ session('error_message') }}
                </div>
            @endif


            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                    </button>
                    <strong>Error!</strong>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="panel-body">


                <div class="panel panel-success" style="margin-top: 20px;">

                    <!-- Card Header -->
                    <div class="panel-heading text-center" style="font-size: 20px; font-weight: bold;">
                       <h3 class="panel-title">
                         New Employees
                       </h3>
                    </div>

                    <!-- Card Body -->
                    <div class="panel-body" style="padding: 15px;">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-highlight">
                                <thead>
                                    <tr style="background: #c7c7c7;">
                                        <th width="1%">S/N</th>
                                        <th>STAFF</th>
                                        <th>FILENO</th>
                                        <th>DATE OF ASSUMPTION</th>
                                        <th>MONTH</th>
                                        <th>YEAR</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>

                                @if ($staffForHalfPayList && count($staffForHalfPayList) > 0)
                                    @foreach ($staffForHalfPayList as $key => $b)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $b->surname }} {{ $b->first_name }} {{ $b->othernames }}</td>
                                            <td>{{ $b->fileNo }}</td>
                                            <td>{{ $b->due_date }}</td>
                                            <td>{{ $b->month_payment }}</td>
                                            <td>{{ $b->year_payment }}</td>
                                            <td>

                                                @if ($b->approvedBy == '')
                                                    <button type="button" class="btn btn-info btn-sm"
                                                        data-toggle="modal" data-backdrop="false"
                                                        data-target="#confirmEnable{{ $b->ID }}">
                                                        <i class="fa fa-btn fa-plus"></i> Approve
                                                    </button>
                                                @else
                                                    <span class="label label-success">Approved</span>
                                                @endif

                                                <!-- Modal -->
                                                <div class="modal fade" id="confirmEnable{{ $b->ID }}"
                                                    tabindex="-1" role="dialog">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                            <div class="modal-header bg-info">
                                                                <h4 class="modal-title text-white">Confirm!</h4>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>

                                                            <form method="POST"
                                                                action="{{ route('approveNewStaffSalary') }}">
                                                                @csrf

                                                                <div class="modal-body">
                                                                    <h4 class="text-center text-success">
                                                                        Are you sure you want to confirm the new employee
                                                                        {{ $b->surname }} {{ $b->first_name }}
                                                                        {{ $b->othernames }} for salary?
                                                                    </h4>

                                                                    <input type="hidden" name="staffId"
                                                                        value="{{ $b->staffid }}">

                                                                    <div class="row" style="margin-top: 20px;">

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Bank Name <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="bankName"
                                                                                    class="form-control" required>
                                                                                    <option value="">Select Bank
                                                                                    </option>
                                                                                    @foreach ($BankList as $bank)
                                                                                        <option
                                                                                            value="{{ $bank->bankID }}"
                                                                                            {{ $b->bankID == $bank->bankID ? 'selected' : '' }}>
                                                                                            {{ $bank->bank }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Account Number <span
                                                                                        class="text-danger">*</span></label>
                                                                                <input type="number" name="accountNumber"
                                                                                    class="form-control"
                                                                                    value="{{ $b->AccNo }}" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Bank Branch</label>
                                                                                <input type="text" name="bank_branch"
                                                                                    class="form-control"
                                                                                    value="{{ $b->bank_branch }}">
                                                                            </div>
                                                                        </div>

                                                                        {{-- <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Bank Group</label>
                                                                                <input type="number" name="bankGroup"
                                                                                    class="form-control"
                                                                                    value="{{ $b->bankGroup }}">
                                                                            </div>
                                                                        </div> --}}

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Employment Type <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="employmentType"
                                                                                    class="form-control" required>
                                                                                    <option value="">Select Type
                                                                                    </option>
                                                                                    @foreach ($employmentType as $type)
                                                                                        <option
                                                                                            value="{{ $type->id }}"
                                                                                            {{ $b->employee_type == $type->id ? 'selected' : '' }}>
                                                                                            {{ $type->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default"
                                                                        data-dismiss="modal">
                                                                        Cancel
                                                                    </button>
                                                                    <button type="submit" class="btn btn-primary">
                                                                        Confirm
                                                                    </button>
                                                                </div>

                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Modal -->

                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">No Records Found...</td>
                                    </tr>
                                @endif

                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

    <script type="text/javascript">
        function StaffSearchReload() {
            document.getElementById('fileNo').value = document.getElementById('userSearch').value;
            // alert(document.getElementById('userSearch').value);
            document.forms["mainform"].submit();
            // alert("jdjdjdeedd");
            return;
        }
    </script>

    <script type="text/javascript">
        $(function() {
            $("#dateofBirth").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#dueDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#incrementalDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>
@endsection
