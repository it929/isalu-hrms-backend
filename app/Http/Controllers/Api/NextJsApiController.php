<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NextJsApiController extends Controller
{
    /**
     * Authenticate user from Next.js
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Only enforce default password warning for staff users
            $mustChangePassword = ($user->user_type === 'staff') && \Illuminate\Support\Facades\Hash::check('12345', $user->password);
            
            $userData = $user->toArray();
            $userData['must_change_password'] = $mustChangePassword;

            // Fetch actual role name from database
            $role = \DB::table('assign_user_role')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->where('assign_user_role.userID', $user->id)
                ->first();

            $roleName = $role ? $role->rolename : 'Staff';

            return response()->json([
                'status' => 'success',
                'user' => $userData,
                'role' => ['name' => $roleName],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Get technical users
     */
    public function getTechnicalUsers()
    {
        // Mocking for now to match Next.js UI, later join with roles
        $users = User::select('id', 'name', 'email')->take(10)->get()->map(function($user) {
            $user->role = 'System Admin'; // Placeholder
            $user->status = 'Active';
            return $user;
        });

        return response()->json(['users' => $users]);
    }

    /**
     * Get Roles and Modules
     */
    public function getRolesAndModules()
    {
        return response()->json([
            'roles' => ['Super Admin', 'Admin', 'Salary Supervisor', 'HR Manager'],
            'modules' => ['HR Module', 'Payroll Module', 'Procurement', 'Funds Management']
        ]);
    }

    /**
     * Get HOD assignments
     */
    public function getHodAssignments()
    {
        $departments = \DB::table('tbldepartment')->get();

        $hods = \DB::table('tbldepartment as d')
            ->leftJoin('tblper as p', 'p.departmentID', '=', 'd.id')
            ->where('p.is_hod', 1)
            ->select(
                'd.id as department_id',
                'd.department as department_name',
                'p.ID as staff_id',
                'p.surname',
                'p.first_name',
                'p.othernames'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'departments' => $departments,
            'hods' => $hods
        ]);
    }

    /**
     * Get staff by department
     */
    public function getStaffByDepartment($dept)
    {
        $staff = \DB::table('tblper')
            ->where('departmentID', $dept)
            ->select('ID', 'surname', 'first_name', 'othernames')
            ->get();

        return response()->json([
            'status' => 'success',
            'staff' => $staff
        ]);
    }

    /**
     * Assign HOD
     */
    public function assignHod(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'user_id' => 'required'
        ]);

        \DB::beginTransaction();
        try {
            // 1. Remove old HOD for this department
            \DB::table('tblper')
                ->where('departmentID', $request->department_id)
                ->update(['is_hod' => 0]);

            // 2. Assign new HOD
            \DB::table('tblper')
                ->where('ID', $request->user_id)
                ->update(['is_hod' => 1]);

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Head of Department assigned successfully.'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to assign HOD: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get Dashboard Stats
     */
    public function getDashboardStats()
    {
        $totalStaff = \DB::table('tblper')->count();
        $maleStaff = \DB::table('tblper')->where('gender', 'Male')->count();
        $femaleStaff = \DB::table('tblper')->where('gender', 'Female')->count();

        // Get count of staff in each department (including departments with 0 staff)
        $departments = \DB::table('tbldepartment')
            ->leftJoin('tblper', 'tblper.departmentID', '=', 'tbldepartment.id')
            ->select('tbldepartment.department as name', \DB::raw('count(tblper.ID) as value'))
            ->groupBy('tbldepartment.id', 'tbldepartment.department')
            ->get();
        
        // Mocking other stats for now as they require leave/task tables
        return response()->json([
            'stats' => [
                ['label' => 'Total Employees', 'value' => number_format($totalStaff), 'icon' => 'Users', 'color' => 'var(--primary)'],
                ['label' => 'Male Staff', 'value' => number_format($maleStaff), 'icon' => 'Users', 'color' => '#10b981'],
                ['label' => 'Female Staff', 'value' => number_format($femaleStaff), 'icon' => '#f59e0b', 'color' => '#f59e0b'],
                ['label' => 'Open Positions', 'value' => '12', 'icon' => 'Briefcase', 'color' => '#8b5cf6'],
            ],
            'departments' => $departments
        ]);
    }

    /**
     * GET /api/nextjs/sidebar-links
     * Returns dynamic sidebar modules and submodules links matching Laravel Blade logic.
     */
    public function getSidebarLinks(Request $request)
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            // Check if superadmin/technical user
            $isTechnical = \DB::table('assign_user_role')
                ->where('userID', $userId)
                ->where('roleID', 1) // roleID 1 is Super Admin
                ->exists();

            // Fetch assigned modules for the user's role
            $modules = \DB::table('assign_user_role')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->join('assign_module_role', 'assign_module_role.roleID', '=', 'assign_user_role.roleID')
                ->join('module', 'module.moduleID', '=', 'assign_module_role.moduleID')
                ->where('assign_user_role.userID', '=', $userId)
                ->whereRaw('module.moduleID = assign_module_role.moduleID')
                ->whereRaw('user_role.roleID = assign_user_role.roleID')
                ->distinct()
                ->select('module.modulename', 'module.moduleID', 'module.link_type')
                ->orderBy('module.link_type', 'ASC')
                ->orderBy('module.modulename', 'ASC')
                ->get();

            $sidebarData = [];

            foreach ($modules as $module) {
                // Fetch assigned submodules for this module under the user's role
                $submodules = \DB::table('assign_user_role')
                    ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                    ->join('assign_module_role', 'assign_module_role.roleID', '=', 'assign_user_role.roleID')
                    ->join('submodule', 'submodule.submoduleID', '=', 'assign_module_role.submoduleID')
                    ->where('assign_user_role.userID', '=', $userId)
                    ->where('submodule.moduleID', '=', $module->moduleID)
                    ->distinct()
                    ->orderBy('submodule.sub_module_rank', 'ASC')
                    ->orderBy('submodule.submodulename', 'ASC')
                    ->get(['submodule.submoduleID', 'submodule.submodulename', 'submodule.route']);

                if ($submodules->count() > 0) {
                    $sidebarData[] = [
                        'moduleID' => $module->moduleID,
                        'modulename' => $module->modulename,
                        'link_type' => $module->link_type,
                        'submodules' => $submodules->map(function ($s) {
                            return [
                                'id' => $s->submoduleID ?? null,
                                'name' => $s->submodulename,
                                'path' => '/' . ltrim($s->route, '/'),
                            ];
                        })
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'is_admin' => $isTechnical,
                'sidebar' => $sidebarData
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update user account details (username and password).
     */
    public function updateAccount(Request $request)
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'userName'              => 'required|string|min:3',
            'password'              => 'required|confirmed|min:5',
            'password_confirmation' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent reusing default password
        if ($request->password === '12345') {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot use the default password. Choose a new one.'
            ], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        // Validate uniqueness of username if it changed
        if ($user->username !== $request->userName) {
            $usernameExists = User::where('username', $request->userName)->where('id', '!=', $userId)->exists();
            if ($usernameExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The username has already been taken.'
                ], 422);
            }
        }

        $user->password = bcrypt($request->password);
        $user->username = $request->userName;
        $user->first_login = 1;
        $user->save();

        $userData = $user->toArray();
        $userData['must_change_password'] = false;

        return response()->json([
            'status' => 'success',
            'message' => 'Your account details were successfully updated!',
            'user' => $userData
        ]);
    }

    /**
     * Handle forgot password request from Next.js.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'staffId' => 'required|string'
        ]);

        $username = trim($request->staffId);

        // Check if there is a record in the users table matching the username
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'error' => "$username does not exist"
            ], 404);
        }

        $userid = $user->id;
        $email = $user->email;
        $staffname = $user->name ?: $user->username;

        // Generate random password token
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $randomPass = implode('', $pass);

        $token = md5($user->username) . md5($randomPass);

        \DB::table('users')->where('id', '=', $userid)->update([
            'resettoken' => $token,
            'token_status' => '1'
        ]);

        $to = $email;
        $subject = "Password Reset";
        $sender = config('mail.from.address') ?: "info@mbrcomputers.net";

        $header = "From:" . $sender . "\r\n";
        $header .= "MIME-Version: 1.0 \r\n";
        $header .= "Content-type: text/html \r\n";

        // Determine base URL for Next.js frontend dynamically or fallback to configuration
        $host = $request->getHost();
        $scheme = $request->isSecure() ? 'https' : 'http';
        $frontendUrl = env('NEXTJS_FRONTEND_URL', 'http://localhost:3000');
        
        if ($host === '127.0.0.1' || $host === 'localhost') {
            $resetUrl = "$scheme://$host:3000/password-reset/resets/$token";
        } else {
            $resetUrl = rtrim($frontendUrl, '/') . "/password-reset/resets/$token";
        }

        $message = "Dear $staffname, <br> Kindly click on <a href='$resetUrl'>here</a> to change your password.";
        
        try {
            // Send email using Laravel's Mail facade to respect .env mail settings
            \Illuminate\Support\Facades\Mail::html($message, function ($mail) use ($to, $subject, $sender) {
                $mail->to($to)
                     ->subject($subject)
                     ->from($sender);
            });
        } catch (\Throwable $e) {
            // Log the mail sending failure and details to laravel.log so it can be verified locally
            \Illuminate\Support\Facades\Log::error("Failed to send password reset email via Mailer: " . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
                'message' => $message
            ]);
            // Fall back to php mail()
            @mail($to, $subject, $message, "From:" . $sender . "\r\n" . "MIME-Version: 1.0\r\n" . "Content-type: text/html\r\n");
        }

        return response()->json([
            'status' => 'success',
            'success' => "Dear $staffname, a message has been sent to your email address: $email for password reset. Kindly check your email."
        ]);
    }

    /**
     * Reset user password using token from Next.js.
     */
    public function resetPassword(Request $request, $token)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => 'required|confirmed|min:5',
            'password_confirmation' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('resettoken', $token)
            ->where('token_status', '1')
            ->where('resettoken', '!=', '')
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'error' => 'The password reset token is invalid or has expired.'
            ], 422);
        }

        $user->password = bcrypt($request->password);
        $user->token_status = '0';
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been successfully reset!'
        ]);
    }
}
