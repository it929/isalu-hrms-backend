<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRoleApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve user context from X-User-Id header.
     */
    

    /**
     * Audit log helper mimicking the legacy behavior.
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
     * GET /api/nextjs/roles
     * List user roles (paginated).
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $perPage = (int)$request->input('perPage', 10);
            $page    = (int)$request->input('page', 1);

            $query = DB::table('user_role')->orderBy('rolename', 'asc');

            $total = $query->count();
            $records = $query->paginate($perPage, ['*'], 'page', $page);

            // Cast fields consistently
            $items = collect($records->items())->map(function($row) {
                return [
                    'roleID' => (int) $row->roleID,
                    'rolename' => $row->rolename,
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
            Log::error('UserRoleApiController index error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/roles
     * Create a new role.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage roles.'], 403);
            }

            $validated = $request->validate([
                'roleName' => 'required|regex:/^[a-zA-Z0-9,.!?\-)\( ]*$/|max:1000|unique:user_role,rolename',
            ]);

            $roleName = trim($validated['roleName']);

            DB::table('user_role')->insert([
                'rolename' => $roleName,
                'created_at' => now(),
            ]);

            $this->logOperation('New Role was created with Role name: ' . $roleName, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'New Role Created Successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('UserRoleApiController store error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/roles/{id}
     * Get a specific role.
     */
    public function show($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $role = DB::table('user_role')->where('roleID', $id)->first();
            if (!$role) {
                return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'roleID' => (int) $role->roleID,
                    'rolename' => $role->rolename,
                    'created_at' => $role->created_at,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('UserRoleApiController show error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/roles/update/{id}
     * Update an existing role.
     */
    public function update($id, Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage roles.'], 403);
            }

            $validated = $request->validate([
                'roleName' => 'required|regex:/^[a-zA-Z0-9,.!?\-)\( ]*$/|max:1000',
            ]);

            $roleName = trim($validated['roleName']);

            // Manual unique check excluding current ID
            $exists = DB::table('user_role')
                ->where('rolename', $roleName)
                ->where('roleID', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The role name has already been taken.'
                ], 422);
            }

            $updated = DB::table('user_role')
                ->where('roleID', $id)
                ->update(['rolename' => $roleName]);

            if (!$updated) {
                $roleExists = DB::table('user_role')->where('roleID', $id)->exists();
                if (!$roleExists) {
                    return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
                }
            }

            $this->logOperation('Role was updated with RoleID: ' . $id, $ctx['userId']);

            return response()->json([
                'status' => 'success',
                'message' => 'Role Successfully Updated'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('UserRoleApiController update error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
