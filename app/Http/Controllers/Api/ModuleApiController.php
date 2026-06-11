<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleApiController extends Controller
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
     * GET /api/nextjs/modules
     * List modules (paginated).
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $perPage = (int)$request->input('perPage', 50);
            $page    = (int)$request->input('page', 1);

            $query = DB::table('module')->orderBy('module_rank', 'asc');

            $total = $query->count();
            $records = $query->paginate($perPage, ['*'], 'page', $page);

            $items = collect($records->items())->map(function($row) {
                return [
                    'moduleID' => (int) $row->moduleID,
                    'modulename' => $row->modulename,
                    'module_rank' => (int) $row->module_rank,
                    'link_type' => $row->link_type,
                    'created_at' => $row->created_at,
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
            Log::error('ModuleApiController index error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/modules
     * Create a new module.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage modules.'], 403);
            }

            $validated = $request->validate([
                'moduleName' => 'required|regex:/^[a-zA-Z0-9,.!?\-)\( ]*$/|max:1000|unique:module,modulename',
                'rank' => 'required|integer',
                'link_type' => 'required|string|in:HR,FINANCE,PAYROLL,PROCUREMENT,STORE',
            ]);

            $moduleName = trim($validated['moduleName']);
            $rank       = (int) $validated['rank'];
            $linkType   = trim($validated['link_type']);

            DB::table('module')->insert([
                'modulename'  => $moduleName,
                'module_rank' => $rank,
                'link_type'   => $linkType,
                'created_at'  => now(),
            ]);

            $this->logOperation('New Module was created with Module name: ' . $moduleName, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'Module Created Successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('ModuleApiController store error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/modules/{id}
     * Get details for specific module.
     */
    public function show($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $module = DB::table('module')->where('moduleID', $id)->first();
            if (!$module) {
                return response()->json(['status' => 'error', 'message' => 'Module not found.'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'moduleID' => (int) $module->moduleID,
                    'modulename' => $module->modulename,
                    'module_rank' => (int) $module->module_rank,
                    'link_type' => $module->link_type,
                    'created_at' => $module->created_at,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('ModuleApiController show error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/modules/update/{id}
     * Update an existing module.
     */
    public function update($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage modules.'], 403);
            }

            $validated = $request->validate([
                'name' => 'required|regex:/^[a-zA-Z0-9,.!?\-)\( ]*$/|max:1000',
                'rank' => 'required|integer',
                'link_type' => 'required|string|in:HR,FINANCE,PAYROLL,PROCUREMENT,STORE',
            ]);

            $moduleName = trim($validated['name']);
            $rank       = (int) $validated['rank'];
            $linkType   = trim($validated['link_type']);

            // Manual uniqueness check excluding current ID
            $exists = DB::table('module')
                ->where('modulename', $moduleName)
                ->where('moduleID', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The module name has already been taken.'
                ], 422);
            }

            $updated = DB::table('module')
                ->where('moduleID', $id)
                ->update([
                    'modulename'  => $moduleName,
                    'module_rank' => $rank,
                    'link_type'   => $linkType,
                ]);

            if (!$updated) {
                $moduleExists = DB::table('module')->where('moduleID', $id)->exists();
                if (!$moduleExists) {
                    return response()->json(['status' => 'error', 'message' => 'Module not found.'], 404);
                }
            }

            $this->logOperation('Module was updated with module ID: ' . $id, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'Module Successfully Updated'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('ModuleApiController update error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
