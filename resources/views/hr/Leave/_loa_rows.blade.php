  @forelse($getleaveRecord as $i => $list)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>

                                                <!-- Staff Name -->
                                                <td class="text-capitalize">
                                                    {{ $list->surname }} {{ $list->first_name }} {{ $list->othernames }}
                                                </td>

                                                <!-- Department -->
                                                <td>{{ $list->department }}</td>

                                                <!-- Leave Type -->
                                                {{-- <td>{{ $list->leaveType }}</td> --}}

                                                <!-- Leave Reason -->
                                                {{-- <td>{{ $list->reason_of_leave }}</td> --}}

                                                <!-- Duration -->
                                                <td>
                                                    @php
                                                        $days =
                                                            \Carbon\Carbon::parse($list->start_date)->diffInDays(
                                                                \Carbon\Carbon::parse($list->end_date),
                                                            ) + 1;
                                                    @endphp
                                                    {{ $days }} days
                                                </td>

                                                <!-- Date Applied -->
                                                <td>{{ \Carbon\Carbon::parse($list->created_at)->format('d M, Y') }}</td>

                                                <!-- Status -->
                                                <td>
                                                    @if ($list->status == 1)
                                                        <span class="label label-success">HOD Approved</span>
                                                    @elseif ($list->status == 2)
                                                        <span class="label label-danger">HR Approved</span>
                                                    @elseif ($list->status == 3)
                                                        <span class="label label-danger">HOD Reject</span>
                                                    @elseif ($list->status == 4)
                                                        <span class="label label-danger">HR Reject</span>
                                                    @else
                                                        <span class="label label-warning">Pending</span>
                                                    @endif
                                                </td>

                                                <!-- Action -->
                                                <td class="no-print">
                                                    {{-- <a href="{{ url('leave/view/' . $list->id) }}"
                                                            class="btn btn-info btn-sm">View</a> --}}

                                                    <a href="javascript:void(0)" class="btn btn-info btn-sm"
                                                        data-toggle="modal" data-target="#viewModal{{ $list->id }}">
                                                        View
                                                    </a>

                                                    @if ($list->status == 0)
                                                        @if ($isHod || $isSuperAdmin || $isAdminStaff)
                                                            {{-- <a href="{{ route('hod.approve', $list->id) }}"
                                                                class="btn btn-success btn-sm">
                                                                HOD Approve
                                                            </a> --}}

                                                            <a href="javascript:void(0)"
                                                                onclick="confirmAction('{{ route('hod.approve', $list->id) }}', 'approve')"
                                                                class="btn btn-success btn-sm">
                                                                HOD Approve
                                                            </a>

                                                            {{-- <a href="{{ route('hod.reject', $list->id) }}"
                                                                class="btn btn-danger btn-sm">
                                                                Reject
                                                            </a> --}}

                                                            <a href="javascript:void(0)"
                                                                onclick="confirmAction('{{ route('hod.reject', $list->id) }}', 'reject')"
                                                                class="btn btn-danger btn-sm">
                                                                Reject
                                                            </a>
                                                        @endif
                                                    @endif
                                                    @if ($list->status == 1)
                                                        @if ($isAdminStaff || $isSuperAdmin)
                                                            {{-- <a href="{{ route('admin.approve', $list->id) }}"
                                                                class="btn btn-success btn-sm">
                                                                HR Approve
                                                            </a> --}}

                                                            <a href="javascript:void(0)"
                                                                onclick="confirmAction('{{ route('admin.approve', $list->id) }}', 'approve')"
                                                                class="btn btn-success btn-sm">
                                                                HR Approve
                                                            </a>

                                                            {{-- <a href="{{ route('admin.reject', $list->id) }}"
                                                                class="btn btn-danger btn-sm">
                                                                Reject
                                                            </a> --}}

                                                            <a href="javascript:void(0)"
                                                                onclick="confirmAction('{{ route('admin.reject', $list->id) }}', 'reject')"
                                                                class="btn btn-danger btn-sm">
                                                                Reject
                                                            </a>
                                                        @endif
                                                    @endif



                                                    {{-- <a href="{{ url('leave/approve/' . $list->id) }}"
                                                        class="btn btn-success btn-sm">Approve</a>

                                                    <a href="{{ url('leave/reject/' . $list->id) }}"
                                                        class="btn btn-danger btn-sm">Reject</a> --}}
                                                </td>

                                                <!-- Action -->
                                                {{-- <td>
                                                    <a href="#" class="btn btn-info btn-sm">View</a>
                                                    <a href="#" class="btn btn-success btn-sm">Approve</a>
                                                    <a href="#" class="btn btn-danger btn-sm">Reject</a>
                                                </td> --}}
                                            </tr>

                                            <div id="viewModal{{ $list->id }}" class="modal fade " tabindex="-1"
                                                role="dialog">
                                                <div class="modal-dialog modal-sm">
                                                    <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header" style="background:#337ab7; color:#fff;">
                                                            <button type="button" class="close"
                                                                data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">Leave Details</h4>
                                                        </div>

                                                        <!-- Modal Body -->
                                                        <div class="modal-body">

                                                            <p><strong>Staff Name:</strong>
                                                                {{ $list->surname }} {{ $list->first_name }}
                                                                {{ $list->othernames }}
                                                            </p>

                                                            <p><strong>Department:</strong> {{ $list->department }}</p>

                                                            {{-- <p><strong>Leave Type:</strong> {{ $list->leaveType }}</p> --}}

                                                            {{-- <p><strong>Reason:</strong> {{ $list->reason_of_leave }}</p> --}}

                                                            <div class="panel panel-default">
                                                                <div class="panel-heading" style="font-weight: bold;">
                                                                    Leave Reason
                                                                </div>
                                                                <div class="panel-body">
                                                                    {{ $list->reason_of_leave }}
                                                                </div>
                                                            </div>

                                                            <p><strong>Start Date:</strong> {{ $list->start_date }}</p>

                                                            <p><strong>End Date:</strong> {{ $list->end_date }}</p>

                                                            <p>
                                                                <strong>Duration:</strong>
                                                                @php
                                                                    $days =
                                                                        \Carbon\Carbon::parse(
                                                                            $list->start_date,
                                                                        )->diffInDays(
                                                                            \Carbon\Carbon::parse($list->end_date),
                                                                        ) + 1;
                                                                @endphp
                                                                {{ $days }} days
                                                            </p>

                                                            <p><strong>Date Applied:</strong>
                                                                {{ \Carbon\Carbon::parse($list->created_at)->format('d M, Y') }}
                                                            </p>

                                                            <p>
                                                                <strong>Status:</strong>
                                                                @if ($list->status == 1)
                                                                    <span class="label label-success">HOD Approved</span>
                                                                @elseif ($list->status == 2)
                                                                    <span class="label label-danger">HR Approved</span>
                                                                @elseif ($list->status == 3)
                                                                    <span class="label label-danger">HOD Reject</span>
                                                                @elseif ($list->status == 4)
                                                                    <span class="label label-danger">HR Reject</span>
                                                                @else
                                                                    <span class="label label-warning">Pending</span>
                                                                @endif
                                                            </p>

                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">Close</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No Record Found!</td>
                                            </tr>
                                        @endforelse
