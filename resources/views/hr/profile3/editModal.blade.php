<!--profile picture update-->
<div id="editProfilePic" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Profile Picture Update</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/picture-update') }}" method="post" role="form"
                enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">


                        <div class="row">
                            <div class="colm-4"><input type="hidden" class="form-control" id="PfileID" name="fileNo"
                                    value=""></div>
                            <div class="colm-4"><label>Profile picture: </label>
                                <div class="form-group">
                                    <input type="file" name="filename" required id="control"
                                        onchange="EnableField1()">
                                    <br>
                                </div>


                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="refreshPage" type="submit" class="btn btnuccess btn-xs">Update</button>
                        <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                    </div>

            </form>
        </div>

    </div>
</div>
</div>

<!--bio data update-->
<div id="editBIO" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Biodata Profile</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update') }}" method="post" role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="fileID" name="fileID" value="">

                        <div class="row">
                            <div class="colm-4"><label>FileNo: </label><input type="text" class="form-control"
                                    id="fileNo" name="fileNo" value=""></div>
                            <div class="colm-4"><label>Division: </label>
                                <select required class="form-control" id="divs" name="division">
                                    @foreach ($getDivision as $list)
                                        <option value="{{ $list->divisionID }}"
                                            {{ old('division') == $list->divisionID ? 'selected' : '' }}>
                                            {{ $list->division }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="colm-4"><label>Title: </label>
                                <select required class="form-control" id="titles" name="title">
                                    @foreach ($getTitles as $list)
                                        <option value="{{ $list->ID }}"
                                            {{ old('title') == $list->ID ? 'selected' : '' }}>{{ $list->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="row">
                            <div class="colm-4"><label>SurName: </label><input type="text" class="form-control"
                                    id="surname" name="surname" value=""></div>
                            <div class="colm-4"><label>First Name: </label><input type="text" class="form-control"
                                    id="firstname" name="firstname" value=""></div>
                            <div class="colm-4"><label>Other Name </label><input type="text" class="form-control"
                                    id="othernames" name="othernames" value=""></div>
                        </div>
                        <div class="row"> <label>Home Address: </label><input type="text" class="form-control"
                                id="address" name="address" value=""></div>
                        <div class="row">
                            <div class="colm-4"><label>Gender: </label>
                                <select required class="form-control" id="gender" name="gender">
                                    @foreach ($getGender as $list)
                                        <option value="{{ $list->ID }}"
                                            {{ old('gender') == $list->ID ? 'selected' : '' }}>{{ $list->gender }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="colm-4"><label>State: </label>
                                <select required class="form-control" id="currentstate" name="currentstate">
                                    @foreach ($getState as $list)
                                        <option value="{{ $list->StateID }}"
                                            {{ old('currentstate') == $list->StateID ? 'selected' : '' }}>
                                            {{ $list->State }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="colm-4"><label>Phone: </label><input type="text" class="form-control"
                                    id="phone" name="phone" value=""></div>
                        </div>

                        <div class="row">
                            <div class="colm-4"><label>Nationality: </label><input type="text"
                                    class="form-control" id="nationality" name="nationality" value=""></div>
                            <div class="colm-4"><label>Staff Status: </label><input type="text"
                                    class="form-control" id="status" name="status" value=""></div>
                            <div class="colm-4"></div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPage" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>

    </div>
</div>

<!--educational qualification update -->
<div id="editEDU" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Educational Qualifications</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-education') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="eduID" name="eduID" value="">
                        <input type="hidden" class="form-control" id="fID" name="staffID" value="">

                        <div class="row">

                            <div class="colm-6"><label>Degree & Professional Qualification: </label><input
                                    type="text" class="form-control" id="degreequalification" name="degree"
                                    value=""></div>
                            <div class="colm-6"><label>School Attended: </label><input type="text"
                                    class="form-control" id="schoolattended" name="schoolattended" value="">
                            </div>

                        </div>
                        <div class="row">

                            <div class="colm-4"><label>From: </label><input type="text" class="form-control"
                                    id="schoolfrom" name="from" value="" readonly></div>
                            <div class="colm-4"><label>To: </label><input type="text" class="form-control"
                                    id="schoolto" name="to" value="" readonly></div>
                            <div class="colm-4"><label>Certificate Held: </label><input type="text"
                                    class="form-control" id="certificateheld" name="certificate" value="">
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--language update -->
<div id="editLANGUAGE" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Languages</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-language') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="langID" name="langID" value="">
                        <input type="hidden" class="form-control" id="stID" name="staffID" value="">

                        <div class="row">

                            <div class="colm-6"><label>Language: </label><input type="text" class="form-control"
                                    id="language" name="language" value=""></div>
                            <div class="colm-6"><label>Spoken: </label><input type="text" class="form-control"
                                    id="spoken" name="spoken" value=""></div>

                        </div>
                        <div class="row">

                            <div class="colm-4"><label>Written: </label><input type="text" class="form-control"
                                    id="written" name="written" value=""></div>
                            <div class="colm-4"><label>Exam, Qualified: </label><input type="text"
                                    class="form-control" id="exam_qualified" name="exam_qualified" value="">
                            </div>
                            <div class="colm-4"><label>Checked By: </label><input type="text" class="form-control"
                                    id="checkedby" name="checkedby" value=""></div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--children particulars update -->
<div id="editCHILDREN" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of children</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-children') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="recordID" name="recordID" value="">
                        <input type="hidden" class="form-control" id="parentID" name="parentID" value="">

                        <div class="row">

                            <div class="colm-6"><label>Fullname: </label><input type="text" class="form-control"
                                    id="fullname" name="fullname" value=""></div>
                            <div class="colm-6"><label>Gender: </label>
                                <select required class="form-control" id="gender2" name="gender2">
                                    @foreach ($getGender as $list)
                                        <option value="{{ $list->ID }}"
                                            {{ old('gender2') == $list->ID ? 'selected' : '' }}>{{ $list->gender }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Date of birth: </label><input type="text"
                                    class="form-control date-field" id="dateofbirth" name="dateofbirth"
                                    value=""></div>
                            <div class="colm-6"><label>Checked By </label><input type="text" class="form-control"
                                    id="checked_children_particulars" name="checked_children_particulars"
                                    value=""></div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--particulars of wife -->
<div id="editWIFE" class="modal fade">
    <div class="modal-dialog modalm box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of wife</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-wife') }}" method="post" role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="wifeID" name="wifeID" value="">
                        <input type="hidden" class="form-control" id="husbandID" name="husbandID" value="">

                        <div class="row">
                            <div class="colm-12"><label>Wife Name: </label><input type="text" class="form-control"
                                    id="wifename" name="wifefullname" value=""></div>
                        </div>

                        <div class="row">
                            <div class="colm-12"><label>Date of birth: </label><input type="text"
                                    class="form-control date-field" id="wifedob" name="wifedob" value=""
                                    readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="colm-12"><label>Date of marriage: </label><input type="text"
                                    class="form-control date-field" id="marriagedate" name="marriagedate"
                                    value="" readonly></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--next of kin particulars update -->
<div id="editNOK" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Next of kin</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-nok') }}" method="post" role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="nokID" name="nokID" value="">
                        <input type="hidden" class="form-control" id="nokparentID" name="nokparentID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Fullname: </label><input type="text" class="form-control"
                                    id="nokfullname" name="nokfullname" value=""></div>
                            <div class="colm-6"><label>Address: </label><input type="text" class="form-control"
                                    id="nokaddress" name="nokaddress" value=""></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Relationship: </label><input type="text"
                                    class="form-control" id="nokrelationship" name="nokrelationship" value="">
                            </div>
                            <div class="colm-6"><label>Phone No: </label><input type="text" class="form-control"
                                    id="nokphoneno" name="nokphoneno" value=""></div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--previous service update -->
<div id="editPRECIOUSSERVICE" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Previous service</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-previous-service') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="serviceID" name="serviceID" value="">
                        <input type="hidden" class="form-control" id="userID" name="userID" value="">

                        <div class="row">

                            <div class="colm-6"><label>Previous Employer: </label><input type="text"
                                    class="form-control" id="preemployer" name="preemployer" value=""></div>
                            <div class="colm-6"><label>From: </label><input type="text" class="form-control"
                                    id="prefrom" name="prefrom" value="" readonly></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>To: </label><input type="text" class="form-control"
                                    id="preto" name="preto" value="" readonly></div>
                            <div class="colm-6"><label>Previous Pay: </label><input type="text"
                                    class="form-control" id="prepay" name="prepay" value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>File Page Reference: </label><input type="text"
                                    class="form-control" id="prefileref" name="prefileref" value=""></div>
                            <div class="colm-6"><label>Checked By: </label><input type="text" class="form-control"
                                    id="precheckedby" name="precheckedby" value=""></div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--censors and commendation update -->
<div id="editCENSORSANDCOMMENDATION" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Censors and Commendations</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-censors-commendations') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="censorID" name="censorID" value="">
                        <input type="hidden" class="form-control" id="censorUserID" name="censorUserID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Leave Type: </label><input type="text" class="form-control"
                                    id="leavetype" name="leavetype" value=""></div>
                            <div class="colm-6"><label>From: </label><input type="text" class="form-control"
                                    id="leavefrom" name="leavefrom" value="" readonly></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>To: </label><input type="text" class="form-control"
                                    id="leaveto" name="leaveto" value="" readonly></div>
                            <div class="colm-6"><label>No.of days: </label><input type="text" class="form-control"
                                    id="numberdate" name="numberOfDays" value=""></div>

                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Date: </label><input type="text" class="form-control"
                                    id="commendationdate" name="commendationdate" value="" readonly></div>
                            <div class="colm-6"><label>File Page Reference: </label><input type="text"
                                    class="form-control" id="censorfileref" name="censorfileref" value="">
                            </div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Summary: </label><input type="text" class="form-control"
                                    id="summary" name="summary" value=""></div>
                            <div class="colm-6"><label>Compiled By: </label><input type="text"
                                    class="form-control" id="checked_commendation" name="checked_commendation"
                                    value=""></div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--gratuity update -->
<div id="editGRATUITY" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Gratuity</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-gratuity') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="gratuityID" name="gratuityID"
                            value="">
                        <input type="hidden" class="form-control" id="gratuityUserID" name="gratuityUserID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Date of payment: </label><input type="text"
                                    class="form-control" id="dateofpayment" name="dateofpayment" value=""
                                    readonly></div>
                            <div class="colm-6"><label>From: </label><input type="text" class="form-control"
                                    id="periodfrom" name="periodfrom" value="" readonly></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>To: </label><input type="text" class="form-control"
                                    id="periodto" name="periodto" value="" readonly></div>
                            <div class="colm-6"><label>Years: </label><input type="text" class="form-control"
                                    id="periodyear" name="periodOfYears" value=""></div>

                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Month: </label><input type="text" class="form-control"
                                    id="periodmonth" name="periodOfMonths" value=""></div>
                            <div class="colm-6"><label>Days: </label><input type="text" class="form-control"
                                    id="periodday" name="periodOfDays" value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Gratuity Rate: </label><input type="text"
                                    class="form-control" id="rateofgratuity" name="rateofgratuity" value="">
                            </div>
                            <div class="colm-6"><label>Amount Paid: </label><input type="text"
                                    class="form-control" id="amountpaid" name="amountpaid" value=""></div>

                        </div>
                        <div class="row">

                            <div class="colm-6"><label>File Page Ref: </label><input type="text"
                                    class="form-control" id="pageref" name="pageref" value=""></div>
                            <div class="colm-6"><label>Checked By: </label><input type="text" class="form-control"
                                    id="gratuitycheckedby" name="gratuitycheckedby" value=""></div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!--terminate update -->
<div id="editTERMINATE" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of termination of service</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-terminate') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="terminateID" name="terminateID"
                            value="">
                        <input type="hidden" class="form-control" id="terminateUserID" name="terminateUserID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Date Terminated: </label><input type="text"
                                    class="form-control" id="dateTerminated" name="dateTerminated" value=""
                                    readonly></div>
                            <div class="colm-6"><label>Pension/Contract: </label><input type="text"
                                    class="form-control" id="pension_contract_terminate"
                                    name="pension_contract_terminate" value=""></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Pension of: </label><input type="text" class="form-control"
                                    id="pensionamount" name="pensionamount" value=""></div>
                            <div class="colm-6"><label>p.a From: </label><input type="text" class="form-control"
                                    id="pensionperanumfrom" name="pensionperanumfrom" value="" readonly></div>


                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Gratuity of: </label><input type="text"
                                    class="form-control" id="gratuity" name="gratuity" value=""></div>
                            <div class="colm-6"><label>Contract Gratuity of: </label><input type="text"
                                    class="form-control" id="contractGratuity" name="contractGratuity"
                                    value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Date of Death: </label><input type="text"
                                    class="form-control" id="dateOfDeath" name="dateOfDeath" value=""
                                    readonly></div>
                            <div class="colm-6"><label>Gratuity Paid: </label><input type="text"
                                    class="form-control" id="gratuityPaidEstate" name="gratuityPaidEstate"
                                    value=""></div>

                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Widows Pension: </label><input type="text"
                                    class="form-control" id="widowsPension" name="widowsPension" value="">
                            </div>
                            <div class="colm-6"><label>Widows Pension from: </label><input type="text"
                                    class="form-control" id="widowsPensionFrom" name="widowsPensionFrom"
                                    value="" readonly></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Orphans Pension: </label><input type="text"
                                    class="form-control" id="orphanPension" name="orphanPension" value="">
                            </div>
                            <div class="colm-6"><label>Orphans Pension from: </label><input type="text"
                                    class="form-control" id="orphanPensionFrom" name="orphanPensionFrom"
                                    value="" readonly></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Date of Transfer: </label><input type="text"
                                    class="form-control date-field" id="dateOfTransfer" name="dateOfTransfer"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Pension/Cont Transfer: </label><input type="text"
                                    class="form-control" id="pension_contract_transfer"
                                    name="pension_contract_transfer" value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Aggregate S.I.N Transfer (Years): </label><input type="text"
                                    class="form-control" id="aggregateYears" name="aggregateYears" value="">
                            </div>
                            <div class="colm-6"><label>Aggregate S.I.N Transfe (Months): </label><input type="text"
                                    class="form-control" id="aggregateMonths" name="aggregateMonths" value="">
                            </div>


                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Aggregate S.I.N Transfer (Days): </label><input type="text"
                                    class="form-control" id="aggregateDays" name="aggregateDays" value="">
                            </div>
                            <div class="colm-6"><label>Aggregate Salary: </label><input type="text"
                                    class="form-control" id="aggregateSalary" name="aggregateSalary" value="">
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- edit tour and leave update-->
<div id="editTOURLEAVE" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of tour and leave</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-tour-leave') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="tourLeaveID" name="tourLeaveID"
                            value="">
                        <input type="hidden" class="form-control" id="leaveUserID" name="leaveUserID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Date Tour Started: </label><input type="text"
                                    class="form-control date-field" id="dateTourStarted" name="dateTourStarted"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Gezette Notice No.: </label><input type="text"
                                    class="form-control" id="tourGezetteNumber" name="tourGezetteNumber"
                                    value=""></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Length of Tour: </label><input type="text"
                                    class="form-control" id="lengthOfTour" name="lengthOfTour" value=""></div>
                            <div class="colm-6"><label>Date Due for Leave: </label><input type="text"
                                    class="form-control date-field" id="leaveDueDate" name="leaveDueDate"
                                    value="" readonly></div>


                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Date Departed on Leave: </label><input type="text"
                                    class="form-control date-field" id="leaveDepartDate" name="leaveDepartDate"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Gazette Notice No.: </label><input type="text"
                                    class="form-control" id="leaveGezetteNumber" name="leaveGezetteNumber"
                                    value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Date Due to Return from Leave: </label><input type="text"
                                    class="form-control date-field" id="leaveReturnDate" name="leaveReturnDate"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Date Extension granted to: </label><input type="text"
                                    class="form-control date-field" id="dateExtensionGranted"
                                    name="dateExtensionGranted" value="" readonly></div>

                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Salary Rule for Ext.: </label><input type="text"
                                    class="form-control" id="salaryRuleForExt" name="salaryRuleForExt"
                                    value=""></div>
                            <div class="colm-6"><label>Date Resumed Duty: </label><input type="text"
                                    class="form-control date-field" id="dateResumedDuty" name="dateResumedDuty"
                                    value="" readonly></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>To UK: </label><input type="text" class="form-control"
                                    id="toUK" name="toUK" value=""></div>
                            <div class="colm-6"><label>Fro UK: </label><input type="text" class="form-control"
                                    id="fromUK" name="fromUK" value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Resident (Mnths): </label><input type="text"
                                    class="form-control date-field" id="residentMonths" name="residentMonths"
                                    value="">
                            </div>
                            <div class="colm-6"><label>Resident (Days): </label><input type="text"
                                    class="form-control date-field" id="residentDays" name="residentDays"
                                    value=""></div>

                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Leave (Months): </label><input type="text"
                                    class="form-control date-field" id="leaveMonths" name="leaveMonths"
                                    value="">
                            </div>
                            <div class="colm-6"><label>Leave (Days): </label><input type="text"
                                    class="form-control date-field" id="leaveDays" name="leaveDays"
                                    value=""></div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- edit record of service -->
<div id="editSERVICERECORD" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of record of service</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-record-service') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="recID" name="recID"
                            value="">
                        <input type="hidden" class="form-control" id="serviceUserID" name="serviceUserID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Date of Entry: </label><input type="text"
                                    class="form-control date-field" id="entryDate" name="entryDate"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Details: </label><input type="text" class="form-control"
                                    id="detail" name="detail" value=""></div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Signature: </label><input type="text"
                                    class="form-control" id="signature" name="signature" value=""></div>
                            <div class="colm-6"><label>Stamp: </label><input type="text" class="form-control"
                                    id="namestamp" name="namestamp" value=""></div>


                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- edit record of emolument-->
<div id="editEMOLUMENTRECORD" class="modal fade">
    <div class="modal-dialog box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of record of emolument</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-record-emolument') }}" method="post"
                role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="emolumentID" name="emolumentID"
                            value="">
                        <input type="hidden" class="form-control" id="emolumentUserID" name="emolumentUserID"
                            value="">

                        <div class="row">

                            <div class="colm-6"><label>Date of Entry: </label><input type="text"
                                    class="form-control date-field" id="eentryDate" name="eentryDate"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Basic Salary: </label><input type="text"
                                    class="form-control" id="salaryScale" name="salaryScale" value="">
                            </div>
                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Basic Salary p.a: </label><input type="text"
                                    class="form-control" id="basicSalaryPA" name="basicSalaryPA" value="">
                            </div>
                            <div class="colm-6"><label>Inducement Pay p.a.: </label><input type="text"
                                    class="form-control" id="inducementPayPA" name="inducementPayPA"
                                    value=""></div>


                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Date Paid from: </label><input type="text"
                                    class="form-control date-field" id="datePaidFrom" name="datePaidFrom"
                                    value="" readonly></div>
                            <div class="colm-6"><label>Increment Date (M): </label><input type="text"
                                    class="form-control date-field" id="month" name="month"
                                    value=""></div>


                        </div>
                        <div class="row">

                            <div class="colm-6"><label>Increment Date (Y): </label><input type="text"
                                    class="form-control" id="year" name="year" value=""></div>
                            <div class="colm-6"><label>Authority: </label><input type="text"
                                    class="form-control date-field" id="authority" name="authority"
                                    value=""></div>


                        </div>

                        <div class="row">

                            <div class="colm-6"><label>Signature: </label><input type="text"
                                    class="form-control" id="ssignature" name="ssignature" value=""></div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>
    </div>
</div>

<!--date of birth update -->
<div id="editDOB" class="modal fade">
    <div class="modal-dialog modalm box box-default" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Particulars of Date of Birth</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-particulars-of-birth') }}"
                method="post" role="form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group" style="margin: 0 10px;">

                        <input type="hidden" class="form-control" id="fileID2" name="fileID"
                            value="">

                        <div class="row">

                            <div class="colm-12"><label>Date of Birth: </label><input type="text"
                                    class="form-control date-field" id="dob" name="date_of_birth"
                                    placeholder="YYYY-MM-DD" required></div>

                        </div>
                        <div class="row">

                            <div class="colm-12"><label>Place of Birth: </label><select required
                                    class="form-control" id="pob" name="place_of_birth">
                                    @foreach ($getState as $list)
                                        <option value="{{ $list->StateID }}"
                                            {{ old('place_of_birth') == $list->StateID ? 'selected' : '' }}>
                                            {{ $list->State }}</option>
                                    @endforeach
                                </select></div>

                        </div>

                        <div class="row">

                            <div class="colm-12"><label>Marital Status: </label> <select required
                                    class="form-control" id="ms" name="maritalStatus">
                                    @foreach ($getMS as $list)
                                        <option value="{{ $list->ID }}"
                                            {{ old('maritalStatus') == $list->ID ? 'selected' : '' }}>
                                            {{ $list->marital_status }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button id="refreshPages" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- <!-alary details update--> --}}
<div id="editSALARYINFO" class="modal fade">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Salary Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-horizontal" action="{{ url('/profile/update-salary-details') }}" method="post"
                role="form">
                {{ csrf_field() }}

                <div class="form-group col-md-6" style="margin-left:10px">

                    <input type="hidden" class="form-control" id="ID3" name="fileID" value="">

                    <div class="row">
                        <div class=""><label>First Appointment: </label><input type="text"
                                class="form-control date-field" id="appointment_date" name="appointment_date"
                                value="" readonly></div>
                        <div class=""><label>First Arrival: </label><input type="text"
                                class="form-control date-field" id="firstarrival_date" name="firstarrival_date"
                                value="" readonly>

                        </div>
                        <div class=""><label>Employment Type: </label>
                            <select required class="form-control" id="employee_type" name="employee_type">
                                @foreach ($getEmpType as $list)
                                    <option value="{{ $list->id }}"
                                        {{ old('employee_type') == $list->id ? 'selected' : '' }}>
                                        {{ $list->employmentType }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class=""><label>Designation: </label><select required class="form-control"
                                id="Designation" name="designation">
                                @foreach ($getDesignation as $list)
                                    <option value="{{ $list->id }}"
                                        {{ old('designation') == $list->id ? 'selected' : '' }}>
                                        {{ $list->designation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=""><label>Department: </label>
                            <select required class="form-control" id="department" name="department">
                                @foreach ($getDepartment as $list)
                                    <option value="{{ $list->id }}"
                                        {{ old('department') == $list->id ? 'selected' : '' }}>
                                        {{ $list->department }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=""><label>Section </label><input type="text" class="form-control"
                                id="section" name="section" value=""></div>

                        <div class=""><label>Grade: </label>
                            <select required class="form-control" id="grade" name="grade">
                                @foreach ($getGrade as $list)
                                    <option value="{{ $list->id }}"
                                        {{ old('grade') == $list->id ? 'selected' : '' }}>{{ $list->grade }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="form-group col-md-6" style="margin-right:10px">

                    <div class="row">

                        <div class=""><label>Step: </label>
                            <select required class="form-control" id="step" name="step">
                                @foreach ($getStep as $list)
                                    <option value="{{ $list->id }}"
                                        {{ old('step') == $list->id ? 'selected' : '' }}>{{ $list->steps }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class=""><label>Bank: </label> <select required class="form-control"
                                id="bank" name="bank">
                                @foreach ($getBank as $list)
                                    <option value="{{ $list->bankID }}"
                                        {{ old('bank') == $list->bankID ? 'selected' : '' }}>{{ $list->bank }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class=""><label>Bank Group: </label><input type="text" class="form-control"
                                id="bankgroup" name="bankgroup" value=""></div>
                        <div class=""><label>Bank Branch: </label><input type="text" class="form-control"
                                id="bankbranch" name="bankbranch" value=""></div>
                        <div class=""><label>Account Number: </label><input type="text"
                                class="form-control" id="accno" name="accno" value=""></div>
                    </div>

                    <div class="row">
                        <div class=""><label>NHF No.: </label><input type="text" class="form-control"
                                id="nhfno" name="nhfno" value=""></div>
                        <div class=""><label>Incremental Date: </label><input type="text"
                                class="form-control date-field" id="incrementaldate" name="incrementaldate"
                                value="" readonly></div>
                        <div class=""></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button id="refreshPage" type="submit" class="btn btnuccess btn-xs">Update</button>
                    <button type="button" class="btn btnecondary btn-xs" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>

    </div>
</div>


@section('script')
@endsection
