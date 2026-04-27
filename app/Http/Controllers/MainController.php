<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use App\Http\Requests;
// use DB;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function userArea()
    {
        $userID = Auth::id();

        // Get user's role ID using helper
        $userRoleID = \App\Helpers\DashboardHelper::getUserRoleID();

        // Get user's division
        $userDivision = DB::table('users')
            ->join('tbldivision', 'users.divisionID', '=', 'tbldivision.divisionID')
            ->where('users.id', $userID)
            ->select('tbldivision.division')
            ->first();

        $divisionName = $userDivision ? $userDivision->division : 'No Division Assigned';

        // Get widgets assigned to user's role
        $assignedWidgets = \App\Helpers\DashboardHelper::getUserWidgets();



        // dd($assignedWidgets);

        // Fetch data for dashboard cards
        $dashboardData = $this->getDashboardData($assignedWidgets);

        // dd($dashboardData);

        // Merge all data
        $data = array_merge([
            'divisionName' => $divisionName,
            'assignedWidgets' => $assignedWidgets,
            'userRoleID' => $userRoleID,
            'userRoleName' => \App\Helpers\DashboardHelper::getUserRoleName(),
            'hasAnyWidget' => \App\Helpers\DashboardHelper::hasAnyWidget()
        ], $dashboardData);

        return view('dashboard.userArea', $data);
    }

    private function getDashboardData($assignedWidgets = [])
    {
        $data = [];

        // Initialize counters
        $data['activeStaffCount'] = 0;
        $data['inactiveStaffCount'] = 0;
        // $data['justicesCount'] = 0;
        $data['contractorsCount'] = 0;
        $data['totalStaff'] = 0;
        $data['totalStaffCount'] = 0;
        $data['totalVouchers'] = 0;
        $data['processedVouchers'] = 0;
        $data['activeStaffPercentage'] = 0;
        $data['inactiveStaffPercentage'] = 0;
        $data['processedPercentage'] = 0;
        $data['currentMonthVouchers'] = 0;

        // Check if user has any widgets
        if (empty($assignedWidgets)) {
            return $data;
        }

        // Calculate data only for assigned widgets
        if (in_array('Total Staff', $assignedWidgets)) {
            $data['totalStaffCount'] = DB::table('tblper')
                // ->where('staff_status', 1)
                ->where(function ($query) {
                    $query->where('employee_type', '!=', 2)
                        ->orWhereNull('employee_type');
                })
                ->count();
        }
        if (in_array('Active Staff', $assignedWidgets)) {
            $data['activeStaffCount'] = DB::table('tblper')
                ->where('staff_status', 1)
                ->where(function ($query) {
                    $query->where('employee_type', '!=', 2)
                        ->orWhereNull('employee_type');
                })
                ->count();
        }

        if (in_array('Inactive Staff', $assignedWidgets)) {
            $data['inactiveStaffCount'] = DB::table('tblper')
                ->where('staff_status', 0)
                ->count();
        }

        // if (in_array('Justices', $assignedWidgets)) {
        //     $data['justicesCount'] = DB::table('tblper')
        //         ->where('employee_type', 2)
        //         ->count();
        // }

        if (in_array('Contractors', $assignedWidgets)) {
            $data['contractorsCount'] = DB::table('tblcontractor')
                ->count();
        }

        if (in_array('Total Vouchers Raised', $assignedWidgets)) {
            $data['totalVouchers'] = DB::table('tblpaymentTransaction')
                ->where(function ($query) {
                    $query->where('is_special', '!=', 1)
                        ->orWhereNull('is_special');
                })
                ->count();
        }

        if (in_array('Processed Vouchers', $assignedWidgets)) {
            $data['processedVouchers'] = DB::table('tblpaymentTransaction')
                ->where('status', '>=', 6)
                ->where(function ($query) {
                    $query->where('is_special', '!=', 1)
                        ->orWhereNull('is_special');
                })
                ->count();
        }

        // Calculate total staff if needed for charts
        $hasStaffChart = in_array('Staff Distribution', $assignedWidgets) ||
            in_array('Workforce Composition', $assignedWidgets);

        if ($hasStaffChart) {
            $data['totalStaff'] = DB::table('tblper')
                ->where(function ($query) {
                    $query->where('employee_type', '!=', 2)
                        ->orWhereNull('employee_type');
                })
                ->count();
        }

        // Calculate percentages
        if ($data['totalStaff'] > 0) {
            $data['activeStaffPercentage'] = round(($data['activeStaffCount'] / $data['totalStaff']) * 100);
            $data['inactiveStaffPercentage'] = round(($data['inactiveStaffCount'] / $data['totalStaff']) * 100);
        }

        if ($data['totalVouchers'] > 0) {
            $data['processedPercentage'] = round(($data['processedVouchers'] / $data['totalVouchers']) * 100);
        }

        return $data;
    }


    public function index(Request $request)
    {
        // Get all roles from user_role table
        $roles = DB::table('user_role')
            ->orderBy('rolename')
            ->get();

        // Get all widgets from widget table
        $widgets = DB::table('widget')
            ->orderBy('name', 'asc')
            ->get();

        // Get current assignments with role and widget names
        $assignments = DB::table('role_widget as rw')
            ->join('user_role as ur', 'rw.role_id', '=', 'ur.roleID')
            ->join('widget as w', 'rw.widget_id', '=', 'w.id')
            ->select('rw.*', 'ur.rolename', 'w.name as widget_name')
            ->orderBy('ur.rolename')
            ->get();

        // Initialize variables that the layout expects
        $warning = session('warning', '');
        $success = session('success', '');

        $CourtInfo = (object) [
            'courtstatus' => 1,
            'courtid' => null,
            'divisionstatus' => 1,
            'divisionid' => null,
        ];
        $CourtList = collect([]);
        $DepartmentList = collect([]);
        $DesignationList = collect([]);

        return view('dashboard.role-widget.form', compact(
            'roles',
            'widgets',
            'assignments',
            'warning',
            'success',
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:user_role,roleID',
            'widgets' => 'array',
            'widgets.*' => 'exists:widget,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $roleId = $request->role_id;
        $selectedWidgets = $request->widgets ?? [];

        // Begin transaction
        DB::beginTransaction();

        try {
            // Delete existing assignments for this role
            DB::table('role_widget')
                ->where('role_id', $roleId)
                ->delete();

            // Insert new assignments
            $insertData = [];
            $now = now()->toDateString();

            foreach ($selectedWidgets as $widgetId) {
                $insertData[] = [
                    'role_id' => $roleId,
                    'widget_id' => $widgetId,
                    'created_at' => $now
                ];
            }

            if (!empty($insertData)) {
                DB::table('role_widget')->insert($insertData);
            }

            DB::commit();

            return redirect()->route('role-widget.form')->with('success', 'Widgets assigned successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error assigning widgets: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function add_27_04_26(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:widget,name',
        ]);

        DB::table('widget')->insert([
            'name'       => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Widget added successfully!');
    }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:widget,name',
        ]);

        $id = DB::table('widget')->insertGetId([
            'name'       => trim($request->name),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'widget' => [
                'id' => $id,
                'name' => $request->name
            ]
        ]);
    }


    public function getWidgetsByRole($roleId)
    {
        // Get widget IDs already assigned to this role
        $assignedWidgets = DB::table('role_widget')
            ->where('role_id', $roleId)
            ->pluck('widget_id')
            ->toArray();

        return response()->json($assignedWidgets);
    }


    public function destroy($id)
    {
        try {
            // Check if assignment exists
            $assignment = DB::table('role_widget')->where('id', $id)->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found'
                ], 404);
            }

            $deleted = DB::table('role_widget')
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment removed successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete assignment'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing assignment: ' . $e->getMessage()
            ], 500);
        }
    }
}
