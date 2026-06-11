<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignUserRoleApiController extends Controller
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
     * GET /api/nextjs/user-assign/metadata
     * Load roles and non-technical users lists.
     */
    public function metadata(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            // Fetch roles
            $roles = DB::table('user_role')
                ->orderBy('rolename', 'asc')
                ->get()
                ->map(function($row) {
                    return [
                        'roleID' => (int) $row->roleID,
                        'rolename' => $row->rolename,
                    ];
                });

            // Fetch non-technical users (case-insensitive — supports 'Technical', 'TECHNICAL', etc.)
            $users = DB::table('users')
                ->whereRaw('UPPER(user_type) != ?', ['TECHNICAL'])
                ->orderBy('name', 'asc')
                ->get()
                ->map(function($row) {
                    return [
                        'id' => (int) $row->id,
                        'name' => $row->name,
                        'username' => $row->username,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'roles' => $roles,
                'users' => $users,
            ]);
        } catch (\Throwable $th) {
            Log::error('AssignUserRoleApiController metadata error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/user-assign/assignments
     * List user role assignments (paginated, with search).
     */
    public function assignments(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $perPage = (int)$request->input('perPage', 20);
            $page    = (int)$request->input('page', 1);
            $search  = $request->input('search');

            $query = DB::table('assign_user_role')
                ->join('users', 'users.id', '=', 'assign_user_role.userID')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->select([
                    'assign_user_role.assignuserID',
                    'assign_user_role.userID',
                    'assign_user_role.roleID',
                    'assign_user_role.created_at',
                    'users.name as name',
                    'users.username as username',
                    'user_role.rolename as rolename'
                ])
                ->orderBy('assign_user_role.assignuserID', 'desc');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('users.name', 'like', '%' . $search . '%')
                      ->orWhere('users.username', 'like', '%' . $search . '%')
                      ->orWhere('user_role.rolename', 'like', '%' . $search . '%');
                });
            }

            $total = $query->count();
            $records = $query->paginate($perPage, ['*'], 'page', $page);

            $items = collect($records->items())->map(function($row) {
                return [
                    'assignuserID' => (int) $row->assignuserID,
                    'userID' => (int) $row->userID,
                    'roleID' => (int) $row->roleID,
                    'name' => $row->name,
                    'username' => $row->username,
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
            Log::error('AssignUserRoleApiController assignments error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/user-assign/assign
     * Save/update user role assignment.
     */
    public function assign(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can manage user role assignments.'], 403);
            }

            $validated = $request->validate([
                'userID' => 'required|integer|exists:users,id',
                'roleID' => 'required|integer|exists:user_role,roleID',
            ]);

            $userID = (int) $validated['userID'];
            $roleID = (int) $validated['roleID'];

            DB::beginTransaction();

            // Delete existing assignments for this user (handles duplicate rows cleanly)
            DB::table('assign_user_role')->where('userID', $userID)->delete();

            // Insert fresh assignment
            DB::table('assign_user_role')->insert([
                'userID' => $userID,
                'roleID' => $roleID,
                'created_at' => now()->toDateString(),
            ]);

            // Audit logging
            $operation = 'User was assigned to role with UserID, RoleID: ' . $userID . ', ' . $roleID . ' respectively';
            $this->logOperation($operation, $ctx['userId']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User was Successfully assigned to a role'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AssignUserRoleApiController assign error: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
