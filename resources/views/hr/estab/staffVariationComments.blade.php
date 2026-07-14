@extends('layouts.layout')
@section('pageTitle')

@endsection
@section('content')


    <div id="attachModal" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">File Attachment</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form-horizontal" id="deletevariableModal" role="form" method="POST" action=""
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group" style="margin: 0 10px;">
                            <!--<input type="hidden" class="form-control" id="cid" name="id">-->
                            <div class="col-sm-12">
                                <label class="control-label">
                                    <h5>File Description </h5>
                                </label>
                                <input required class="form-control" autocomplete="off" name="attachment_description">
                            </div>
                            <div class="col-sm-12">
                                <label class="control-label">Attach File:</label>
                                <input type="file" name="filename" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" name="btn-attachment">Add</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <div class="box box-default" style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:0px;">
        <div class="box-body box-profile">
            <div class="box-header with-border">
                <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
            </div>
            <div class="box-body" style="padding-bottom:0px; margin-bottom:0px;">
                <div class="row">
                    <div class="col-xs-2">
                        <img src="{{ asset('Images/scn_logo.png') }}" class="img-responsive responsive"
                            style="width:100%; height:auto;">
                    </div>

                    <div class="col-xs-8">
                        <div>
                            <h3 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h3>
                            {{-- <h4 class="text-center text-success"><strong>ISALU HOSPITAL LIMITED , 3 ARM ZONE,
                                    ABUJA</strong></h4> --}}
                            <h4 class="text-center text-success"><strong>Staff Variation Approval/Action Comments</strong>
                            </h4>
                        </div>
                    </div>

                    <div class="col-xs-2">
                        <img style="width:250px; height:230px;" src="{{ $comments[0]->passport_url }}"
                            class="img-responsive responsive">
                    </div>
                </div>
            </div>
            <div class="box-body" style="padding-top:0px; margin-top:0px;">
                <div class="row">
                    <div class="col-md-12"><!--1st col-->

                        <div class="panel panel-default"
                            style="padding-bottom:0px; margin-bottom:0px;padding-top:0px; margin-top:0px;">
                            <div class="panel-heading fieldset-preview"><b>Staff Information</b></div>
                            <div class="panel-body">
                                <table class="table table-striped table-hover table-responsive table-condensed">
                                    <thead class="btn-lg">
                                        <tr>

                                            <td><b>Name </b></td>
                                            <td><b>Old Grade|Step </b></td>
                                            <td><b>New Grade|Step </b></td>
                                            <td><b> Last Increment </b></td>
                                            <td><b>Due Date </b></td>
                                            {{-- <td><b>Amount Approved </b></td> --}}
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td><b>{{ strtoupper($variationList->surname . ' ' . $variationList->first_name . ' ' . $variationList->othernames) }}</b>
                                            </td>
                                            <td><b>GL-{{ $variationList->old_grade }}|S-{{ $variationList->old_step }}</b>
                                            </td>
                                            <td><b>GL-{{ $variationList->new_grade }}|S-{{ $variationList->new_step }}</b>
                                            </td>
                                            <td><b>{{ $variationList->incremental_date }}</b></td>
                                            <td><b>{{ $variationList->due_date }}</b></td>
                                            {{-- <td style="text-align: right; "><b>Amount </b></td> --}}
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="clearfix"></div>
                            </div>
                        </div>


                        @foreach ($comments as $b)
                            <div class="panel panel-default">
                                <div class="panel-heading fieldset-preview"><b>Comment by: {{ $b->name }} on
                                        {{ date('F j, Y', strtotime($b->updated_at)) }} </b></div>
                                <div class="panel-body">
                                    {{ $b->comment }}
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        @endforeach




                        <hr />
                    </div>

                </div>
            </div>




        @endsection

        @section('styles')
            <style type="text/css">
                .modal-dialog {
                    width: 15cm
                }

                .modal-header {

                    background-color: #20b56d;

                    color: #FFF;

                }

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
            </style>
        @endsection

        @section('scripts')
            <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">

            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
            <link rel="stylesheet" type="text/css"
                href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

            <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

            <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>

            <script>
                function ReloadForm() {
                    document.getElementById('thisform1').submit();
                    return;
                }

                function addattachment(x) {
                    //document.getElementById('cid').value = x;
                    $("#attachModal").modal('show');
                }


                $(function() {
                    $("#fromdate").datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd'
                    });
                    $("#todate").datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd'
                    });
                });

                $(document).ready(function() {
                    $('#').DataTable();
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
            </script>


        @stop
