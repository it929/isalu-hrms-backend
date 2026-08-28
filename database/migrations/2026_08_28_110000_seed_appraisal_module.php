<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedAppraisalModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Check or create "Performance & Appraisal" Module
        $module = DB::table('module')->where('modulename', 'Performance & Appraisal')->first();
        if (!$module) {
            $maxRank = DB::table('module')->max('module_rank') ?? 10;
            $moduleId = DB::table('module')->insertGetId([
                'modulename' => 'Performance & Appraisal',
                'module_rank' => $maxRank + 1,
                'link_type' => 'HR',
                'created_at' => now(),
            ]);
        } else {
            $moduleId = $module->moduleID;
        }

        // 2. Define Submodules
        $submodulesData = [
            [
                'name' => 'My Self-Appraisal',
                'route' => 'dashboard/appraisals/self',
                'rank' => 1,
                'for_all' => true,
            ],
            [
                'name' => 'Staff Appraisals (Appraiser)',
                'route' => 'dashboard/appraisals/team',
                'rank' => 2,
                'for_appraiser' => true,
            ],
            [
                'name' => 'HR Calibration & Approvals',
                'route' => 'dashboard/appraisals/approvals',
                'rank' => 3,
                'for_hr_admin' => true,
            ],
            [
                'name' => 'Appraisal Setup & Templates',
                'route' => 'dashboard/appraisals/setup',
                'rank' => 4,
                'for_hr_admin' => true,
            ],
            [
                'name' => 'Appraisal Review Cycles',
                'route' => 'dashboard/appraisals/periods',
                'rank' => 5,
                'for_hr_admin' => true,
            ],
        ];

        // Fetch HR/SuperAdmin role IDs
        $allRoles = DB::table('user_role')->pluck('roleID');
        $adminRoles = DB::table('user_role')
            ->whereIn('roleID', [1, 48, 68])
            ->orWhere('rolename', 'LIKE', '%Super%')
            ->orWhere('rolename', 'LIKE', '%Admin%')
            ->orWhere('rolename', 'LIKE', '%HR%')
            ->pluck('roleID')
            ->unique();

        $hodRoles = DB::table('user_role')
            ->whereIn('roleID', [1, 48, 68])
            ->orWhere('rolename', 'LIKE', '%HOD%')
            ->orWhere('rolename', 'LIKE', '%Head%')
            ->orWhere('rolename', 'LIKE', '%Manager%')
            ->orWhere('rolename', 'LIKE', '%Supervisor%')
            ->pluck('roleID')
            ->merge($adminRoles)
            ->unique();

        foreach ($submodulesData as $sub) {
            $existingSub = DB::table('submodule')
                ->where('moduleID', $moduleId)
                ->where('submodulename', $sub['name'])
                ->first();

            if (!$existingSub) {
                $subId = DB::table('submodule')->insertGetId([
                    'moduleID' => $moduleId,
                    'submodulename' => $sub['name'],
                    'route' => $sub['route'],
                    'sub_module_rank' => $sub['rank'],
                    'status' => 1,
                    'created_at' => now(),
                ]);
            } else {
                $subId = $existingSub->submoduleID;
            }

            // Assign permissions
            if (!empty($sub['for_all'])) {
                foreach ($allRoles as $roleId) {
                    DB::table('assign_module_role')->updateOrInsert(
                        ['roleID' => $roleId, 'moduleID' => $moduleId, 'submoduleID' => $subId],
                        ['created_at' => now()]
                    );
                }
            } elseif (!empty($sub['for_appraiser'])) {
                foreach ($hodRoles as $roleId) {
                    DB::table('assign_module_role')->updateOrInsert(
                        ['roleID' => $roleId, 'moduleID' => $moduleId, 'submoduleID' => $subId],
                        ['created_at' => now()]
                    );
                }
            } else {
                foreach ($adminRoles as $roleId) {
                    DB::table('assign_module_role')->updateOrInsert(
                        ['roleID' => $roleId, 'moduleID' => $moduleId, 'submoduleID' => $subId],
                        ['created_at' => now()]
                    );
                }
            }
        }

        // 3. Seed Default Appraisal Template & Criteria
        $templateExists = DB::table('appraisal_templates')->where('title', 'Standard Staff Performance Appraisal')->exists();
        if (!$templateExists) {
            $templateId = DB::table('appraisal_templates')->insertGetId([
                'title' => 'Standard Staff Performance Appraisal',
                'description' => 'Comprehensive performance evaluation template covering core job deliverables, competencies, and professional values.',
                'total_weight' => 100.00,
                'passing_score' => 60.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categories = [
                [
                    'name' => '1. Key Job Deliverables & Output',
                    'weight' => 35.00,
                    'rank' => 1,
                    'items' => [
                        ['title' => 'Job Knowledge & Technical Proficiency', 'description' => 'Demonstrates sound practical and theoretical knowledge required for duties.'],
                        ['title' => 'Quality and Accuracy of Work', 'description' => 'Delivers high-standard, error-free work meeting hospital/organizational benchmarks.'],
                        ['title' => 'Productivity & Timely Delivery', 'description' => 'Consistently meets project deadlines, patient care targets, and task commitments.'],
                    ],
                ],
                [
                    'name' => '2. Core Competencies & Quality of Work',
                    'weight' => 25.00,
                    'rank' => 2,
                    'items' => [
                        ['title' => 'Problem Solving & Critical Thinking', 'description' => 'Effectively identifies challenges and implements practical solutions.'],
                        ['title' => 'Attention to Detail & Standards', 'description' => 'Follows SOPs, hygiene/safety protocols, and hospital guidelines meticulously.'],
                        ['title' => 'Continuous Learning & Adaptability', 'description' => 'Eagerly acquires new skills and adjusts positively to changes.'],
                    ],
                ],
                [
                    'name' => '3. Communication & Team Collaboration',
                    'weight' => 15.00,
                    'rank' => 3,
                    'items' => [
                        ['title' => 'Teamwork & Interpersonal Skills', 'description' => 'Collaborates constructively with colleagues and respects diverse opinions.'],
                        ['title' => 'Communication Clarity & Courtesy', 'description' => 'Communicates clearly, respectfully, and empathetically with clients and staff.'],
                    ],
                ],
                [
                    'name' => '4. Professionalism, Ethics & Attendance',
                    'weight' => 15.00,
                    'rank' => 4,
                    'items' => [
                        ['title' => 'Punctuality & Attendance Record', 'description' => 'Punctual, dependable, and respects shift timings and roster schedules.'],
                        ['title' => 'Integrity, Confidentiality & Ethics', 'description' => 'Maintains high confidentiality, honesty, and professional ethics.'],
                    ],
                ],
                [
                    'name' => '5. Initiative, Leadership & Organizational Contribution',
                    'weight' => 10.00,
                    'rank' => 5,
                    'items' => [
                        ['title' => 'Initiative & Resourcefulness', 'description' => 'Takes proactive steps beyond basic job requirements to improve department workflow.'],
                        ['title' => 'Mentorship & Support to Others', 'description' => 'Guides junior team members and fosters a positive workplace environment.'],
                    ],
                ],
            ];

            foreach ($categories as $cat) {
                $catId = DB::table('appraisal_criteria_categories')->insertGetId([
                    'template_id' => $templateId,
                    'name' => $cat['name'],
                    'weight' => $cat['weight'],
                    'rank' => $cat['rank'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $itemWeight = count($cat['items']) > 0 ? round($cat['weight'] / count($cat['items']), 2) : 0;
                foreach ($cat['items'] as $itemIdx => $item) {
                    DB::table('appraisal_criteria_items')->insert([
                        'category_id' => $catId,
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'max_score' => 5,
                        'weight' => $itemWeight,
                        'rank' => $itemIdx + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = DB::table('module')->where('modulename', 'Performance & Appraisal')->first();
        if ($module) {
            $subIds = DB::table('submodule')->where('moduleID', $module->moduleID)->pluck('submoduleID');
            DB::table('assign_module_role')->whereIn('submoduleID', $subIds)->delete();
            DB::table('submodule')->where('moduleID', $module->moduleID)->delete();
            DB::table('assign_module_role')->where('moduleID', $module->moduleID)->delete();
            DB::table('module')->where('moduleID', $module->moduleID)->delete();
        }
    }
}
