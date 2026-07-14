@extends('layouts.layout')

@section('pageTitle')
    PERSONAL EMOLUMENT RECORDS
@endsection

@section('content')
    <div class="box box-default" style="border: none;">
        <div class="box-body box-profile" style="margin:0 5px;">
            <form class="form-horizontal" method="post" action="{{ url('/staff/personal-emolument/update') }}"
                enctype="multipart/form-data">
                {{ csrf_field() }}

                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="divisionID">Division</label>

                                @if (session('updateStatus') && session('updateStatus') == 1)
                                    <input type="hidden" name="" staffID="{{ session('emolumentStaffId') }}"
                                        id="updateStatus" value="1">
                                    <input type="hidden" name="" id="prevDivision"
                                        value="{{ session('emolumentDivision') }}">
                                @else
                                    <input type="hidden" name="" staffID="" id="updateStatus" value="0">
                                    <input type="hidden" name="" id="prevDivision" value="1">
                                @endif


                                @if (session('is_global') == 0)
                                    <input type="hidden" name="" id="statusChecker" value="0">
                                @else
                                    <input type="hidden" name="" id="statusChecker" value="1">
                                @endif

                                @if ($User->is_global == 1)
                                    <select class="form-control" id="divisions" name="division">
                                        {{-- <option selected disabled value="">Choose Division</option> --}}
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->divisionID }}">{{ $division->division }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" id="divisions2"
                                        value="{{ $userDivision[0]->division }}"
                                        divisionId={{ $userDivision[0]->divisionID }} readonly>
                                @endif
                            </div>


                            <div class="col-md-6 mb-3">
                                <label for="divisionID">Staff Name Search</label>
                                <input type="hidden" id="findID" value="" name="findID">

                                <input type="text" id="getStaff" name="getStaff" autocomplete="off" list="enrolledUsers"
                                    class="form-control getStaff" value="{{ old('getStaff') }}">
                                <datalist id="enrolledUsers">

                                </datalist>

                                <div>
                                    <small id="noUserMsg" style="color: red" hidden>No User Found</small>
                                    <small id="userMsg" style="color: green" hidden></small>
                                </div>
                            </div>


                            <div class="col-md-12 mb-3">
                                <h3 class="box-title"> <span class="text-center" id='processing'></span></h3>
                            </div>
                        </div>

                    </div>
                </div>

                <hr style="border: 2px solid #00A65A">

                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px">
                        <div class="row">
                            <div class="col-md-8 mb-3">

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">TO ALL STAFF,</h5>
                                        <p class="card-text">
                                            ISALU HOSPITAL LIMITED
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row" style="background-color: #00A65A; color: #ffffff; text-transform:uppercase">
                            <div class="col-md-12 mb-3">
                                <h4 class="text- text-center">
                                    <strong>PERSONAL EMOLUMENT RECORDS FOR {{ date('Y') }}</strong>
                                </h4>
                            </div>
                        </div>
                    </div>


                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <div style="word-break: break-all;">
                                    In order to comply with the Accountant-General instruction and to maintain correct and
                                    comprehensive
                                    record of all staff of the Council, <br /> you are requested to complete this Form and
                                    return same to
                                    the Salary Section before &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input class="form-control1" type="text" name="returnBefore" id="returnBefore"
                                        style="border: none; font-weight: bold;"
                                        placeholder="----E.g 25th November, 2017----">

                                    <br>
                                    <ol start="2">
                                        <li>
                                            Failure to return the Form on time may give rise to omitting your name from
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="text" name="failureReturn" id="failureReturn"
                                                style="border: none; font-weight: bold;"
                                                placeholder="----E.g 25th November, 2017----">
                                            Salary Pay Roll
                                        </li>
                                        <li>
                                            Submit along with this Form, one recent passport photograph.
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            <div class="col-md-2 mb-3">

                            </div>
                            <div class="col-md-3 mb-3">

                                <div class="card">
                                    <div class="card-body">
                                        <img style="width: 150px; height:150px" src="{{ asset('default.png') }}"
                                            id="displayPass" height="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <hr style="border: 1px solid #00A65A">


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fileNo">File Number</label>
                                <input type="text" name="fileNo" id="fileNo" value="{{ old('fileNo') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="surname">Surname</label>
                                <input type="text" name="surname" id="surname" value="{{ old('surname') }}"
                                    class="form-control" placeholder="Surname">
                            </div>


                            <div class="col-md-4 mb-3">
                                <label for="firstName">Firstname</label>
                                <input type="text" name="firstName" id="firstName" value="{{ old('firstName') }}"
                                    required class="form-control" placeholder="First Name">
                            </div>
                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="otherNames">Other Names</label>
                                <input type="text" name="otherNames" id="otherNames" value="{{ old('otherNames') }}"
                                    class="form-control" placeholder="Other Names">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" class="form-control" required>
                                    <option value="">Choose Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>


                            <div class="col-md-4 mb-3">
                                <label for="dateOfBirth">Date of birth</label>
                                <input type="text" name="dateOfBirth" id="dateOfBirth"
                                    value="{{ old('dateOfBirth') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="mstatus">Marital Status</label>
                                <select name="mstatus" id="mstatus" class="form-control">
                                    <option value="">-Select Status-</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Widowed">Widowed</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="altemail">Alternative email</label>
                                <input type="email" name="altemail" id="altemail" value="{{ old('altemail') }}"
                                    class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="phoneNumber">Phone No</label>
                                <input type="text" name="phoneNumber" id="phoneNumber"
                                    value="{{ old('phoneNumber') }}" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="altphoneno">Alternative Phone No</label>
                                <input type="text" name="altphoneno" id="altphoneno" value="{{ old('altphoneno') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="residentialAddress">Residential Address</label>
                                <textarea rows="1" cols="1" name="residentialAddress" id="residentialAddress" class="form-control">{{ old('residentialAddress') }} {{ Session::get('lg') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="state">State</label>
                                <select name="state" id="state" class="form-control">
                                    <option value="">Select a State</option>
                                    @foreach ($statelist as $b)
                                        <option value="{{ $b->StateID }}">{{ $b->State }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-4 mb-3">
                                <label for="lga">LGA</label>
                                <select name="lga" id="lga" class="form-control">
                                    <option value="">-Select Status-</option>

                                </select>
                            </div>


                            <div class="col-md-4 mb-3">
                                <label for="gpz">Geo-political Zone</label>
                                <select name="gpz" id="gpz" class="form-control">
                                    <option value="">-Select Status-</option>
                                    <option value="North Central">North Central</option>
                                    <option value="North East">North East</option>
                                    <option value="North West">North West</option>
                                    <option value="South East">South East</option>
                                    <option value="South South">South South</option>
                                    <option value="South West">South West</option>

                                </select>
                            </div>


                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="currentstate">Current Residential State</label>
                                <select name="currentstate" id="currentstate" class="form-control">
                                    <option value="">Select a State</option>
                                    @foreach ($currentState as $b)
                                        <option value="{{ $b->id }}">{{ $b->state }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="religion">Religion</label>
                                <select name="religion" id="religion" class="form-control">
                                    <option value="">-Select Religion-</option>
                                    <option value="Christianity">Christianity</option>
                                    <option value="Islam">Islamic</option>
                                    <option value="Traditional Practice">Traditional Practice</option>
                                    <option value="None">None</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="qurter">Home Town Address</label>
                                <input type="text" name="qurter" id="qurter" value="{{ old('qurter') }}"
                                    class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="challenge">Physically Challenged</label>
                                <select name="challenge" id="challenge" class="form-control">
                                    <option value="Normal">Normal</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="challengedetails">State if Any</label>
                                <textarea rows="1" cols="1" name="challengedetails" id="challengedetails" class="form-control">{{ old('challengedetails') }}</textarea>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="leaveAddress">Leave Address</label>
                                <textarea rows="1" cols="1" name="leaveAddress" id="leaveAddress" class="form-control">{{ old('leaveAddress') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            {{-- <div class="col-md-4 mb-3">
                                <label for="grade">Grade Level </label>
                                <input type="text" name="grade" id="grade" value="{{ old('grade') }}"
                                    class="form-control" required>
                            </div> --}}

                            <div class="col-md-4 mb-3">
                                <label for="grade">Grade Level</label>
                                <select name="grade" id="grade" class="form-control" required>
                                    <option value="">-- Select Grade Level --</option>
                                    @for ($i = 1; $i <= 17; $i++)
                                        @if ($i == 11)
                                            @continue
                                        @endif
                                        <option value="{{ $i }}" {{ old('grade') == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>


                            {{-- <div class="col-md-4 mb-3">
                                <label for="step">Step</label>
                                <input type="number" name="step" id="step" value="{{ old('grade') }}"
                                    class="form-control" required>
                            </div> --}}

                            <div class="col-md-4 mb-3">
                                <label for="step">Step</label>
                                <select name="step" id="step" class="form-control" required>
                                    <option value="">-- Select Step --</option>
                                    @for ($i = 1; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ old('step') == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>


                            <div class="col-md-4 mb-3">
                                <label for="hrEmploymentType">Employment Type</label>
                                <select name="hrEmploymentType" id="hrEmploymentType" class="form-control">
                                    <option value="">Select a State</option>
                                    @foreach ($hrEmploymentType as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="col-md-4 mb-3">
                                <label for="divi">Division</label>
                                <input type="text" name="divi" id="divi" value="" class="form-control"
                                    readonly>
                                <input type="hidden" name="diviID" id="diviID" value="">
                            </div> --}}

                        </div>
                    </div>
                </div>

                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">



                            {{-- <div class="col-md-4 mb-3">
                                <label for="section">Department</label>
                                <select name="section" id="section" class="form-control">
                                    <option value="">Select a Department</option>
                                    @foreach ($department as $list)
                                        <option value="{{ $list->id }}">{{ $list->department }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="designation">Designation</label>
                                <select name="designation" id="designation" class="form-control">
                                    <option value="">Select Designation</option>
                                    @foreach ($desig as $list)
                                        <option value="{{ $list->id }}">{{ $list->designation }}</option>
                                    @endforeach

                                </select>
                            </div> --}}
                            <div class="col-md-4 mb-3">
                                <label for="section">Department</label>
                                <select name="section" id="section" class="form-control">
                                    <option value="">Select a Department</option>
                                    @foreach ($department as $list)
                                        <option value="{{ $list->id }}">{{ $list->department }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="designation">Designation</label>
                                <select name="designation" id="designation" class="form-control">
                                    <option value="">Select Designation</option>


                                </select>
                            </div>





                            <div class="col-md-4 mb-3">
                                <label for="appointmentfirst">Date of First Appointment</label>
                                <input type="text" name="appointmentfirst" id="appointmentfirst"
                                    value="{{ old('appointmentfirst') }}" class="form-control" readonly>
                            </div>

                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="appointmentDate">Date of Present Appointment</label>
                                <input type="text" name="appointmentDate" id="appointmentDate"
                                    value="{{ old('appointmentDate') }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="confirmationDate">Date of Confirmation</label>
                                <input type="text" name="confirmationDate" id="confirmationDate"
                                    value="{{ old('confirmationDate') }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="resumptionDate">Resumption Date</label>
                                <input type="text" name="resumptionDate" id="resumptionDate"
                                    value="{{ old('resumptionDate') }}" class="form-control" readonly>
                            </div>

                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="lastPromotionDate">Last Promotion Date</label>
                                <input type="text" name="lastPromotionDate" id="lastPromotionDate"
                                    value="{{ old('lastPromotionDate') }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="incrementalDate">Incremental Date</label>
                                <input type="text" name="incrementalDate" id="incrementalDate"
                                    value="{{ old('incrementalDate') }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="bank">Bank</label>
                                <select name="bank" id="bank" class="form-control" required>
                                    <option value="">Select a Bank</option>
                                    @foreach ($getBank as $bank)
                                        <option value="{{ $bank->bankID }}">{{ $bank->bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="branch">Bank Branch</label>
                                <input type="text" name="branch" id="branch" value="{{ old('branch') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="bankGroup">Bank Group</label>
                                <input type="text" name="bankGroup" id="bankGroup" value="{{ old('bankGroup') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="accountNo">Account Number </label>
                                <input type="text" name="accountNo" id="accountNo" value="{{ old('accountNo') }}"
                                    class="form-control" required>
                            </div>

                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="nhfNo">NHF Number</label>
                                <input name="nhfNo" id="nhfNo" class="form-control" value="{{ old('nhfNo') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="empType">Employment Type Category</label>
                                <select name="employeeType" class="form-control" id="empType">
                                    <option value="">Select</option>
                                    @foreach ($employeeType as $list)
                                        <option value="{{ $list->id }}">{{ $list->employmentType }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="passport">Passport </label>
                                        <input type="file" name="photo" id="passport" class="form-control" />
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="passport" style="visibility:hidden">Passport </label> <br>
                                        <a class="btn btn-success"
                                            style="background-color: #00A65A; color: #ffffff; font-weight:500"
                                            id="clear">
                                            Reset Image</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div style="margin: 0px  5%;">
                    <div class="row">
                        <div class="col-md-12 mb-3 mt-5">
                            <div class="form-group">
                                <input type="checkbox" name="certify" checked disabled>
                                <label for="certify">
                                    I hereby certify that the particulars given above are correct to the best of my
                                    knowledge.
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <hr style="border: 1px solid #00A65A">
                <div style="margin: 0px  5%;">

                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-4 mb-3"> </div>
                            <div class="col-md-4 mb-3"> </div>
                            <div class="col-md-4 mb-3">
                                <table>
                                    <tr>
                                        <td align="right">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="right"> ........................................................
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">Signature</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <br>
                <div style="margin: 0px  5%;">

                    <div class="form-group" style="margin-bottom: 10px;">
                        <div class="row">
                            <div class="col-12  mb-3">

                                <button type="button" id="saveUpdate" data-toggle="modal" data-target="#confirmUpdate"
                                    class="btn btn-success btn-block w-100"
                                    style="background-color: #00A65A; color: #ffffff; font-weight:500"> <i
                                        class="fa fa-save"></i>
                                    Save/Update</button>
                            </div>
                        </div>
                    </div>

                </div>


                <!-- Modal Dialog for UPDATE RECORD-->
                <div class="modal fade" id="confirmUpdate" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                                <h4 class="modal-title">PERSONAL EMOLUMENT RECORDS FOR {{ date('Y') }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to perform this operation?
                                    <br />
                                    &nbsp; <b>Continue</b> - this will save/update your record <br />
                                    &nbsp; <b>Cancel</b> - this will return you back to the same page
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                <input type="submit" name="button" class="btn btn-success" value="Continue">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- //Modal Dialog -->
            </form>
        </div>
    </div>
@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <style>
        #bank {
            /*cursor:no-drop;*/
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show validation errors in SweetAlert
            @if (count($errors) > 0)
                Swal.fire({
                    title: "Error!",
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: "error",
                    confirmButtonText: "OK",
                });
            @endif

            // Show success message in SweetAlert
            @if (session('msg'))
                Swal.fire({
                    title: "Success!",
                    text: "{{ session('msg') }}",
                    icon: "success",
                    confirmButtonText: "OK",
                });
            @endif

            // Show operation error in SweetAlert
            @if (session('err'))
                Swal.fire({
                    title: "Operation Error!",
                    text: "{{ session('err') }}",
                    icon: "warning",
                    confirmButtonText: "OK",
                });
            @endif
        });
    </script>
    <script type="text/javascript">
        var defaultImage = "{{ asset('default.png') }}";
        if (fileNo == "") {
            $('#saveUpdate').attr("disabled", true);
        } else {
            $('#saveUpdate').attr("disabled", false);
        }

        (function() {

            $('#getStaff').change(function() {
                var fileNo = $('#getStaff').val();
                // var fileNo = $('#getStaff option:selected').attr('value');
                // alert(fileNo);
                $('#processing').text('Processing. Please wait...');
                $.ajax({
                    url: murl + '/personal-emolument/findStaff',
                    type: "post",
                    data: {
                        'getStaff': $('#getStaff').val(),
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log({
                            data
                        });

                        // let profilePicture = data.picture ? data.picture : defaultImage;
                        let profilePicture = data.passport_url ? data.passport_url : defaultImage;

                        $('#saveUpdate').attr("disabled", false);
                        $('#processing').text('');
                        $('#surname').val(data.surname);
                        $('#fileNo').val(data.fileNo);
                        $('#firstName').val(data.first_name);
                        $('#otherNames').val(data.othernames);
                        $('#getStaff').val(data.ID);
                        $('#findID').val(data.ID);


                        $('#grade').val(data.level);
                        $('#step').val(data.step);

                        $('#division').val(data.division);

                        $('#state').val(data.stateID).prop("selected", true);

                        // $('#state').val(data.stateID);
                        //$('#lga').val(data.lgaID);
                        //var lgadormmy=data.lgaID;
                        $('#bank').append('<option value="' + data.bankID + '" selected>' + data
                            .bankname + '</option>');
                        $('#branch').val(data.bank_branch);
                        $('#accountNo').val(data.AccNo);
                        $('#bankGroup').val(data.bankGroup);
                        $('#section').append('<option value="' + data.deptID + '" selected>' + data
                            .depart + '</option>');
                        // Load all designations for the selected department
                        $.ajax({
                            url: murl + '/get-designations/' + data.deptID,
                            type: "GET",
                            success: function(designations) {

                                // Clear existing options
                                $('#designation')
                                    .empty()
                                    .append(
                                        '<option value="">Select Designation</option>');

                                // Append all designations alphabetically
                                $.each(designations, function(index, item) {
                                    $('#designation').append(
                                        '<option value="' + item.id + '">' +
                                        item.designation + '</option>'
                                    );
                                });

                                // Auto-select the staff’s own designation
                                $('#designation').val(data.desigID).prop("selected",
                                    true);
                            }
                        });

                        $('#appointmentfirst').val(data.appointment_date);
                        $('#appointmentDate').val(data.date_present_appointment);
                        $('#confirmationDate').val(data.date_of_confirmation);
                        $('#resumptionDate').val(data.resumption_date);
                        $('#incrementalDate').val(data.incremental_date);
                        $('#lastPromotionDate').val(data.last_promotion_date);
                        $('#dateOfBirth').val(data.dob);
                        $('#residentialAddress').val(data.home_address);
                        $('#qurter').val(data.government_qtr);
                        $('#phoneNumber').val(data.phone);
                        $('#altphoneno').val(data.alternate_phone);
                        $('#email').val(data.email);
                        $('#altemail').val(data.alternate_email);
                        $('#leaveAddress').val(data.leaveaddress);
                        $('#religion').val(data.religion);
                        $('#gender').val(data.gender);
                        $('#hrEmploymentType').val(data.hremploymentType).prop("selected", true);
                        $('#mstatus').val(data.maritalstatus);
                        $('#nhfNo').val(data.nhfNo);
                        $('#gpz').val(data.gpz);
                        $('#divi').val(data.division);
                        $('#diviID').val(data.divisionID);
                        $('#controlNo').val(data.control_no);
                        $('#challenge').val(data.challengestatus);
                        $('#challengedetails').val(data.challengedetails);
                        // $('#displayPass').attr('src', murl + '/passport/' + data.picture + '');
                        $('#displayPass').attr('src', profilePicture);

                        // $('#designation').append('<option value="' + data.designationID +
                        //     '" selected>' +
                        //     data.Designation + '</option>');

                        // $('#designation').val(data.desigID).prop("selected", true);
                        $('#empType').val(data.empID).prop("selected", true);

                        // Populate designation dynamically
                        $('#designation').empty().append(
                            '<option value="">Select Designation</option>');
                        if (data.desigID && data.designation) {
                            $('#designation').append('<option value="' + data.desigID +
                                '" selected>' + data.designation + '</option>');
                        }

                        // $('#empType').append('<option value="' + data.empID + '" selected>' + data
                        //     .employmentType + '</option>');
                        document.getElementById('currentstate').value = data.current_state;

                    }


                })
            });
        })();
        ////////////////////////////////////////////////////////
        $(function() {
            $("#appointmentDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#incrementalDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#dateOfBirth").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#appointmentfirst").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#confirmationDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#lastPromotionDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#resumptionDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#returnBefore").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#failureReturn").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function() {

            $.ajax({
                url: murl + '/personal-emolument/division/staffs',
                type: "post",
                data: {
                    'divisionID': 1,
                    '_token': $('input[name=_token]').val()
                },
                success: function(data) {
                    // location.reload(true);
                    // console.log(data);

                    if (data.length == 0) {
                        $("#noUserMsg").show();
                        $("#userMsg").hide();
                    } else {
                        $("#noUserMsg").hide();
                        $("#userMsg").show().html(`${data.length} staff found!!`);

                        $.each(data, function(i, option) {
                            let displayFormat = option.fileNo + " : " + option
                                .surname + " " + option.first_name + " " + option
                                .othernames;

                            $("#enrolledUsers").append($("<option>", {
                                value: option.ID,
                                text: displayFormat
                            }))
                        });
                    }

                }
            });

            // Retrieve some values
            let updateStatus = $("#updateStatus").val();
            let staffId = $("#updateStatus").attr('staffId');
            let prevDivision = $("#prevDivision").val();
            console.log("prevDivision", prevDivision)

            // If an update was made in the previous request
            // and updateStatus is one, an ajax request will be made
            // to retrieve the previous user using the user ID saved in a flash session
            if (updateStatus == 1) {

                // Preselect the value for selected division with the previous
                $("#divisions").val(prevDivision);
                $("#divisions").change()

                $("#enrolledUsers").empty();
                $("#getStaff").val('');

                // Sends an ajax request to retrieve the staff under the choosen division after updating a staff
                $.ajax({
                    url: murl + '/personal-emolument/division/staffs',
                    type: "post",
                    data: {
                        'divisionID': prevDivision,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        // location.reload(true);
                        // console.log(data);

                        if (data.length == 0) {
                            $("#noUserMsg").show();
                            $("#userMsg").hide();
                        } else {
                            $("#noUserMsg").hide();
                            $("#userMsg").show().html(`${data.length} staff found!!`);

                            $.each(data, function(i, option) {
                                let displayFormat = option.fileNo + " : " + option
                                    .surname + " " + option.first_name + " " + option
                                    .othernames;

                                $("#enrolledUsers").append($("<option>", {
                                    value: option.ID,
                                    text: displayFormat
                                }))
                            });
                        }

                    }
                });

                // Sends an ajax to recollect selected staff info again after updating
                $.ajax({
                    url: murl + '/personal-emolument/findStaffAfterUpdate',
                    type: "post",
                    data: {
                        'staffID': staffId,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log(data);
                        $('#saveUpdate').attr("disabled", false);
                        $('#processing').text('');
                        $('#surname').val(data.surname);
                        $('#fileNo').val(data.fileNo);
                        $('#firstName').val(data.first_name);
                        $('#otherNames').val(data.othernames);
                        $('#getStaff').val(data.ID);




                        $('#grade').val(data.level);
                        $('#step').val(data.step);

                        $('#division').val(data.division);
                        $('#state').val(data.stateID);
                        //$('#lga').val(data.lgaID);
                        //var lgadormmy=data.lgaID;
                        $('#bank').append('<option value="' + data.bankID + '" selected>' + data
                            .bankname + '</option>');
                        $('#branch').val(data.bank_branch);
                        $('#accountNo').val(data.AccNo);
                        $('#section').append('<option value="' + data.deptID + '" selected>' + data
                            .depart + '</option>');
                        $('#appointmentfirst').val(data.appointment_date);
                        $('#appointmentDate').val(data.date_present_appointment);
                        $('#confirmationDate').val(data.date_of_confirmation);
                        $('#resumptionDate').val(data.resumption_date);
                        $('#incrementalDate').val(data.incremental_date);
                        $('#lastPromotionDate').val(data.last_promotion_date);
                        $('#dateOfBirth').val(data.dob);
                        $('#residentialAddress').val(data.home_address);
                        $('#qurter').val(data.government_qtr);
                        $('#phoneNumber').val(data.phone);
                        $('#altphoneno').val(data.alternate_phone);
                        $('#email').val(data.email);
                        $('#altemail').val(data.alternate_email);
                        $('#leaveAddress').val(data.leaveaddress);
                        $('#religion').val(data.religion);
                        $('#gender').val(data.gender);
                        $('#hrEmploymentType').val(data.hremploymentType).prop("selected", true);
                        $('#mstatus').val(data.maritalstatus);
                        $('#nhfNo').val(data.nhfNo);
                        $('#gpz').val(data.gpz);
                        $('#divi').val(data.division);
                        $('#diviID').val(data.divisionID);
                        $('#controlNo').val(data.control_no);
                        $('#challenge').val(data.challengestatus);
                        $('#challengedetails').val(data.challengedetails);
                        $('#displayPass').attr('src', murl + '/passport/' + data.picture + '');
                        $('#designation').append('<option value="' + data.desigID + '" selected>' +
                            data.designation + '</option>');
                        $('#empType').append('<option value="' + data.empID + '" selected>' + data
                            .employmentType + '</option>');
                        document.getElementById('currentstate').value = data.current_state;


                    }


                })
            }

            // When a user is not global
            // his/her division appears on the division input
            // a request with the divisionID is sent to retrieve staffs under that division
            let statusChecker = $("#statusChecker").val()
            let division2 = $("#divisions2").attr("divisionID");

            if (statusChecker == 0) {
                $.ajax({
                    url: murl + '/personal-emolument/division/staffs',
                    type: "post",
                    data: {
                        'divisionID': division2,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        // location.reload(true);
                        // console.log(data);

                        if (data.length == 0) {
                            $("#noUserMsg").show();
                            $("#userMsg").hide();
                        } else {
                            $("#noUserMsg").hide();
                            $("#userMsg").show().html(`${data.length} staff found!!`);

                            $.each(data, function(i, option) {
                                let displayFormat = option.fileNo + " : " + option
                                    .surname + " " + option.first_name + " " + option
                                    .othernames;

                                $("#enrolledUsers").append($("<option>", {
                                    value: option.ID,
                                    text: displayFormat
                                }))
                            });
                        }

                    }
                });
            }

            // When a division is picked
            // Sends an ajax request and returns the staff
            $('#divisions').change(function(e) {
                e.preventDefault();

                $("#enrolledUsers").empty();
                $("#getStaff").val('');

                var division = $(this).val();

                $.ajax({
                    url: murl + '/personal-emolument/division/staffs',
                    type: "post",
                    data: {
                        'divisionID': division,
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        // location.reload(true);
                        // console.log(data);

                        if (data.length == 0) {
                            $("#noUserMsg").show();
                            $("#userMsg").hide();
                        } else {
                            $("#noUserMsg").hide();
                            $("#userMsg").show().html(`${data.length} staff found!!`);

                            $.each(data, function(i, option) {
                                let displayFormat = option.fileNo + " : " + option
                                    .surname + " " + option.first_name + " " + option
                                    .othernames;

                                $("#enrolledUsers").append($("<option>", {
                                    value: option.ID,
                                    text: displayFormat
                                }))
                            });
                        }

                    }
                });
            });
        });

        (function() {
            $('#state').change(function() {

                //alert(fileNo);
                $('#processing').text('Processing. Please wait...');
                $.ajax({
                    url: murl + '/personal-emolument/get-lga',
                    type: "post",
                    data: {
                        'stateId': $('#state').val(),
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log(data);
                        $('#lga').empty();
                        $.each(data, function(index, obj) {
                            $('#lga').append('<option value="' + obj.lgaId + '">' + obj
                                .lga + '</option>');
                        });

                        $('#processing').text('');
                    }
                })
            });
        })();




        (function() {
            $('.getStaff').change(function() {
                var fileNo = $('.getStaff').val();
                //alert(fileNo);
                $('#processing').text('Processing. Please wait...');
                $.ajax({
                    url: murl + '/collect/staff-detail',
                    type: "post",
                    data: {
                        'staffId': $('.getStaff').val(),
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(datas) {
                        console.log(datas);


                        $.each(datas, function(index, obj) {

                            $('#lga').append('<option value="' + obj.lgaId + '" >' + obj
                                .lga + '</option>');
                        });

                        $('#processing').text('');
                    }
                })
            });
        })();


        //appen lga selected value
        (function() {
            $('.getStaff').change(function() {
                var fileNo = $('.getStaff').val();
                //alert(fileNo);
                $('#processing').text('Processing. Please wait...');
                $.ajax({
                    url: murl + '/collect/append',
                    type: "post",
                    data: {
                        'staffId': $('.getStaff').val(),
                        '_token': $('input[name=_token]').val()
                    },
                    success: function(data) {
                        console.log(data);


                        $('#lga').append('<option value="' + data.lgaId + '" selected >' + data
                            .lga + '</option>');


                    }
                })
            });
        })();

        //image display
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#displayPass').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#passport").change(function() {
            readURL(this);
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#clear").click(function() {
                $("#passport").val("");
            });
        });
    </script>
    {{-- <script>
        $(document).ready(function() {
            $('#bank').css('pointer-events', 'none');
        });
    </script> --}}

    <script type="text/javascript">
        $('#section').on('change', function() {
            let dept_id = $(this).val();
            $('#designation').html('<option value="">Loading...</option>');

            if (dept_id) {
                $.ajax({
                    url: '/get-designations/' + dept_id,
                    type: 'GET',
                    success: function(data) {
                        $('#designation').empty();
                        $('#designation').append('<option value="">Select Designation</option>');
                        data.forEach(function(item) {
                            $('#designation').append('<option value="' + item.id + '">' + item
                                .designation + '</option>');
                        });
                    }
                });
            } else {
                $('#designation').html('<option value="">Select Designation</option>');
            }
        });
    </script>



@stop
