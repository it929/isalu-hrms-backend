<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignModuleRoleApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve user context from X-User-Id header.
     */
    

    /**
     * Audit log helper mimicking legacy behavior.
     */
    private function logOperation($operation, $userId)
    {
        $ipAddress    = request()->ip();
        $url          = request()->fullUrl();
        $computerName = gethostname();
        $hostName     = $_SERVER['HTTP_HOST'] ?? 'localhost';

        DB::table('role_permission_audit_log')->insert([
            'userID'       => $userId,
            'operation'    => $hostName,
            'ipaddress'    => $operation,
            'url'          => $ipAddress,
            'computername' => $url,
            'hostname'     => $computerName,
            'created_at'   => now(),
        ]);
    }

    /**
     * GET /api/nextjs/assign-module/metadata
     * Load roles list and grouped modules/submodules metadata.
     */
    public function metadata(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            // Fetch roles (max 100 for assignment selection dropdown)
            $roles = DB::table('user_role')
                ->orderBy('rolename', 'asc')
                ->get()
                ->map(function($row) {
                    return [
                        'roleID' => (int) $row->roleID,
                        'rolename' => $row->rolename,
                    ];
                });

            // Fetch submodules joined with modules directly filtering by active status
            $query = DB::table('module')
                ->join('submodule', 'submodule.moduleID', '=', 'module.moduleID')
                ->where('module.active', '=', 1)
                ->where('submodule.status', '=', 1);

            $submodules = $query->select([
                    'submodule.moduleID as modID',
                    'module.moduleID as moduleID',
                    'module.link_type',
                    'submodule.submoduleID',
                    'module.modulename',
                    'submodule.submodulename',
                    'submodule.sub_module_rank'
                ])
                ->orderBy('module.moduleID', 'asc')
                ->orderBy('submodule.sub_module_rank', 'asc')
                ->orderBy('submodule.submoduleID', 'asc')
                ->get()
                ->map(function($row) {
                    return [
                        'moduleID' => (int) $row->moduleID,
                        'modID' => (int) $row->modID,
                        'link_type' => $row->link_type,
                        'submoduleID' => (int) $row->submoduleID,
                        'modulename' => $row->modulename,
                        'submodulename' => $row->submodulename,
                        'sub_module_rank' => (int) $row->sub_module_rank,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'roles' => $roles,
                'submodules' => $submodules,
            ]);
        } catch (\Throwable $th) {
            Log::error('AssignModuleRoleApiController metadata error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/assign-module/assignments/{roleID}
     * Load current submoduleID assignments for a given roleID.
     */
    public function assignments($roleID, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $assignments = DB::table('assign_module_role')
                ->where('roleID', $roleID)
                ->pluck('submoduleID')
                ->map(function($id) {
                    return (int) $id;
                });

            return response()->json([
                'status' => 'success',
                'assignments' => $assignments,
            ]);
        } catch (\Throwable $th) {
            Log::error('AssignModuleRoleApiController assignments error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/assign-module/assign
     * Save submodule assignments for a user role.
     */
    public function assign(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can configure role assignments.'], 403);
            }

            $validated = $request->validate([
                'roleID' => 'required|integer|exists:user_role,roleID',
                'submoduleIDs' => 'nullable|array',
                'submoduleIDs.*' => 'integer|exists:submodule,submoduleID',
            ]);

            $roleID       = (int) $validated['roleID'];
            $submoduleIDs = $validated['submoduleIDs'] ?? [];

            DB::beginTransaction();

            // Clear previous assignments for the role
            DB::table('assign_module_role')->where('roleID', $roleID)->delete();

            // Assign submodules
            foreach ($submoduleIDs as $subMID) {
                $submodule = DB::table('submodule')->where('submoduleID', $subMID)->first();
                if ($submodule) {
                    DB::table('assign_module_role')->insert([
                        'roleID'      => $roleID,
                        'submoduleID' => $subMID,
                        'moduleID'    => $submodule->moduleID,
                        'created_at'  => now(),
                    ]);

                    // Audit Log entry matching legacy system
                    $operation = 'Submodule was assigned to module RoleID, SubModuleIDs, ModuleID: ' . $roleID . ', ' . $subMID . ', ' . $submodule->moduleID . ' respectively';
                    $this->logOperation($operation, $ctx['userId']);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Module Assigned Successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AssignModuleRoleApiController assign error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
