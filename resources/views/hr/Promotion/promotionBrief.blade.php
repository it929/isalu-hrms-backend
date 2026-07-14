@extends('layouts.layout')

@section('pageTitle')
@endsection
<style type="text/css">
    .details table tr td h4 {}

    .content {
        background: #eee !important;
    }
</style>

@section('content')
    <div class="box box-default" style="border:none;  background: #eee !important;">
        <div class="box-body box-profile" style="border:none">
            <div class="box-header" style="border:none">
                <h3 class="box-title"><b>@yield('pageTitle')</b> <span id='processing'></span></h3>
            </div>
            <div class="box-body"
                style="width:80% !important; margin:auto !important; background: #FFF !important;padding-left:35px;">
                <div class="row">
                    <div class="col-md-12"><!--1st col-->

                        @if (count($errors) > 0)
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">&times;</span> </button>
                                <strong>Error!</strong>
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if (session('msg'))
                            <div class="alert alert-success alert-dismissible" role="alert">

                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">&times;</span> </button>
                                <strong>Success!</strong> {{ session('msg') }}

                            </div>
                        @endif

                        @if (session('err'))
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">&times;</span> </button>
                                <strong>Not Allowed ! </strong> {{ session('err') }}
                            </div>
                        @endif

                    </div>
                    <div class="col-md-12"><!--2nd col-->
                        <h2 style="text-align: center; color: #00a65a;">ISALU HOSPITAL LIMITED </h2>
                        <h4 class="text-center">
                            <p>ISALU HOSPITAL LIMITED Complex</p>
                            <p>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308,</p>
                            <p>Abuja</p>
                        </h4>
                        <h3 style="text-align: center;"> BRIEF ON CANDIDATE FOR PROMOTION </h3>
                        <hr />
                        <div class="details">

                            <table width="100%">
                                <tr>
                                    <td><span></span>
                                        <h4><span>1. </span> SURNAME</h4>
                                    </td>
                                    <td>{{ $lists->surname }} </td>
                                    <td><strong>TITLE</strong> ____________</td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4><span>2. </span> OTHER NAMES </h4>
                                    </td>
                                    <td>{{ $lists->first_name }} {{ $lists->othernames }}</td>

                                </tr>
                                <tr>
                                    <td>
                                        <h4><span>3. </span> CHANGE OF NAME(If any) </h4>
                                    </td>
                                    <td></td>

                                </tr>
                                <tr>
                                    <td>
                                        <h4><span>4. </span> DATE OF BIRTH</h4>
                                    </td>
                                    <td>{{ $lists->dob }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4><span>5. </span> STATE OF ORIGIN</h4>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4><span>6. </span> L. G. A OF ORIGIN</h4>
                                    </td>
                                    <td>{{ $lists->Designation }} GL {{ $lists->grade }}</td>
                                </tr>

                                <tr>
                                    <td>
                                        <h4><span>7. </span> SENETORIAL ZONE</h4>
                                    </td>
                                    <td>{{ $lists->current_state }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4><span>8. </span> MARITAL STATUS</h4>
                                    </td>
                                    <td>{{ $lists->maritalstatus }}</td>
                                </tr>
                            </table>

                        </div>
                        <div class="schools" style="margin-top: 30px; margin-bottom:30px;">
                            <h4><span>9. </span> SCHOOLS COLLEGES ATTENDED WITH DATES</h4>

                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8; margin-top: 30px;">
                                <thead>
                                    <tr>
                                        <th colspan="3">(A) PRIMARY SCHOOL(S):</th>
                                    </tr> <br>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Name Of School</th>
                                        <th>Date Attended</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($primary as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->schoolattended }}</td>
                                            <td><strong>From: </strong> {{ $list->schoolfrom }} - <strong>To: </strong>
                                                {{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>


                        </div>

                        <!-- Education Qualification With Dates  -->

                        <div class="schools">

                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8;margin-top: 2px;">
                                <thead>
                                    <tr>
                                        <th colspan="3">(B) SECONDARY SCHOOL(S):</th>
                                    </tr> <br>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Name of School</th>
                                        <th>Date Attended</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($secondary as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->schoolattended }}</td>
                                            <td><strong>From: </strong> {{ $list->schoolfrom }} - <strong>To:
                                                </strong>{{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                        </div>

                        <!-- Education Qualification With Dates  -->

                        <div class="schools">


                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8; margin-top:20px;">
                                <thead>
                                    <tr>
                                        <th colspan="3">(C) TERTIARY SCHOOL(S):</th>
                                    </tr> <br>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Name of School</th>
                                        <th>Dates</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($tertiary as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->schoolattended }}</td>
                                            <td><strong>From: </strong> {{ $list->schoolfrom }} - <strong>To:
                                                </strong>{{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8; margin-top:20px;">
                                <thead>
                                    <tr>
                                        <th colspan="3">(D) POST GRADUATE SCHOOL(S):</th>
                                    </tr> <br>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Position Held</th>
                                        <th>Dates</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($postGraduate as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->schoolattended }}</td>
                                            <td><strong>From: </strong> {{ $list->schoolfrom }} - <strong>To:
                                                </strong>{{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                        </div>

                        <div class="schools" style="margin-top: 30px; margin-bottom:30px;">
                            <h4><span>10. </span> PROFESSIONAL/SPECIAL COURSES: (If Any)</h4>

                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8; margin-top: 20px;">
                                <thead>

                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($professional as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->schoolattended }}</td>
                                            <td><strong>From: </strong> {{ $list->schoolfrom }} - <strong>To:
                                                </strong>{{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>


                        </div>

                        <div class="schools" style="margin-top: 30px; margin-bottom:30px;">
                            <h4><span>11. </span> ALL EDUCATION/PROFESSIONAL QUALIFICATION AND DATES</h4>

                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8; margin-top: 20px;">
                                <thead>

                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($qualification as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->degreequalification }}</td>
                                            <td>{{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>


                        </div>

                        <div class="schools" style="margin-top: 30px; margin-bottom:30px;">
                            <h4><span>12. </span> RECORD OF SERVICE/CAREER PROGRESSION LEAVING SCHOOL:</h4>

                            <table class="table table-responsive table-condensed"
                                style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8; margin-top: 20px;">
                                <thead>
                                    <tr>
                                        <th>(A) BEFORE COMING TO SCN</th>
                                    </tr>

                                    <tr>
                                        <th>ORGANISATION</th>
                                        <th>POSITION HELD & SGL</th>
                                        <th>DATES</th>
                                    </tr>

                                </thead>

                                <tbody>
                                    @php $sn =1; @endphp
                                    @foreach ($educations as $list)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $list->degreequalification }}</td>
                                            <td>{{ $list->schoolto }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                            <h4> (B) ISALU HOSPITAL LIMITED </h4>
                            <p>
                            <ul>
                                <li>DATE OF APPOINMENT __________________________________{{ $lists->appointment_date }}
                                </li>
                                <li>DATE OF CONFIRMATION ________________________________{{ $lists->date_of_confirmation }}
                                </li>
                                <li>PRESENT POST _______________________________________{{ $lists->designation }}</li>
                                <li>DATE OF PRESENT POST
                                    _______________________________{{ $lists->date_present_appointment }}</li>
                                <li>DATE OF LAST PROMOTION _____________________________{{ $lists->last_promotion_date }}
                                </li>
                                <li>PRESENT SALARY GRADE LEVEL _________________________{{ $lists->grade }}</li>
                            </ul>
                            </p>

                        </div>

                        <div class="schools" style="margin-top: 30px; margin-bottom:30px;">
                            <h4><span>13. </span> <span><b>SIGNATURE___________________ DATE_______________</b></span></h4>
                        </div>



                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->



        </div>

    @endsection

    @section('styles')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
        <style type="text/css">
            .details table tr td h4 {}

            .content {
                background: #eee !important;
            }
        </style>
    @endsection

    @section('scripts')
        <script src="{{ asset('assets/js/datepicker_scripts.js') }}"></script>
        <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
        <script type="text/javascript">
            //Modal popup
            $(document).ready(function() {
                $('.open-modal').click(function() {
                    $('#myModal').modal('show');
                });
            });

            $(function() {
                $("#date").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '1910:2090', // specifying a hard coded year range
                    showOtherMonths: true,
                    selectOtherMonths: true,
                    dateFormat: "dd MM, yy",
                    //dateFormat: "D, MM d, yy",
                    onSelect: function(dateText, inst) {
                        var theDate = new Date(Date.parse($(this).datepicker('getDate')));
                        var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
                        $("#date").val(dateFormatted);
                    },
                });

            });
        </script>
    @endsection
