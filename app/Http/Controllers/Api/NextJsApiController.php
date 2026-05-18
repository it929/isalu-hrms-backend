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
            // Optional: load roles/modules here
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'role' => ['name' => 'Super Admin'], // Mocking role for now
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
        return response()->json([
            'departments' => [
                ['id' => 1, 'name' => 'Human Resources', 'currentHOD' => 'Jane Smith', 'users' => 15],
                ['id' => 2, 'name' => 'Information Technology', 'currentHOD' => 'John Doe', 'users' => 42],
            ]
        ]);
    }
    /**
     * Get Dashboard Stats
     */
    public function getDashboardStats()
    {
        $totalStaff = \DB::table('tblper')->count();
        $maleStaff = \DB::table('tblper')->where('gender', 'Male')->count();
        $femaleStaff = \DB::table('tblper')->where('gender', 'Female')->count();
        
        // Mocking other stats for now as they require leave/task tables
        return response()->json([
            'stats' => [
                ['label' => 'Total Employees', 'value' => number_format($totalStaff), 'icon' => 'Users', 'color' => 'var(--primary)'],
                ['label' => 'Male Staff', 'value' => number_format($maleStaff), 'icon' => 'Users', 'color' => '#10b981'],
                ['label' => 'Female Staff', 'value' => number_format($femaleStaff), 'icon' => '#f59e0b', 'color' => '#f59e0b'],
                ['label' => 'Open Positions', 'value' => '12', 'icon' => 'Briefcase', 'color' => '#8b5cf6'],
            ]
        ]);
    }
}
