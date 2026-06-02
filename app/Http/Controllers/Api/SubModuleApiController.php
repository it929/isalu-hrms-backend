<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubModuleApiController extends Controller
{
    /**
     * Resolve user context from X-User-Id header.
     */
    private function getUserContext(Request $request): ?array
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return null;
        }

        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 1)
            ->exists();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

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
     * GET /api/nextjs/submodules
     * List submodules (paginated, optionally filtered by moduleID).
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $perPage  = (int)$request->input('perPage', 10);
            $page     = (int)$request->input('page', 1);
            $moduleID = $request->input('moduleID');

            $query = DB::table('submodule')
                ->join('module', 'module.moduleID', '=', 'submodule.moduleID')
                ->select([
                    'submodule.submoduleID',
                    'submodule.moduleID',
                    'submodule.submodulename',
                    'submodule.route',
                    'submodule.sub_module_rank',
                    'submodule.created_at',
                    'module.modulename'
                ]);

            if ($moduleID) {
                $query->where('submodule.moduleID', '=', $moduleID);
            }

            $query->orderBy('submodule.sub_module_rank', 'asc')
                  ->orderBy('submodule.submoduleID', 'asc');

            $total = $query->count();
            $records = $query->paginate($perPage, ['*'], 'page', $page);

            $items = collect($records->items())->map(function($row) {
                return [
                    'submoduleID' => (int) $row->submoduleID,
                    'moduleID' => (int) $row->moduleID,
                    'submodulename' => $row->submodulename,
                    'route' => $row->route,
                    'sub_module_rank' => (int) $row->sub_module_rank,
                    'created_at' => $row->created_at,
                    'modulename' => $row->modulename,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $items,
                'total' => $total,
                'perPage' => $perPage,
                'page' => $page,
                'lastPage' => $records->lastPage(),
            ]);
        } catch (\Throwable $th) {
            Log::error('SubModuleApiController index error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/submodules
     * Create a new submodule.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage submodules.'], 403);
            }

            $validated = $request->validate([
                'moduleID' => 'required|integer|exists:module,moduleID',
                'subModuleName' => 'required|regex:/^[a-zA-Z0-9,.!?\-)\( ]*$/|max:1000|unique:submodule,submodulename',
                'route' => 'required|string',
                'rank' => 'nullable|integer',
            ]);

            $moduleID      = (int) $validated['moduleID'];
            $subModuleName = trim($validated['subModuleName']);
            $route         = ltrim(rtrim($validated['route'], "/"), "/");
            $rank          = (int) ($validated['rank'] ?? 0);

            DB::table('submodule')->insert([
                'moduleID' => $moduleID,
                'submodulename' => $subModuleName,
                'route' => $route,
                'sub_module_rank' => $rank,
                'created_at' => now(),
            ]);

            $this->logOperation('New Sub-Module was created with Submodule: ' . $subModuleName, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'Sub Module Created Successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('SubModuleApiController store error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/submodules/{id}
     * Get details for specific submodule.
     */
    public function show($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $submodule = DB::table('submodule')
                ->join('module', 'module.moduleID', '=', 'submodule.moduleID')
                ->select([
                    'submodule.submoduleID',
                    'submodule.moduleID',
                    'submodule.submodulename',
                    'submodule.route',
                    'submodule.sub_module_rank',
                    'submodule.created_at',
                    'module.modulename'
                ])
                ->where('submodule.submoduleID', $id)
                ->first();

            if (!$submodule) {
                return response()->json(['status' => 'error', 'message' => 'Submodule not found.'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'submoduleID' => (int) $submodule->submoduleID,
                    'moduleID' => (int) $submodule->moduleID,
                    'submodulename' => $submodule->submodulename,
                    'route' => $submodule->route,
                    'sub_module_rank' => (int) $submodule->sub_module_rank,
                    'created_at' => $submodule->created_at,
                    'modulename' => $submodule->modulename,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('SubModuleApiController show error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/submodules/update/{id}
     * Update an existing submodule.
     */
    public function update($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage submodules.'], 403);
            }

            $validated = $request->validate([
                'moduleID' => 'required|integer|exists:module,moduleID',
                'subModuleName' => 'required|regex:/^[a-zA-Z0-9,.!?\-)\( ]*$/|max:1000',
                'route' => 'required|string',
                'rank' => 'required|integer',
            ]);

            $moduleID      = (int) $validated['moduleID'];
            $subModuleName = trim($validated['subModuleName']);
            $route         = ltrim(rtrim($validated['route'], "/"), "/");
            $rank          = (int) $validated['rank'];

            // Unique check excluding current ID
            $exists = DB::table('submodule')
                ->where('submodulename', $subModuleName)
                ->where('submoduleID', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The submodule name has already been taken.'
                ], 422);
            }

            $updated = DB::table('submodule')
                ->where('submoduleID', $id)
                ->update([
                    'moduleID' => $moduleID,
                    'submodulename' => $subModuleName,
                    'route' => $route,
                    'sub_module_rank' => $rank,
                ]);

            if (!$updated) {
                $submoduleExists = DB::table('submodule')->where('submoduleID', $id)->exists();
                if (!$submoduleExists) {
                    return response()->json(['status' => 'error', 'message' => 'Submodule not found.'], 404);
                }
            }

            $this->logOperation('Sub-module was updated with ID: ' . $id, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'SubModule Successfully Updated'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('SubModuleApiController update error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/submodules/delete/{id}
     * Delete an existing submodule.
     */
    public function destroy($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage submodules.'], 403);
            }

            $submodule = DB::table('submodule')->where('submoduleID', $id)->first();
            if (!$submodule) {
                return response()->json(['status' => 'error', 'message' => 'Submodule not found.'], 404);
            }

            DB::table('submodule')->where('submoduleID', $id)->delete();
            DB::table('assign_module_role')->where('submoduleID', $id)->delete();

            $this->logOperation('Sub-module was deleted with ID: ' . $id, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'Sub-module Successfully Deleted'
            ]);
        } catch (\Throwable $th) {
            Log::error('SubModuleApiController destroy error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
