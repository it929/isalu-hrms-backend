<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\ResolveUserContextTrait;

class NextJsApiController extends Controller
{
    use ResolveUserContextTrait;
    /**
     * Authenticate user from Next.js
     */
    public function login(Request $request)
    {
        $username = trim($request->input('username'));
        $password = $request->input('password');

        // 1. Try finding user by username directly
        $user = \App\Models\User::where('username', $username)->first();

        // 2. If not found, look up the staff by fileNo (PF Number) in tblper
        if (!$user) {
            $staff = \DB::table('tblper')->where('fileNo', $username)->first();
            if ($staff) {
                if ($staff->UserID) {
                    $user = \App\Models\User::find($staff->UserID);
                }
                if (!$user) {
                    $user = $this->autoCreateUserForStaff($staff);
                }
            }
        }

        // 3. If still not found, try to look up by ID
        if (!$user && is_numeric($username)) {
            $staff = \DB::table('tblper')->where('ID', (int)$username)->first();
            if ($staff) {
                if ($staff->UserID) {
                    $user = \App\Models\User::find($staff->UserID);
                }
                if (!$user) {
                    $user = $this->autoCreateUserForStaff($staff);
                }
            }
        }

        // 4. If a user is found, attempt authentication with their actual username and password
        if ($user && Auth::attempt(['username' => $user->username, 'password' => $password])) {
            // Only enforce default password warning for staff users
            $mustChangePassword = ($user->user_type === 'staff') && \Illuminate\Support\Facades\Hash::check('12345', $user->password);
            
            $userData = $user->toArray();
            $userData['must_change_password'] = $mustChangePassword;

            $staff = \DB::table('tblper')->where('UserID', $user->id)->first();
            $userData['passport_url'] = $staff ? $staff->passport_url : null;

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
     * Helper to auto-create user credentials for staff records missing user mappings.
     */
    private function autoCreateUserForStaff($staff)
    {
        $fullname = trim($staff->surname . ' ' . $staff->first_name . ' ' . ($staff->othernames ?? ''));
        $rawUsername = (string)$staff->ID;

        // Double check if user already exists with this username
        $userObj = \App\Models\User::where('username', $rawUsername)->first();
        if ($userObj) {
            \DB::table('tblper')->where('ID', $staff->ID)->update(['UserID' => $userObj->id]);
            return $userObj;
        }

        $userId = \DB::table('users')->insertGetId([
            'name' => strtoupper($fullname),
            'username' => $rawUsername,
            'email' => $staff->email ?: ($rawUsername . '@isalu.gov.ng'),
            'password' => bcrypt('12345'),
            'courtID' => 9,
            'user_type' => 'staff',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        \DB::table('tblper')->where('ID', $staff->ID)->update(['UserID' => $userId]);

        // Ensure role is assigned
        $staffRole = \DB::table('user_role')
            ->whereRaw('LOWER(rolename) = ?', ['staff'])
            ->first();
        $staffRoleId = $staffRole ? $staffRole->roleID : 2;

        $roleExists = \DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', $staffRoleId)
            ->exists();
        if (!$roleExists) {
            \DB::table('assign_user_role')->insert([
                'userID' => $userId,
                'roleID' => $staffRoleId,
                'created_at' => now()
            ]);
        }

        return \App\Models\User::find($userId);
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

            $isHod = false;
            $isHr = false;
            $activeHrDelegations = collect();
            $employee = \DB::table('tblper')->where('UserID', $userId)->first();
            if ($employee) {
                $activeHrDelegations = \DB::table('hr_delegations')
                    ->where('delegate_staff_id', $employee->ID)
                    ->where('status', 'active')
                    ->where(function($query) {
                        $query->whereNull('start_date')
                              ->orWhere('start_date', '<=', now()->toDateString());
                    })
                    ->where(function($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', now()->toDateString());
                    })
                    ->get();
            }

            $activeHodDelegations = collect();
            if ($employee && $employee->is_hod == 1) {
                $isHod = true;
            } else if ($employee) {
                $activeHodDelegations = \DB::table('hod_delegations')
                    ->where('delegate_staff_id', $employee->ID)
                    ->where('status', 'active')
                    ->where(function($query) {
                        $query->whereNull('start_date')
                              ->orWhere('start_date', '<=', now()->toDateString());
                    })
                    ->where(function($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', now()->toDateString());
                    })
                    ->get();
                $isHod = $activeHodDelegations->isNotEmpty();
            }

            if ($employee) {
                $isHr = \DB::table('assign_user_role')
                    ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                    ->where('assign_user_role.userID', $userId)
                    ->where(function($query) {
                        $query->where('assign_user_role.roleID', 48)
                              ->orWhereRaw('LOWER(user_role.rolename) = ?', ['hr head']);
                    })
                    ->exists();

                if ($activeHrDelegations->count() > 0 && !$isTechnical) {
                    $submoduleIds = [];
                    foreach ($activeHrDelegations as $activeHrDelegation) {
                        $delegatedHrPerms = json_decode($activeHrDelegation->permissions, true) ?: [];

                        foreach ($delegatedHrPerms as $perm) {
                            if (is_numeric($perm)) {
                                $submoduleIds[] = (int)$perm;
                            } else {
                                $mapping = [
                                    'hr_approve_leave' => [252, 253],
                                    'hr_approve_loan' => [231, 232],
                                    'hr_approve_iou' => [242],
                                    'hr_approve_refund' => [263],
                                    'hr_approve_resignation' => [264],
                                ];
                                if (isset($mapping[$perm])) {
                                    $submoduleIds = array_merge($submoduleIds, $mapping[$perm]);
                                }
                            }
                        }
                    }
                    $submoduleIds = array_unique($submoduleIds);

                    if (count($submoduleIds) > 0) {
                        $delegatedSubmodules = \DB::table('submodule as s')
                            ->join('module as m', 'm.moduleID', '=', 's.moduleID')
                            ->whereIn('s.submoduleID', $submoduleIds)
                            ->select('s.submoduleID', 's.submodulename', 's.route', 's.moduleID', 'm.modulename', 'm.link_type')
                            ->orderBy('s.sub_module_rank', 'ASC')
                            ->get();

                        foreach ($delegatedSubmodules as $sub) {
                            $modIndex = -1;
                            foreach ($sidebarData as $idx => $sData) {
                                if ($sData['moduleID'] == $sub->moduleID) {
                                    $modIndex = $idx;
                                    break;
                                }
                            }

                            if ($modIndex !== -1) {
                                $subExists = false;
                                foreach ($sidebarData[$modIndex]['submodules'] as $existingSub) {
                                    if ($existingSub['path'] === '/' . ltrim($sub->route, '/')) {
                                        $subExists = true;
                                        break;
                                    }
                                }
                                if (!$subExists) {
                                    $sidebarData[$modIndex]['submodules'][] = [
                                        'id' => $sub->submoduleID,
                                        'name' => $sub->submodulename,
                                        'path' => '/' . ltrim($sub->route, '/'),
                                    ];
                                }
                            } else {
                                $sidebarData[] = [
                                    'moduleID' => $sub->moduleID,
                                    'modulename' => $sub->modulename,
                                    'link_type' => $sub->link_type,
                                    'submodules' => [
                                        [
                                            'id' => $sub->submoduleID,
                                            'name' => $sub->submodulename,
                                            'path' => '/' . ltrim($sub->route, '/'),
                                        ]
                                    ]
                                ];
                            }
                        }
                    }



                }
                if ($activeHodDelegations->count() > 0 && !$isTechnical) {
                    $hodSubmoduleIds = [];
                    foreach ($activeHodDelegations as $activeHodDelegation) {
                        $delegatedHodPerms = json_decode($activeHodDelegation->permissions, true) ?: [];
                        foreach ($delegatedHodPerms as $perm) {
                            if (is_numeric($perm)) {
                                $hodSubmoduleIds[] = (int)$perm;
                            }
                        }
                    }
                    $hodSubmoduleIds = array_unique($hodSubmoduleIds);

                    if (count($hodSubmoduleIds) > 0) {
                        $delegatedHodSubmodules = \DB::table('submodule as s')
                            ->join('module as m', 'm.moduleID', '=', 's.moduleID')
                            ->whereIn('s.submoduleID', $hodSubmoduleIds)
                            ->select('s.submoduleID', 's.submodulename', 's.route', 's.moduleID', 'm.modulename', 'm.link_type')
                            ->orderBy('s.sub_module_rank', 'ASC')
                            ->get();

                        foreach ($delegatedHodSubmodules as $sub) {
                            $modIndex = -1;
                            foreach ($sidebarData as $idx => $sData) {
                                if ($sData['moduleID'] == $sub->moduleID) {
                                    $modIndex = $idx;
                                    break;
                                }
                            }

                            if ($modIndex !== -1) {
                                $subExists = false;
                                foreach ($sidebarData[$modIndex]['submodules'] as $existingSub) {
                                    if ($existingSub['path'] === '/' . ltrim($sub->route, '/')) {
                                        $subExists = true;
                                        break;
                                    }
                                }
                                if (!$subExists) {
                                    $sidebarData[$modIndex]['submodules'][] = [
                                        'id' => $sub->submoduleID,
                                        'name' => $sub->submodulename,
                                        'path' => '/' . ltrim($sub->route, '/'),
                                    ];
                                }
                            } else {
                                $sidebarData[] = [
                                    'moduleID' => $sub->moduleID,
                                    'modulename' => $sub->modulename,
                                    'link_type' => $sub->link_type,
                                    'submodules' => [
                                        [
                                            'id' => $sub->submoduleID,
                                            'name' => $sub->submodulename,
                                            'path' => '/' . ltrim($sub->route, '/'),
                                        ]
                                    ]
                                ];
                            }
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'is_admin' => $isTechnical,
                'is_hod' => $isHod,
                'is_hr' => $isHr,
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

        $staff = \DB::table('tblper')->where('UserID', $user->id)->first();
        $userData['passport_url'] = $staff ? $staff->passport_url : null;

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

        try {
            \DB::table('users')->where('id', '=', $userid)->update([
                'password' => bcrypt($randomPass),
                'resettoken' => null,
                'token_status' => '0'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Database error: ' . $e->getMessage() . '. Please ensure you have run "php artisan migrate" on the live server.'
            ], 500);
        }

        $to = $email;
        $subject = "Password Recovered";
        $sender = config('mail.from.address') ?: "info@mbrcomputers.net";

        $message = "Dear $staffname, <br><br> Your password has been successfully reset. <br><br> Your new password is: <strong>$randomPass</strong> <br><br> Please log in using this password and make sure to change it in your account settings.";
        
        try {
            // Send email using Laravel's Mail facade to respect .env mail settings
            \Illuminate\Support\Facades\Mail::html($message, function ($mail) use ($to, $subject, $sender) {
                $mail->to($to)
                     ->subject($subject)
                     ->from($sender);
            });
        } catch (\Throwable $e) {
            // Log the mail sending failure and details to laravel.log so it can be verified locally
            \Illuminate\Support\Facades\Log::error("Failed to send password recovery email via Mailer: " . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
                'message' => $message
            ]);
            
            return response()->json([
                'status' => 'error',
                'error' => 'Failed to send email via SMTP: ' . $e->getMessage() . '. Please verify your live MAIL_host, port, username, password and encryption in .env, and make sure to clear the config cache.'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'success' => "Dear $staffname, your password has been reset and sent to your email address: $email. Kindly check your email."
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

        try {
            $user = User::where('resettoken', $token)
                ->where('token_status', '1')
                ->where('resettoken', '!=', '')
                ->first();
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Database error: ' . $e->getMessage() . '. Please ensure you have run "php artisan migrate" on the live server.'
            ], 500);
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'error' => 'The password reset token is invalid or has expired.'
            ], 422);
        }

        try {
            $user->password = bcrypt($request->password);
            $user->token_status = '0';
            $user->save();
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Failed to save new password: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been successfully reset!'
        ]);
    }

    /**
     * GET /api/nextjs/hod-delegations
     */
    public function getDelegations(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isHod']) {
            return response()->json(['status' => 'error', 'message' => 'HOD privileges required.'], 403);
        }

        // Get department staff of the HOD
        $deptId = $ctx['employee']->departmentID;
        $staff = \DB::table('tblper')
            ->where('departmentID', $deptId)
            ->where('ID', '!=', $ctx['employee']->ID) // exclude the HOD themselves
            ->select('ID', 'surname', 'first_name', 'othernames')
            ->get();

        // Get current HOD delegations
        $delegations = \DB::table('hod_delegations as hd')
            ->join('tblper as p', 'p.ID', '=', 'hd.delegate_staff_id')
            ->where('hd.hod_staff_id', $ctx['employee']->ID)
            ->select(
                'hd.*',
                'p.surname',
                'p.first_name',
                'p.othernames'
            )
            ->orderBy('hd.created_at', 'DESC')
            ->get();

        // Get modules and submodules assigned to the HOD's roles or delegated to HOD by HR
        $hrDelegatedSubmoduleIds = [];
        $hrDelegation = \DB::table('hr_delegations')
            ->where('delegate_staff_id', $ctx['employee']->ID)
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->first();
        if ($hrDelegation) {
            $perms = json_decode($hrDelegation->permissions, true) ?: [];
            foreach ($perms as $perm) {
                if (is_numeric($perm)) {
                    $hrDelegatedSubmoduleIds[] = (int)$perm;
                } else {
                    $mapping = [
                        'hr_approve_leave' => [252, 253],
                        'hr_approve_loan' => [231, 232],
                        'hr_approve_iou' => [242],
                        'hr_approve_refund' => [263],
                        'hr_approve_resignation' => [264],
                    ];
                    if (isset($mapping[$perm])) {
                        $hrDelegatedSubmoduleIds = array_merge($hrDelegatedSubmoduleIds, $mapping[$perm]);
                    }
                }
            }
        }

        $query = \DB::table('assign_user_role')
            ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
            ->join('assign_module_role', 'assign_module_role.roleID', '=', 'assign_user_role.roleID')
            ->join('submodule', 'submodule.submoduleID', '=', 'assign_module_role.submoduleID')
            ->join('module', 'module.moduleID', '=', 'submodule.moduleID')
            ->where('assign_user_role.userID', '=', $ctx['userId'])
            ->select('submodule.submoduleID', 'submodule.submodulename', 'submodule.route', 'module.modulename');

        if (!empty($hrDelegatedSubmoduleIds)) {
            $delegatedSubmodulesQuery = \DB::table('submodule')
                ->join('module', 'module.moduleID', '=', 'submodule.moduleID')
                ->whereIn('submodule.submoduleID', $hrDelegatedSubmoduleIds)
                ->select('submodule.submoduleID', 'submodule.submodulename', 'submodule.route', 'module.modulename');
            
            $assignedSubmodules = $query->union($delegatedSubmodulesQuery)->get();
        } else {
            $assignedSubmodules = $query->distinct()
                ->orderBy('module.modulename', 'ASC')
                ->orderBy('submodule.submodulename', 'ASC')
                ->get();
        }

        $assignedSubmodules = $assignedSubmodules->unique('submoduleID')->values();

        return response()->json([
            'status' => 'success',
            'staff' => $staff,
            'delegations' => $delegations,
            'assignedSubmodules' => $assignedSubmodules
        ]);
    }

    /**
     * POST /api/nextjs/hod-delegations
     */
    public function saveDelegation(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isHod']) {
            return response()->json(['status' => 'error', 'message' => 'HOD privileges required.'], 403);
        }

        $request->validate([
            'delegate_staff_id' => 'required|integer',
            'permissions' => 'required|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Verify that the delegate is in the HOD's department
        $delegate = \DB::table('tblper')
            ->where('ID', $request->delegate_staff_id)
            ->where('departmentID', $ctx['employee']->departmentID)
            ->first();

        if (!$delegate) {
            return response()->json(['status' => 'error', 'message' => 'Staff member is not in your department.'], 403);
        }

        // Insert new delegation
        \DB::table('hod_delegations')->insert([
            'hod_staff_id' => $ctx['employee']->ID,
            'delegate_staff_id' => $request->delegate_staff_id,
            'department_id' => $ctx['employee']->departmentID,
            'permissions' => json_encode($request->permissions),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role delegation created successfully.'
        ]);
    }

    /**
     * POST /api/nextjs/hod-delegations/toggle/{id}
     */
    public function toggleDelegation(Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isHod']) {
            return response()->json(['status' => 'error', 'message' => 'HOD privileges required.'], 403);
        }

        $delegation = \DB::table('hod_delegations')
            ->where('id', $id)
            ->where('hod_staff_id', $ctx['employee']->ID)
            ->first();

        if (!$delegation) {
            return response()->json(['status' => 'error', 'message' => 'Delegation not found.'], 404);
        }

        $newStatus = $delegation->status === 'active' ? 'inactive' : 'active';

        \DB::table('hod_delegations')
            ->where('id', $id)
            ->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Delegation status updated successfully.',
            'new_status' => $newStatus
        ]);
    }

    /**
     * GET /api/nextjs/hr-delegations
     */
    public function getHrDelegations(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isAdminStaff'] || (isset($ctx['isDelegatedHr']) && $ctx['isDelegatedHr'])) {
            return response()->json(['status' => 'error', 'message' => 'HR Head privileges required.'], 403);
        }

        if (!$ctx['employee']) {
            return response()->json(['status' => 'error', 'message' => 'Employee profile not found for this user.'], 400);
        }

        // Get department staff of the HR manager
        $deptId = $ctx['employee']->departmentID;
        $staff = \DB::table('tblper')
            ->where('departmentID', $deptId)
            ->where('ID', '!=', $ctx['employee']->ID) // exclude the HR manager themselves
            ->select('ID', 'UserID', 'surname', 'first_name', 'othernames')
            ->get();

        // Get current HR delegations
        $delegations = \DB::table('hr_delegations as hd')
            ->join('tblper as p', 'p.ID', '=', 'hd.delegate_staff_id')
            ->where('hd.hr_staff_id', $ctx['employee']->ID)
            ->select(
                'hd.*',
                'p.surname',
                'p.first_name',
                'p.othernames'
            )
            ->orderBy('hd.created_at', 'DESC')
            ->get();

        // Get modules and submodules assigned to the HR HEAD's roles
        $assignedSubmodules = \DB::table('assign_user_role')
            ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
            ->join('assign_module_role', 'assign_module_role.roleID', '=', 'assign_user_role.roleID')
            ->join('submodule', 'submodule.submoduleID', '=', 'assign_module_role.submoduleID')
            ->join('module', 'module.moduleID', '=', 'submodule.moduleID')
            ->where('assign_user_role.userID', '=', $ctx['userId'])
            ->select('submodule.submoduleID', 'submodule.submodulename', 'submodule.route', 'module.modulename')
            ->distinct()
            ->orderBy('module.modulename', 'ASC')
            ->orderBy('submodule.submodulename', 'ASC')
            ->get();

        // Get system roles list
        $roles = \DB::table('user_role')
            ->orderBy('rolename', 'ASC')
            ->get();

        return response()->json([
            'status' => 'success',
            'staff' => $staff,
            'delegations' => $delegations,
            'assignedSubmodules' => $assignedSubmodules,
            'roles' => $roles
        ]);
    }

    /**
     * POST /api/nextjs/hr-delegations
     */
    public function saveHrDelegation(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isAdminStaff'] || (isset($ctx['isDelegatedHr']) && $ctx['isDelegatedHr'])) {
            return response()->json(['status' => 'error', 'message' => 'HR Head privileges required.'], 403);
        }

        if (!$ctx['employee']) {
            return response()->json(['status' => 'error', 'message' => 'Employee profile not found for this user.'], 400);
        }

        $request->validate([
            'delegate_staff_id' => 'required|integer',
            'permissions' => 'required|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Verify that the delegate is in the HR manager's department
        $delegate = \DB::table('tblper')
            ->where('ID', $request->delegate_staff_id)
            ->where('departmentID', $ctx['employee']->departmentID)
            ->first();

        if (!$delegate) {
            return response()->json(['status' => 'error', 'message' => 'Staff member is not in your department.'], 403);
        }

        // Delete any existing delegation records for this delegate to avoid duplicate active scopes
        \DB::table('hr_delegations')
            ->where('hr_staff_id', $ctx['employee']->ID)
            ->where('delegate_staff_id', $request->delegate_staff_id)
            ->delete();

        // Insert new delegation
        \DB::table('hr_delegations')->insert([
            'hr_staff_id' => $ctx['employee']->ID,
            'delegate_staff_id' => $request->delegate_staff_id,
            'permissions' => json_encode($request->permissions),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'HR role delegated successfully.'
        ]);
    }

    /**
     * POST /api/nextjs/hr-delegations/toggle/{id}
     */
    public function toggleHrDelegation(Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isAdminStaff'] || (isset($ctx['isDelegatedHr']) && $ctx['isDelegatedHr'])) {
            return response()->json(['status' => 'error', 'message' => 'HR Head privileges required.'], 403);
        }

        if (!$ctx['employee']) {
            return response()->json(['status' => 'error', 'message' => 'Employee profile not found for this user.'], 400);
        }

        $delegation = \DB::table('hr_delegations')
            ->where('id', $id)
            ->where('hr_staff_id', $ctx['employee']->ID)
            ->first();

        if (!$delegation) {
            return response()->json(['status' => 'error', 'message' => 'Delegation not found.'], 404);
        }

        $newStatus = $delegation->status === 'active' ? 'inactive' : 'active';

        \DB::table('hr_delegations')
            ->where('id', $id)
            ->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Delegation status updated successfully.',
            'new_status' => $newStatus
        ]);
    }
}
