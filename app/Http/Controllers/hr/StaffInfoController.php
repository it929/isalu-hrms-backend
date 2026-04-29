<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;

use App\Http\Requests;
use Carbon\Carbon;
use Session;
use DateTime;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffInfoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function allStaffInfo(Request $request)
    {
        $query = DB::table('tblper')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->select(
                'tblper.ID',
                'fileNo',
                'surname',
                'first_name',
                'othernames',
                'passport_url',
                'signature_url'
            )
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.employee_type', '!=', 2)
            ->where('tblper.staff_status', 1)
            ->orderBy('surname', 'Asc');

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fileNo', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('othernames', 'like', "%{$search}%");
            });
        }

        // Get all staff first
        $allStaff = $query->get();

        // Apply document/attachment filter
        $filter = $request->get('filter', 'all');

        if ($filter !== 'all') {
            $filteredStaff = $allStaff->filter(function ($staff) use ($filter) {
                $hasDocuments = DB::table('tbleducations')
                    ->where('staffid', $staff->ID)
                    ->orWhere('fileNo', $staff->fileNo)
                    ->exists();

                $hasAttachments = DB::table('tblstaffAttachment')
                    ->where('staffID', $staff->ID)
                    ->exists();

                switch ($filter) {
                    case 'with_documents':
                        return $hasDocuments;
                    case 'without_documents':
                        return !$hasDocuments;
                    case 'with_attachments':
                        return $hasAttachments;
                    case 'without_attachments':
                        return !$hasAttachments;
                    case 'with_both':
                        return $hasDocuments && $hasAttachments;
                    case 'without_both':
                        return !$hasDocuments && !$hasAttachments;
                    default:
                        return true;
                }
            });

            $allStaff = $filteredStaff;
        }

        // Paginate the results
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentItems = $allStaff->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $staffList = new LengthAwarePaginator(
            $currentItems,
            $allStaff->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Load documents and attachments for paginated staff
        $staffIds = $staffList->pluck('ID')->toArray();

        // Initialize counters for staff with documents/attachments
        $staffWithDocuments = 0;
        $staffWithAttachments = 0;

        if (!empty($staffIds)) {
            // Get distinct staff IDs who have education documents
            $staffWithDocuments = DB::table('tbleducations')
                ->whereIn('staffid', $staffIds)
                ->orWhereIn('fileNo', $staffList->pluck('fileNo')->filter()->toArray())
                ->distinct('staffid')
                ->count('staffid');

            // Get distinct staff IDs who have attachments
            $staffWithAttachments = DB::table('tblstaffAttachment')
                ->whereIn('staffID', $staffIds)
                ->distinct('staffID')
                ->count('staffID');

            $educations = DB::table('tbleducations')
                ->whereIn('staffid', $staffIds)
                ->orWhereIn('fileNo', $staffList->pluck('fileNo')->filter()->toArray())
                ->get()
                ->groupBy(function ($item) use ($staffList) {
                    foreach ($staffList as $staff) {
                        if ($item->staffid == $staff->ID) {
                            return $staff->ID;
                        }
                        if ($item->fileNo == $staff->fileNo) {
                            return $staff->ID;
                        }
                    }
                    return $item->staffid;
                });

            $attachments = DB::table('tblstaffAttachment')
                ->whereIn('staffID', $staffIds)
                ->get()
                ->groupBy('staffID');

            foreach ($staffList as $staff) {
                $staff->educations = $educations->get($staff->ID, collect());
                $staff->attachments = $attachments->get($staff->ID, collect());
            }
        }

        // Calculate totals for all staff (system-wide)
        $totalAllStaffWithDocuments = DB::table('tblper')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tbleducations')
                    ->whereRaw('tbleducations.staffid = tblper.ID OR tbleducations.fileNo = tblper.fileNo');
            })
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.employee_type', '!=', 2)
            ->where('tblper.staff_status', 1)
            ->count();

        $totalAllStaffWithAttachments = DB::table('tblper')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tblstaffAttachment')
                    ->whereRaw('tblstaffAttachment.staffID = tblper.ID');
            })
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.employee_type', '!=', 2)
            ->where('tblper.staff_status', 1)
            ->count();

        return view('hr.staff.index', compact(
            'staffList',
            'staffWithDocuments',
            'staffWithAttachments',
            'totalAllStaffWithDocuments',
            'totalAllStaffWithAttachments'
        ));
    }
}
