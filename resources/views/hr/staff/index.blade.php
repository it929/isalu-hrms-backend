@extends('layouts.layout')
@section('pageTitle')
    Staff List
@endsection

@section('content')
    <div class="box box-default" style="border-top: none;">
        <div class="box-header with-border">
            <div class="pull-left">
                <button class="btn btn-sm btn-primary hidden-print" onclick="window.print()">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
            <div class="pull-right hidden-print">
                <div class="form-inline">
                    <form method="GET" action="{{ url()->current() }}" class="form-inline" id="filterForm">
                        <select name="filter" id="documentFilter" class="form-control input-sm" style="width: 200px; margin-right: 10px;" onchange="this.form.submit()">
                            <option value="all" {{ request('filter', 'all') == 'all' ? 'selected' : '' }}>All Staff</option>
                            <option value="with_documents" {{ request('filter') == 'with_documents' ? 'selected' : '' }}>With Education Documents</option>
                            <option value="without_documents" {{ request('filter') == 'without_documents' ? 'selected' : '' }}>Without Education Documents</option>
                            <option value="with_attachments" {{ request('filter') == 'with_attachments' ? 'selected' : '' }}>With Attachments</option>
                            <option value="without_attachments" {{ request('filter') == 'without_attachments' ? 'selected' : '' }}>Without Attachments</option>
                            <option value="with_both" {{ request('filter') == 'with_both' ? 'selected' : '' }}>With Both Documents & Attachments</option>
                            <option value="without_both" {{ request('filter') == 'without_both' ? 'selected' : '' }}>Without Any Documents/Attachments</option>
                        </select>
                        <input type="text" name="search" id="searchStaff" class="form-control input-sm" placeholder="Search staff..." style="width: 250px;" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-default hidden-print" style="margin-left: 5px;">
                            <i class="fa fa-search"></i>
                        </button>
                        @if(request('filter') || request('search'))
                            <a href="{{ url()->current() }}" class="btn btn-sm btn-warning hidden-print" style="margin-left: 5px;">
                                <i class="fa fa-times"></i> Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div style="margin: 10px 20px;">
        <div align="center" class="hidden-print">
            <h3><b>{{ strtoupper('ISALU HOSPITAL') }}</b></h3>
            <h5><strong>STAFF LIST</strong></h5>
            @if(request('filter') && request('filter') != 'all')
                <p class="text-info hidden-print">
                    <i class="fa fa-filter"></i> Filter:
                    @switch(request('filter'))
                        @case('with_documents') With Education Documents @break
                        @case('without_documents') Without Education Documents @break
                        @case('with_attachments') With Attachments @break
                        @case('without_attachments') Without Attachments @break
                        @case('with_both') With Both Documents & Attachments @break
                        @case('without_both') Without Any Documents/Attachments @break
                    @endswitch
                </p>
            @endif
            @if(request('search'))
                <p class="text-info hidden-print">
                    <i class="fa fa-search"></i> Search: "{{ request('search') }}"
                </p>
            @endif
            <big><b></b></big>
        </div>
        <div align="center" class="visible-print-block">
            <h3><b>{{ strtoupper('ISALU HOSPITAL') }}</b></h3>
            <h5><strong>STAFF LIST</strong></h5>
            <big><b></b></big>
        </div>

        <!-- STAFF COUNTS SUMMARY CARDS - NEW SECTION -->
        <div class="row hidden-print" style="margin: 20px 0;">
            <div class="col-md-4 col-sm-6">
                <div class="small-box bg-info" style="border-radius: 5px; padding: 15px; color: white; background-color: #17a2b8;">
                    <div class="inner">
                        <h3>{{ $staffList->total() }}</h3>
                        <p>Total Staff on this page</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="small-box bg-success" style="border-radius: 5px; padding: 15px; color: white; background-color: #28a745;">
                    <div class="inner">
                        <h3>{{ $staffWithDocuments }}</h3>
                        <p>Staff with Education Documents <span style="font-size: 12px;">(Current Page)</span></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="small-box bg-primary" style="border-radius: 5px; padding: 15px; color: white; background-color: #007bff;">
                    <div class="inner">
                        <h3>{{ $staffWithAttachments }}</h3>
                        <p>Staff with Attachments <span style="font-size: 12px;">(Current Page)</span></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-paperclip"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- SYSTEM-WIDE STAFF TOTALS -->
        @if(isset($totalAllStaffWithDocuments) && isset($totalAllStaffWithAttachments))
        <div class="row hidden-print" style="margin: 10px 0 20px 0;">
            <div class="col-md-12">
                <div class="alert alert-info" style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px;">
                    <i class="fa fa-database"></i>
                    <strong>System-wide totals:</strong>
                    <span class="badge" style="background-color: #28a745; color: white; padding: 5px 10px; margin: 0 5px;">
                        <i class="fa fa-graduation-cap"></i> {{ number_format($totalAllStaffWithDocuments) }} Staff with Education Documents
                    </span>
                    <span class="badge" style="background-color: #007bff; color: white; padding: 5px 10px; margin: 0 5px;">
                        <i class="fa fa-paperclip"></i> {{ number_format($totalAllStaffWithAttachments) }} Staff with Attachments
                    </span>
                </div>
            </div>
        </div>
        @endif

        <br />
        @if (session('err'))
            <div class="col-sm-12 alert alert-warning alert-dismissible hidden-print" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong>
                {{ session('err') }}
            </div>
        @endif

        @if (session('success'))
            <div class="col-sm-12 alert alert-success alert-dismissible hidden-print" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span>
                </button>
                <strong>Success!</strong>
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="box-body">
        <div class="row">
            {{ csrf_field() }}

            <div class="col-md-12">
                <!-- Card container -->
                <div class="panel panel-success">
                    <div class="panel-heading bg-success hidden-print" style="color:#fff;background-color:#28a745;border-color:#28a745;">
                        <h4 class="panel-title">
                            <i class="fa fa-users"></i> STAFF LIST
                            <span class="pull-right" style="font-size: 14px;">
                                <span class="badge" style="background-color: #fff; color: #28a745; margin-right: 5px;">
                                    <i class="fa fa-users"></i> Total: {{ $staffList->total() }}
                                </span>
                                <span class="badge" style="background-color: #fff; color: #28a745; margin-right: 5px;">
                                    <i class="fa fa-graduation-cap"></i> With Docs: {{ $staffWithDocuments }}
                                </span>
                                <span class="badge" style="background-color: #fff; color: #007bff;">
                                    <i class="fa fa-paperclip"></i> With Att: {{ $staffWithAttachments }}
                                </span>
                            </span>
                        </h4>
                    </div>

                    <div class="panel-body" style="overflow-x:auto;">
                        @if($staffList->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed table-bordered input-sm">
                                <thead>
                                    <tr class="input-sm">
                                        <th>S/N</th>
                                        <th>FILE NO.</th>
                                        <th width="200">FULL NAME</th>
                                        <th>DOCUMENTS</th>
                                        <th>ATTACHMENTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $key = 1;
                                        $staffWithDocsCount = 0;
                                        $staffWithAttsCount = 0;
                                    @endphp
                                    @foreach ($staffList as $staff)
                                        @php
                                            $hasDocs = isset($staff->educations) && $staff->educations->count() > 0;
                                            $hasAtts = isset($staff->attachments) && $staff->attachments->count() > 0;

                                            if($hasDocs) $staffWithDocsCount++;
                                            if($hasAtts) $staffWithAttsCount++;
                                        @endphp
                                        <tr>
                                            <td>{{ ($staffList->currentpage() - 1) * $staffList->perpage() + $key++ }}</td>
                                            <td>
                                                <strong>{{ $staff->fileNo }}</strong>
                                                @if($hasDocs && $hasAtts)
                                                    <span class="label label-success pull-right" title="Has both documents and attachments">Both</span>
                                                @elseif($hasDocs)
                                                    <span class="label label-info pull-right" title="Has education documents">Docs</span>
                                                @elseif($hasAtts)
                                                    <span class="label label-primary pull-right" title="Has attachments">Att</span>
                                                @else
                                                    <span class="label label-warning pull-right" title="No documents or attachments">None</span>
                                                @endif
                                            </td>
                                            <td>{{ strtoupper($staff->surname . ' ' . $staff->first_name . ' ' . ($staff->othernames ?? '')) }}</td>

                                            <!-- Education Documents Column -->
                                            <td>
                                                @if($hasDocs)
                                                    <div class="dropdown hidden-print">
                                                        <button class="btn btn-xs btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa fa-graduation-cap"></i> Education ({{ $staff->educations->count() }})
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @foreach($staff->educations as $education)
                                                                <li>
                                                                    <a href="{{ $education->document ?? '#' }}" target="_blank" title="{{ $education->degreequalification }}">
                                                                        <i class="fa fa-file-pdf-o text-danger"></i>
                                                                        {{ $education->degreequalification }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <span class="visible-print-block">
                                                        <i class="fa fa-check text-success"></i> {{ $staff->educations->count() }} Education Document(s)
                                                    </span>
                                                    <span class="hidden-print label label-success">Has Documents</span>
                                                @else
                                                    <span class="label label-warning">No Education</span>
                                                @endif
                                            </td>

                                            <!-- Attachments Column -->
                                            <td>
                                                @if($hasAtts)
                                                    <div class="dropdown hidden-print">
                                                        <button class="btn btn-xs btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa fa-paperclip"></i> Attachments ({{ $staff->attachments->count() }})
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @foreach($staff->attachments as $attachment)
                                                                <li>
                                                                    <a href="{{ $attachment->filepath }}" target="_blank" title="{{ $attachment->filedesc }}">
                                                                        <i class="fa fa-file-text-o"></i>
                                                                        {{ Str::limit($attachment->filedesc, 20) }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <span class="visible-print-block">
                                                        <i class="fa fa-check text-success"></i> {{ $staff->attachments->count() }} Attachment(s)
                                                    </span>
                                                    <span class="hidden-print label label-primary">Has Attachments</span>
                                                @else
                                                    <span class="label label-warning">No Attachments</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Detailed Staff Counts Summary -->
                        <div class="row hidden-print" style="margin: 20px 0 10px 0;">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading" style="background-color: #f0f0f0;">
                                        <h5 class="panel-title"><i class="fa fa-pie-chart"></i> Staff Document Status Summary (Current Page)</h5>
                                    </div>
                                    <div class="panel-body">
                                        @php
                                            $staffWithBoth = $staffList->filter(function($staff) {
                                                return isset($staff->educations) && $staff->educations->count() > 0
                                                    && isset($staff->attachments) && $staff->attachments->count() > 0;
                                            })->count();

                                            $staffWithDocsOnly = $staffWithDocsCount - $staffWithBoth;
                                            $staffWithAttsOnly = $staffWithAttsCount - $staffWithBoth;
                                            $staffWithNone = $staffList->count() - ($staffWithDocsCount + $staffWithAttsCount - $staffWithBoth);
                                        @endphp

                                        <div class="row">
                                            <div class="col-md-3 col-sm-6">
                                                <div class="well well-sm text-center" style="background-color: #d4edda;">
                                                    <h4>{{ $staffWithBoth }}</h4>
                                                    <small>Staff with Both</small>
                                                    <div class="progress" style="height: 8px; margin-top: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: {{ $staffList->count() > 0 ? ($staffWithBoth/$staffList->count())*100 : 0 }}%"></div>
                                                    </div>
                                                    <span class="text-muted">{{ $staffList->count() > 0 ? round(($staffWithBoth/$staffList->count())*100, 1) : 0 }}%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <div class="well well-sm text-center" style="background-color: #cce5ff;">
                                                    <h4>{{ $staffWithDocsOnly }}</h4>
                                                    <small>Staff with Documents Only</small>
                                                    <div class="progress" style="height: 8px; margin-top: 8px;">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: {{ $staffList->count() > 0 ? ($staffWithDocsOnly/$staffList->count())*100 : 0 }}%"></div>
                                                    </div>
                                                    <span class="text-muted">{{ $staffList->count() > 0 ? round(($staffWithDocsOnly/$staffList->count())*100, 1) : 0 }}%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <div class="well well-sm text-center" style="background-color: #fff3cd;">
                                                    <h4>{{ $staffWithAttsOnly }}</h4>
                                                    <small>Staff with Attachments Only</small>
                                                    <div class="progress" style="height: 8px; margin-top: 8px;">
                                                        <div class="progress-bar bg-warning" role="progressbar"
                                                            style="width: {{ $staffList->count() > 0 ? ($staffWithAttsOnly/$staffList->count())*100 : 0 }}%"></div>
                                                    </div>
                                                    <span class="text-muted">{{ $staffList->count() > 0 ? round(($staffWithAttsOnly/$staffList->count())*100, 1) : 0 }}%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <div class="well well-sm text-center" style="background-color: #f8d7da;">
                                                    <h4>{{ $staffWithNone }}</h4>
                                                    <small>Staff with No Documents/Attachments</small>
                                                    <div class="progress" style="height: 8px; margin-top: 8px;">
                                                        <div class="progress-bar bg-danger" role="progressbar"
                                                            style="width: {{ $staffList->count() > 0 ? ($staffWithNone/$staffList->count())*100 : 0 }}%"></div>
                                                    </div>
                                                    <span class="text-muted">{{ $staffList->count() > 0 ? round(($staffWithNone/$staffList->count())*100, 1) : 0 }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System-wide Staff Counts Summary -->
                        @if(isset($totalAllStaffWithDocuments) && isset($totalAllStaffWithAttachments))
                        <div class="row hidden-print" style="margin: 20px 0;">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h5 class="panel-title"><i class="fa fa-globe"></i> System-wide Staff Document Status</h5>
                                    </div>
                                    <div class="panel-body">
                                        @php
                                            $totalStaffSystem = DB::table('tblper')
                                                ->where('employee_type', '<>', 'CONSOLIDATED')
                                                ->where('employee_type', '!=', 2)
                                                ->where('staff_status', 1)
                                                ->count();
                                        @endphp
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h3>{{ $totalStaffSystem }}</h3>
                                                    <small>Total Staff (System-wide)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h3>{{ $totalAllStaffWithDocuments }}</h3>
                                                    <small>Staff with Education Documents</small>
                                                    <div class="progress" style="height: 5px; margin-top: 5px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: {{ $totalStaffSystem > 0 ? ($totalAllStaffWithDocuments/$totalStaffSystem)*100 : 0 }}%"></div>
                                                    </div>
                                                    <span>{{ $totalStaffSystem > 0 ? round(($totalAllStaffWithDocuments/$totalStaffSystem)*100, 1) : 0 }}% of total</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h3>{{ $totalAllStaffWithAttachments }}</h3>
                                                    <small>Staff with Attachments</small>
                                                    <div class="progress" style="height: 5px; margin-top: 5px;">
                                                        <div class="progress-bar bg-primary" role="progressbar"
                                                            style="width: {{ $totalStaffSystem > 0 ? ($totalAllStaffWithAttachments/$totalStaffSystem)*100 : 0 }}%"></div>
                                                    </div>
                                                    <span>{{ $totalStaffSystem > 0 ? round(($totalAllStaffWithAttachments/$totalStaffSystem)*100, 1) : 0 }}% of total</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="text-right hidden-print" style="margin-top:10px;">
                            Showing {{ ($staffList->currentpage() - 1) * $staffList->perpage() + 1 }}
                            to {{ min($staffList->currentpage() * $staffList->perpage(), $staffList->total()) }}
                            of {{ $staffList->total() }} entries
                        </div>

                        <div class="hidden-print text-center" style="margin-top:20px;">
                            {{ $staffList->appends(request()->except('page'))->links('vendor.pagination.bootstrap-4') }}
                        </div>
                        @else
                        <div class="text-center text-muted py-4">
                            <i class="fa fa-users fa-3x mb-3" style="color: #ccc;"></i><br>
                            <h4>No staff records found</h4>
                            <p>There are no staff records matching your criteria.</p>
                            @if(request('filter') || request('search'))
                                <a href="{{ url()->current() }}" class="btn btn-sm btn-primary hidden-print">
                                    <i class="fa fa-times"></i> Clear Filters
                                </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div><!-- /.row -->
    </div><!-- /.box-body -->

    <!-- Documents Modal -->
    <div id="documentsModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-folder-open"></i> Staff Documents</h4>
                </div>
                <div class="modal-body" id="staff-documents">
                    <!-- Staff documents will be loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // View all documents button click event
            $('.view-docs-btn').click(function() {
                var staffId = $(this).data('id');

                $.ajax({
                    url: "{{ url('/hr/staff/documents') }}/" + staffId,
                    type: "GET",
                    beforeSend: function() {
                        $('#staff-documents').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><br>Loading documents...</div>');
                    },
                    success: function(response) {
                        var html = `
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Education Documents</h4>
                                    ${response.educations && response.educations.length > 0 ?
                                        '<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Qualification</th><th>School</th><th>Period</th><th>Certificate</th><th>Action</th></tr></thead><tbody>' +
                                        response.educations.map(edu =>
                                            `<tr>
                                                <td>${edu.degreequalification || 'N/A'}</td>
                                                <td>${edu.schoolattended || 'N/A'}</td>
                                                <td>${edu.schoolfrom || ''} to ${edu.schoolto || ''}</td>
                                                <td>${edu.certificateheld || 'N/A'}</td>
                                                <td>
                                                    ${edu.document ?
                                                        `<a href="${edu.document}" target="_blank" class="btn btn-xs btn-danger">
                                                            <i class="fa fa-file-pdf-o"></i> View
                                                        </a>` :
                                                        '<span class="label label-default">No Document</span>'
                                                    }
                                                </td>
                                            </tr>`
                                        ).join('') +
                                        '</tbody></table></div>' :
                                        '<div class="alert alert-warning">No education records found</div>'
                                    }

                                    <hr>

                                    <h4>Staff Attachments</h4>
                                    ${response.attachments && response.attachments.length > 0 ?
                                        '<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Description</th><th>File</th><th>Action</th></tr></thead><tbody>' +
                                        response.attachments.map(attach =>
                                            `<tr>
                                                <td>${attach.filedesc || 'No Description'}</td>
                                                <td>${attach.filepath ? attach.filepath.split('/').pop() : 'N/A'}</td>
                                                <td>
                                                    ${attach.filepath ?
                                                        `<a href="${attach.filepath}" target="_blank" class="btn btn-xs btn-primary">
                                                            <i class="fa fa-download"></i> Download
                                                        </a>` :
                                                        '<span class="label label-default">No File</span>'
                                                    }
                                                </td>
                                            </tr>`
                                        ).join('') +
                                        '</tbody></table></div>' :
                                        '<div class="alert alert-warning">No attachments found</div>'
                                    }

                                    <hr>

                                    <h4>Profile Images</h4>
                                    <div class="row">
                                        <div class="col-md-6 text-center">
                                            <h5>Passport Photo</h5>
                                            ${response.passport_url ?
                                                `<img src="${response.passport_url}" class="img-thumbnail" style="max-height: 200px;">
                                                <br><a href="${response.passport_url}" target="_blank" class="btn btn-xs btn-success mt-2">View Full Size</a>` :
                                                '<div class="alert alert-info">No passport photo available</div>'
                                            }
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <h5>Signature</h5>
                                            ${response.signature_url ?
                                                `<img src="${response.signature_url}" class="img-thumbnail" style="max-height: 200px;">
                                                <br><a href="${response.signature_url}" target="_blank" class="btn btn-xs btn-warning mt-2">View Full Size</a>` :
                                                '<div class="alert alert-info">No signature available</div>'
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        $('#staff-documents').html(html);
                        $('#documentsModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        $('#staff-documents').html(`
                            <div class="alert alert-danger">
                                <h4>Error Loading Documents</h4>
                                <p>Failed to load staff documents. Please try again.</p>
                            </div>
                        `);
                        $('#documentsModal').modal('show');
                    }
                });
            });

            // Edit button click event
            $('.edit-btn').click(function() {
                var staffId = $(this).data('id');
                window.location.href = "{{ url('/hr/staff/edit') }}/" + staffId;
            });

            // Profile button click event
            $('.profile-btn').click(function() {
                var staffId = $(this).data('id');
                window.location.href = "{{ url('/hr/staff/profile') }}/" + staffId;
            });

            // Enter key submits search form
            $('#searchStaff').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#filterForm').submit();
                }
            });

            // Show loading indicator when form submits
            $('#filterForm').on('submit', function() {
                $('.panel-body').html(`
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-3x"></i><br>
                        <h4>Loading staff data...</h4>
                        <p>Please wait while we filter the staff records.</p>
                    </div>
                `);
            });
        });
    </script>
@endsection

@section('styles')
    <style type="text/css">
        /* Print Styles */
        @media print {
            /* Hide all elements that shouldn't print */
            .hidden-print {
                display: none !important;
            }

            /* Show elements that should only appear in print */
            .visible-print-block {
                display: block !important;
            }
            .visible-print-inline {
                display: inline !important;
            }

            /* Remove all background colors */
            body, .panel-title, td, th, span, div {
                color: #000 !important;
                background-color: transparent !important;
            }

            /* Ensure table prints properly */
            .table {
                border-collapse: collapse !important;
                width: 100% !important;
                font-size: 11px !important;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #000 !important;
                padding: 4px !important;
            }

            /* Remove table striping for better print */
            .table-striped > tbody > tr:nth-of-type(odd) {
                background-color: transparent !important;
            }

            /* Remove all box shadows and borders */
            .box, .panel, .panel-success {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hide panel heading in print */
            .panel-heading {
                display: none !important;
            }

            /* Adjust panel body for print */
            .panel-body {
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
            }

            /* Hide filters and search info in print */
            .text-info {
                display: none !important;
            }

            /* Hide the "Showing X to Y of Z entries" text */
            .text-right.hidden-print {
                display: none !important;
            }

            /* Hide pagination */
            .pagination {
                display: none !important;
            }

            /* Make text bold for better readability in print */
            th {
                font-weight: bold !important;
            }

            /* Hide dropdown buttons and show simple text */
            .btn, .dropdown, .dropdown-toggle, .dropdown-menu {
                display: none !important;
            }

            /* Adjust font sizes for print */
            body {
                font-size: 11px !important;
            }

            /* Remove margins and padding from main containers */
            .container, .box-body, .row, .col-md-12 {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            /* Better page breaks */
            table {
                page-break-inside: auto !important;
            }
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            thead {
                display: table-header-group !important;
            }

            /* Remove link colors */
            a {
                color: #000 !important;
                text-decoration: none !important;
            }

            /* Remove label backgrounds */
            .label {
                background-color: transparent !important;
                border: 1px solid #000 !important;
                padding: 1px 4px !important;
                font-size: 10px !important;
            }

            /* Adjust check icons for print */
            .fa-check {
                color: #000 !important;
                font-size: 10px !important;
            }

            /* Remove any remaining background images */
            * {
                background-image: none !important;
            }

            /* Show Supreme Court header in print */
            .visible-print-block {
                text-align: center;
                margin-bottom: 15px;
            }

            .visible-print-block h3 {
                font-size: 18px !important;
                margin: 5px 0 !important;
            }

            .visible-print-block h5 {
                font-size: 14px !important;
                margin: 5px 0 !important;
            }

            /* Hide summary cards and detailed summary in print */
            .small-box, .panel-default, .panel-info, .well, .alert {
                display: none !important;
            }
        }

        /* Screen Styles */
        .hidden-print {
            display: block;
        }
        .visible-print-block {
            display: none;
        }
        .visible-print-inline {
            display: none;
        }

        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .table-bordered th {
            background-color: #f8f9fa;
        }
        .panel {
            margin-bottom: 20px;
            background-color: #fff;
            border: 1px solid transparent;
            border-radius: 4px;
            -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
            box-shadow: 0 1px 1px rgba(0,0,0,.05);
        }
        .panel-success {
            border-color: #d6e9c6;
        }
        .panel-heading {
            padding: 10px 15px;
            border-bottom: 1px solid transparent;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .panel-title {
            margin-top: 0;
            margin-bottom: 0;
            font-size: 16px;
            color: inherit;
        }
        .panel-body {
            padding: 15px;
        }
        .btn-xs {
            padding: 3px 8px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 3px;
        }
        .dropdown-menu > li > a {
            padding: 5px 15px;
            font-size: 12px;
        }
        .img-thumbnail {
            max-width: 100%;
            height: auto;
        }
        .label {
            padding: 3px 8px;
            font-size: 11px;
            display: inline-block;
            margin-bottom: 2px;
            border-radius: 3px;
        }
        .label-success {
            background-color: #28a745;
            color: white;
        }
        .label-info {
            background-color: #17a2b8;
            color: white;
        }
        .label-primary {
            background-color: #007bff;
            color: white;
        }
        .label-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #6c757d !important;
            font-size: 11px;
        }
        .table td {
            vertical-align: middle !important;
        }

        /* Filter styling */
        #documentFilter {
            height: 30px;
            font-size: 12px;
        }

        .text-info {
            color: #17a2b8 !important;
            font-size: 14px;
            margin: 5px 0;
        }

        /* Summary Cards Styling */
        .small-box {
            border-radius: 5px;
            position: relative;
            display: block;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .small-box > .inner {
            padding: 10px;
        }

        .small-box h3 {
            font-size: 38px;
            font-weight: bold;
            margin: 0 0 10px 0;
            white-space: nowrap;
            padding: 0;
        }

        .small-box p {
            font-size: 15px;
            margin: 0;
        }

        .small-box .icon {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 0;
            font-size: 70px;
            color: rgba(0,0,0,0.15);
        }

        .small-box:hover .icon {
            font-size: 75px;
            color: rgba(0,0,0,0.2);
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            border-radius: 10px;
            margin: 0 2px;
        }

        /* Progress bars for summary */
        .progress {
            height: 8px;
            margin-bottom: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            float: left;
            width: 0%;
            height: 100%;
            font-size: 12px;
            line-height: 20px;
            color: #fff;
            text-align: center;
            background-color: #007bff;
            transition: width .6s ease;
        }

        .progress-bar.bg-success {
            background-color: #28a745;
        }

        .progress-bar.bg-info {
            background-color: #17a2b8;
        }

        .progress-bar.bg-warning {
            background-color: #ffc107;
        }

        .progress-bar.bg-danger {
            background-color: #dc3545;
        }

        .well {
            min-height: 20px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
        }

        .well-sm {
            padding: 9px;
            border-radius: 3px;
        }

        .bg-success {
            background-color: #d4edda;
        }
        .bg-info {
            background-color: #d1ecf1;
        }
        .bg-warning {
            background-color: #fff3cd;
        }
        .bg-danger {
            background-color: #f8d7da;
        }

        .pull-right {
            float: right !important;
        }

        .mt-2 {
            margin-top: 10px;
        }
    </style>
@endsection
