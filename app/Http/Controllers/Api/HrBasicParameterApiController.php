<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HrBasicParameterApiController extends Controller
{
    /**
     * Helper to retrieve sole court settings.
     */
    private function getCourtInfo()
    {
        $info = DB::table('tblsole_court')->first();
        if ($info) {
            return $info;
        }

        return (object)[
            'courtstatus' => 1,
            'courtid' => null,
            'divisionstatus' => 1,
            'divisionid' => null
        ];
    }

    /**
     * Resolve the user's filtered court based on their role/user type.
     */
    private function resolveCourtID(Request $request, $courtInfo)
    {
        $court = trim($request->input('court'));
        
        if ($courtInfo->courtstatus == 0) {
            $court = $courtInfo->courtid;
        }

        $userId = $request->header('X-User-Id');
        if ($userId) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user && $user->user_type === 'NONTECHNICAL') {
                $court = $user->courtID;
            }
        }

        return $court;
    }

    /**
     * GET /api/nextjs/hr/basic/section
     */
    public function getDepartments(Request $request)
    {
        try {
            $courtInfo = $this->getCourtInfo();
            $court = $this->resolveCourtID($request, $courtInfo);

            $courtList = DB::table('tbl_court')
                ->where('active', 1)
                ->select('id', 'court_name')
                ->get();

            $query = DB::table('tbldepartment as d')
                ->leftJoin('tbl_court as c', 'd.courtID', '=', 'c.id')
                ->select('d.*', 'c.court_name');

            if ($court) {
                $query->where('d.courtID', $court);
            }

            $departmentList = $query->orderBy('d.id', 'desc')->get()->map(function($dept) {
                $dept->courtID = $dept->courtID ?? null;
                return $dept;
            });

            return response()->json([
                'status'         => 'success',
                'CourtList'      => $courtList,
                'DepartmentList' => $departmentList,
                'CourtInfo'      => $courtInfo,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/section (Add / Edit / Delete)
     */
    public function handleDepartment(Request $request)
    {
        try {
            // Check Delete
            if ($request->has('delcode')) {
                $del = $request->input('delcode');
                DB::table('tbldepartment')->where('id', $del)->delete();
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Department deleted successfully.'
                ]);
            }

            // Check Edit
            if ($request->has('editid')) {
                $editid = $request->input('editid');
                $department = trim($request->input('department'));
                $court = trim($request->input('court'));

                if (empty($department)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Department name is required.'
                    ], 400);
                }

                DB::table('tbldepartment')->where('id', $editid)->update([
                    'department' => $department,
                    'courtID'    => $court ?: null
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Department updated successfully.'
                ]);
            }

            // Check Add
            if ($request->has('add')) {
                $department = trim($request->input('department'));
                $court = trim($request->input('court'));

                if (empty($department)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Department name is required.'
                    ], 400);
                }

                // Check Duplicates
                $exists = DB::table('tbldepartment')
                    ->where('courtID', $court)
                    ->where('department', $department)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Department '$department' already exists under the selected court."
                    ], 400);
                }

                DB::table('tbldepartment')->insert([
                    'courtID'    => $court ?: null,
                    'department' => $department
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Department added successfully.'
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid request operation.'
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/hr/basic/designation
     */
    public function getDesignations(Request $request)
    {
        try {
            $courtInfo = $this->getCourtInfo();
            $court = $this->resolveCourtID($request, $courtInfo);

            $courtList = DB::table('tbl_court')
                ->where('active', 1)
                ->select('id', 'court_name')
                ->get();

            // Load departments belonging to resolved court
            $deptQuery = DB::table('tbldepartment');
            if ($court) {
                $deptQuery->where('courtID', $court);
            }
            $departmentList = $deptQuery->orderBy('department', 'asc')->get();

            // Load designations
            $query = DB::table('tbldesignation as d')
                ->leftJoin('tbldepartment as dept', 'd.departmentId', '=', 'dept.id')
                ->leftJoin('tbl_court as c', 'd.courtId', '=', 'c.id')
                ->select('d.*', 'dept.department', 'c.court_name');

            if ($court) {
                $query->where('d.courtId', $court);
            }

            $designationList = $query->orderBy('d.id', 'desc')->get()->map(function ($item) {
                $item->departmentID = $item->departmentId ?? $item->departmentID ?? null;
                $item->courtID = $item->courtId ?? $item->courtID ?? null;
                return $item;
            });

            return response()->json([
                'status'          => 'success',
                'CourtList'       => $courtList,
                'DepartmentList'  => $departmentList,
                'DesignationList' => $designationList,
                'CourtInfo'       => $courtInfo,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/designation (Add new designation)
     */
    public function handleDesignation(Request $request)
    {
        try {
            $designation = strtoupper(trim($request->input('designation')));
            $department = trim($request->input('department'));
            $court = trim($request->input('court'));

            if (empty($designation) || empty($department)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Designation name and Department are required.'
                ], 400);
            }

            DB::table('tbldesignation')->insert([
                'courtId'      => $court ?: null,
                'departmentId' => $department,
                'designation'  => $designation
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Designation added successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/designation/edit
     */
    public function updateDesignation(Request $request)
    {
        try {
            $postID = trim($request->input('PostID'));
            $designation = strtoupper(trim($request->input('designation')));
            $deptID = trim($request->input('DeptID'));
            $courtID = trim($request->input('CourtID'));

            if (empty($postID) || empty($designation) || empty($deptID)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid designation details provided.'
                ], 400);
            }

            DB::table('tbldesignation')
                ->where('id', $postID)
                ->update([
                    'designation'  => $designation,
                    'departmentId' => $deptID,
                    'courtId'      => $courtID ?: null
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Designation updated successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/designation/delete
     */
    public function deleteDesignation(Request $request)
    {
        try {
            $postID = trim($request->input('PostID'));

            if (empty($postID)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Designation ID is required.'
                ], 400);
            }

            DB::table('tbldesignation')->where('id', $postID)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Designation deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    //  UNIT SETUP
    // =========================================================================

    /**
     * GET /api/nextjs/hr/basic/unit
     */
    public function getUnits(Request $request)
    {
        try {
            $departmentList = DB::table('tbldepartment')->orderBy('department', 'asc')->get();

            $query = DB::table('tblunits as d')
                ->leftJoin('tbldepartment as dept', 'd.departmentID', '=', 'dept.id')
                ->select('d.*', 'dept.department');

            $unitList = $query->orderBy('d.unitID', 'desc')->get();

            return response()->json([
                'status'         => 'success',
                'DepartmentList' => $departmentList,
                'UnitList'       => $unitList,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/unit (Add)
     */
    public function handleUnit(Request $request)
    {
        try {
            $unit = strtoupper(trim($request->input('unit')));
            $department = trim($request->input('department'));

            if (empty($unit) || empty($department)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unit name and Department are required.'
                ], 400);
            }

            // Check duplicate
            $exists = DB::table('tblunits')
                ->where('departmentID', $department)
                ->whereRaw('UPPER(unit) = ?', [$unit])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Unit '$unit' already exists under the selected department."
                ], 400);
            }

            DB::table('tblunits')->insert([
                'departmentID' => $department,
                'unit'         => $unit
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Unit added successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/unit/edit
     */
    public function updateUnit(Request $request)
    {
        try {
            $postID = trim($request->input('PostID'));
            $unit = strtoupper(trim($request->input('unit')));
            $deptID = trim($request->input('DeptID'));

            if (empty($postID) || empty($unit) || empty($deptID)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid unit details provided.'
                ], 400);
            }

            DB::table('tblunits')
                ->where('unitID', $postID)
                ->update([
                    'unit'         => $unit,
                    'departmentID' => $deptID
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Unit updated successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/basic/unit/delete
     */
    public function deleteUnit(Request $request)
    {
        try {
            $postID = trim($request->input('PostID'));

            if (empty($postID)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unit ID is required.'
                ], 400);
            }

            DB::table('tblunits')->where('unitID', $postID)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Unit deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    //  LGA COVERED
    // =========================================================================

    /**
     * GET /api/nextjs/hr/lga/covered
     */
    public function getLgaCovered(Request $request)
    {
        try {
            $stateID = $request->input('stateID');

            $states = DB::table('tblstates')->orderBy('State', 'asc')->get();

            $lgaList = collect([]);
            $stateName = null;

            if ($stateID) {
                $lgaList = DB::table('lga')->where('stateid', $stateID)->orderBy('lga', 'asc')->get();
                $stateName = DB::table('tblstates')->where('StateID', $stateID)->value('State');
            }

            return response()->json([
                'status'    => 'success',
                'StateList' => $states,
                'LgaList'   => $lgaList,
                'StateID'   => $stateID,
                'StateName' => $stateName,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/lga/covered/add
     */
    public function storeLga(Request $request)
    {
        try {
            $stateID = trim($request->input('state'));
            $lga = trim($request->input('localGovernmentArea'));

            if (empty($stateID) || empty($lga)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'State and LGA name are required.'
                ], 400);
            }

            // Check duplicate
            $exists = DB::table('lga')
                ->where('lga', $lga)
                ->where('stateId', $stateID)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Local Government Area already exists under this state.'
                ], 400);
            }

            DB::table('lga')->insert([
                'stateid' => $stateID,
                'lga'     => $lga
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Local Government Area successfully added.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/lga/covered/edit
     */
    public function updateLga(Request $request)
    {
        try {
            $lgaId = trim($request->input('lgaid'));
            $lgaName = trim($request->input('lgaChange'));

            if (empty($lgaId) || empty($lgaName)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'LGA ID and name are required.'
                ], 400);
            }

            DB::table('lga')->where('lgaId', $lgaId)->update(['lga' => $lgaName]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Local Government Area successfully updated.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/lga/covered/remove/{lgaId}
     */
    public function deleteLga(Request $request, $lgaId)
    {
        try {
            if (empty($lgaId)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'LGA ID is required.'
                ], 400);
            }

            // Check if staff is assigned to this LGA
            $lgaExists = DB::table('tblper')->where('lgaID', $lgaId)->exists();

            if ($lgaExists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot delete LGA because a staff is still assigned to it.'
                ], 400);
            }

            DB::table('lga')->where('lgaId', $lgaId)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Local Government Area successfully deleted.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
