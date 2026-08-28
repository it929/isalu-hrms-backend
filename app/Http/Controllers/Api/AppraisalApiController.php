<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppraisalPeriod;
use App\Models\AppraisalTemplate;
use App\Models\AppraisalCriteriaCategory;
use App\Models\AppraisalCriteriaItem;
use App\Models\AppraisalSubmission;
use App\Models\AppraisalScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppraisalApiController extends Controller
{
    use ResolveUserContextTrait;

    /* =========================================================================
       1. APPRAISAL PERIODS & CYCLES (HR / SUPERADMIN)
       ========================================================================= */

    /**
     * GET /api/nextjs/appraisals/periods
     */
    public function getPeriods(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $periods = DB::table('appraisal_periods')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($period) {
                    $total = DB::table('appraisal_submissions')->where('period_id', $period->id)->count();
                    $selfDone = DB::table('appraisal_submissions')->where('period_id', $period->id)->where('status', '!=', 'pending_self_review')->count();
                    $appraiserDone = DB::table('appraisal_submissions')->where('period_id', $period->id)->whereIn('status', ['pending_hr_review', 'pending_md_approval', 'approved', 'acknowledged'])->count();
                    $approved = DB::table('appraisal_submissions')->where('period_id', $period->id)->whereIn('status', ['approved', 'acknowledged'])->count();

                    return [
                        'id' => (int)$period->id,
                        'title' => $period->title,
                        'review_type' => $period->review_type,
                        'start_date' => $period->start_date,
                        'end_date' => $period->end_date,
                        'self_review_deadline' => $period->self_review_deadline,
                        'appraiser_review_deadline' => $period->appraiser_review_deadline,
                        'status' => $period->status,
                        'description' => $period->description,
                        'stats' => [
                            'total_assigned' => $total,
                            'self_submitted' => $selfDone,
                            'appraiser_completed' => $appraiserDone,
                            'approved' => $approved,
                        ],
                    ];
                });

            return response()->json(['status' => 'success', 'data' => $periods]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController getPeriods: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/periods
     */
    public function storePeriod(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff']))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized: Only HR or Admins can create appraisal cycles.'], 403);
            }


            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'review_type' => 'required|in:annual,mid_year,quarterly,probation',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'self_review_deadline' => 'required|date',
                'appraiser_review_deadline' => 'required|date',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            $periodId = DB::table('appraisal_periods')->insertGetId([
                'title' => $request->title,
                'review_type' => $request->review_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'self_review_deadline' => $request->self_review_deadline,
                'appraiser_review_deadline' => $request->appraiser_review_deadline,
                'status' => 'active',
                'description' => $request->description,
                'created_by' => $ctx['userId'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Auto-dispatch if requested
            if ($request->boolean('auto_dispatch')) {
                $this->internalDispatchPeriod($periodId, $request->input('template_id'));
            }

            return response()->json(['status' => 'success', 'message' => 'Appraisal cycle created successfully.', 'period_id' => $periodId]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController storePeriod: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/periods/{id}/dispatch
     * Bulk initializes appraisal forms for staff.
     */
    public function dispatchPeriod(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff']))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized: Only HR or Admins can dispatch appraisal cycles.'], 403);
            }


            $count = $this->internalDispatchPeriod($id, $request->input('template_id'), $request->input('department_id'));

            return response()->json([
                'status' => 'success',
                'message' => "Appraisal cycle successfully dispatched to {$count} staff members."
            ]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController dispatchPeriod: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Internal helper to dispatch forms and populate appraisal scores.
     */
    private function internalDispatchPeriod($periodId, $templateId = null, $departmentId = null)
    {
        if (!$templateId) {
            $defaultTemplate = DB::table('appraisal_templates')->where('is_active', 1)->first();
            $templateId = $defaultTemplate ? $defaultTemplate->id : 1;
        }

        // Get criteria items for this template
        $items = DB::table('appraisal_criteria_categories as c')
            ->join('appraisal_criteria_items as i', 'i.category_id', '=', 'c.id')
            ->where('c.template_id', $templateId)
            ->select('i.id as item_id')
            ->get();

        // Get active staff query
        $staffQuery = DB::table('tblper')
            ->where(function($q) {
                $q->whereNull('staff_status')->orWhere('staff_status', 1);
            });

        if ($departmentId) {
            $staffQuery->where('departmentID', $departmentId);
        }

        $allStaff = $staffQuery->get();
        $dispatchedCount = 0;

        foreach ($allStaff as $staff) {
            // Check if already dispatched for this period
            $exists = DB::table('appraisal_submissions')
                ->where('period_id', $periodId)
                ->where('staff_id', $staff->ID)
                ->exists();

            if ($exists) {
                continue;
            }

            $deptId = $staff->departmentID ?? null;

            // Find HOD of staff's department to assign as appraiser
            $hodStaff = null;
            if ($deptId) {
                $hodStaff = DB::table('tblper')
                    ->where('is_hod', 1)
                    ->where('departmentID', $deptId)
                    ->first();
            }



            // Fallback appraiser if staff is the HOD or no HOD in dept
            if (!$hodStaff || $hodStaff->ID == $staff->ID) {
                // Assign Head of HR (role 48/68) or Admin
                $hrUser = DB::table('assign_user_role as aur')
                    ->join('tblper as p', 'p.UserID', '=', 'aur.userID')
                    ->whereIn('aur.roleID', [1, 48, 68])
                    ->where('p.ID', '!=', $staff->ID)
                    ->select('p.ID')
                    ->first();
                $appraiserId = $hrUser ? $hrUser->ID : $staff->ID;
            } else {
                $appraiserId = $hodStaff->ID;
            }

            $submissionId = DB::table('appraisal_submissions')->insertGetId([
                'period_id' => $periodId,
                'staff_id' => $staff->ID,
                'appraiser_id' => $appraiserId,
                'template_id' => $templateId,
                'status' => 'pending_self_review',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Initialize score rows for all criteria items
            foreach ($items as $item) {
                DB::table('appraisal_scores')->insert([
                    'submission_id' => $submissionId,
                    'criteria_item_id' => $item->item_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $dispatchedCount++;
        }

        DB::table('appraisal_periods')->where('id', $periodId)->update(['status' => 'active']);

        return $dispatchedCount;
    }

    /* =========================================================================
       2. TEMPLATES & CRITERIA BUILDER (HR / ADMIN)
       ========================================================================= */

    /**
     * GET /api/nextjs/appraisals/templates
     */
    public function getTemplates(Request $request)
    {
        try {
            $templates = AppraisalTemplate::with(['categories.items'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['status' => 'success', 'data' => $templates]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController getTemplates: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/templates
     */
    public function storeTemplate(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff']))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }


            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'total_weight' => 'nullable|numeric',
                'passing_score' => 'nullable|numeric',
                'categories' => 'required|array|min:1',
                'categories.*.name' => 'required|string',
                'categories.*.weight' => 'required|numeric',
                'categories.*.items' => 'required|array|min:1',
                'categories.*.items.*.title' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            DB::beginTransaction();

            $template = AppraisalTemplate::create([
                'title' => $request->title,
                'description' => $request->description,
                'total_weight' => $request->total_weight ?? 100.00,
                'passing_score' => $request->passing_score ?? 60.00,
                'is_active' => true,
            ]);

            foreach ($request->categories as $cIdx => $catData) {
                $category = AppraisalCriteriaCategory::create([
                    'template_id' => $template->id,
                    'name' => $catData['name'],
                    'weight' => $catData['weight'],
                    'rank' => $cIdx + 1,
                ]);

                $itemsCount = count($catData['items']);
                $itemWeight = $itemsCount > 0 ? round($catData['weight'] / $itemsCount, 2) : 0;

                foreach ($catData['items'] as $iIdx => $itemData) {
                    AppraisalCriteriaItem::create([
                        'category_id' => $category->id,
                        'title' => $itemData['title'],
                        'description' => $itemData['description'] ?? null,
                        'max_score' => $itemData['max_score'] ?? 5,
                        'weight' => $itemData['weight'] ?? $itemWeight,
                        'rank' => $iIdx + 1,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Template created successfully.', 'data' => $template]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AppraisalApiController storeTemplate: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /* =========================================================================
       3. EMPLOYEE SELF-REVIEW PORTAL (ALL STAFF)
       ========================================================================= */

    /**
     * GET /api/nextjs/appraisals/my-active
     * Fetches current staff's appraisals.
     */
    public function getMyActiveAppraisals(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || empty($ctx['employee'])) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found for logged in user.'], 404);
            }

            $staffId = $ctx['employee']->ID;

            $submissions = DB::table('appraisal_submissions as s')
                ->join('appraisal_periods as p', 'p.id', '=', 's.period_id')
                ->join('appraisal_templates as t', 't.id', '=', 's.template_id')
                ->leftJoin('tblper as appraiser', 'appraiser.ID', '=', 's.appraiser_id')
                ->where('s.staff_id', $staffId)
                ->select(
                    's.*',
                    'p.title as period_title',
                    'p.review_type',
                    'p.self_review_deadline',
                    'p.appraiser_review_deadline',
                    't.title as template_title',
                    't.passing_score',
                    'appraiser.surname as appraiser_surname',
                    'appraiser.first_name as appraiser_firstname'
                )
                ->orderBy('s.created_at', 'desc')
                ->get();

            return response()->json(['status' => 'success', 'data' => $submissions]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController getMyActiveAppraisals: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/appraisals/form/{submissionId}
     * Retrieves full questionnaire, criteria structure, and existing ratings.
     */
    public function getAppraisalForm(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $submission = DB::table('appraisal_submissions as s')
                ->join('appraisal_periods as p', 'p.id', '=', 's.period_id')
                ->join('appraisal_templates as t', 't.id', '=', 's.template_id')
                ->join('tblper as staff', 'staff.ID', '=', 's.staff_id')
                ->leftJoin('tblper as appraiser', 'appraiser.ID', '=', 's.appraiser_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'staff.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'staff.designationID')



                ->where('s.id', $submissionId)
                ->select(
                    's.*',
                    'p.title as period_title',
                    'p.review_type',
                    'p.self_review_deadline',
                    'p.appraiser_review_deadline',
                    't.title as template_title',
                    't.total_weight',
                    't.passing_score',
                    'staff.surname as staff_surname',
                    'staff.first_name as staff_firstname',
                    'staff.fileNo as staff_file_no',
                    'd.department as department_name',
                    'des.designation as designation_name',
                    'appraiser.surname as appraiser_surname',
                    'appraiser.first_name as appraiser_firstname'
                )
                ->first();

            if (!$submission) {
                return response()->json(['status' => 'error', 'message' => 'Appraisal submission not found.'], 404);
            }

            // Categories & Items with existing scores
            $categories = AppraisalCriteriaCategory::with(['items'])
                ->where('template_id', $submission->template_id)
                ->orderBy('rank', 'asc')
                ->get();

            $scores = DB::table('appraisal_scores')
                ->where('submission_id', $submissionId)
                ->get()
                ->keyBy('criteria_item_id');

            $structuredCategories = $categories->map(function ($cat) use ($scores) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'weight' => (float)$cat->weight,
                    'rank' => (int)$cat->rank,
                    'items' => $cat->items->map(function ($item) use ($scores) {
                        $scoreRow = $scores->get($item->id);
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'description' => $item->description,
                            'max_score' => (int)$item->max_score,
                            'weight' => (float)$item->weight,
                            'self_score' => $scoreRow ? ($scoreRow->self_score !== null ? (float)$scoreRow->self_score : null) : null,
                            'self_comment' => $scoreRow ? $scoreRow->self_comment : null,
                            'appraiser_score' => $scoreRow ? ($scoreRow->appraiser_score !== null ? (float)$scoreRow->appraiser_score : null) : null,
                            'appraiser_comment' => $scoreRow ? $scoreRow->appraiser_comment : null,
                            'final_score' => $scoreRow ? ($scoreRow->final_score !== null ? (float)$scoreRow->final_score : null) : null,
                        ];
                    }),
                ];
            });

            return response()->json([
                'status' => 'success',
                'submission' => $submission,
                'categories' => $structuredCategories,
            ]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController getAppraisalForm: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/form/{submissionId}/save-self
     * Save draft or final submit employee self-review.
     */
    public function saveSelfReview(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || empty($ctx['employee'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $submission = AppraisalSubmission::findOrFail($submissionId);
            $staffId = $ctx['employee']->ID;

            if ($submission->staff_id != $staffId && !$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'You can only edit your own self-review.'], 403);
            }

            $isFinalSubmit = $request->boolean('is_submit');

            DB::beginTransaction();

            $totalWeightedSelfScore = 0;
            $totalPossibleWeight = 0;

            if ($request->has('scores') && is_array($request->scores)) {
                foreach ($request->scores as $scoreData) {
                    $criteriaItemId = $scoreData['criteria_item_id'];
                    $selfScore = isset($scoreData['self_score']) && $scoreData['self_score'] !== '' ? (float)$scoreData['self_score'] : null;
                    $selfComment = $scoreData['self_comment'] ?? null;

                    $item = AppraisalCriteriaItem::with('category')->find($criteriaItemId);
                    if ($item && $selfScore !== null) {
                        $maxScore = $item->max_score ?: 5;
                        $weight = $item->weight ?: 1;
                        $weightedItemScore = ($selfScore / $maxScore) * $weight;
                        $totalWeightedSelfScore += $weightedItemScore;
                        $totalPossibleWeight += $weight;
                    }

                    DB::table('appraisal_scores')->updateOrInsert(
                        ['submission_id' => $submissionId, 'criteria_item_id' => $criteriaItemId],
                        [
                            'self_score' => $selfScore,
                            'self_comment' => $selfComment,
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            $calculatedSelfPercent = $totalPossibleWeight > 0 ? round(($totalWeightedSelfScore / $totalPossibleWeight) * 100, 2) : 0;

            $updateData = [
                'staff_key_accomplishments' => $request->staff_key_accomplishments,
                'staff_challenges' => $request->staff_challenges,
                'staff_training_needs' => $request->staff_training_needs,
                'self_total_score' => $calculatedSelfPercent,
                'updated_at' => now(),
            ];

            if ($isFinalSubmit) {
                $updateData['status'] = 'pending_appraiser';
                $updateData['self_submitted_at'] = now();
            }

            $submission->update($updateData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $isFinalSubmit ? 'Self-appraisal submitted successfully to your appraiser.' : 'Self-appraisal draft saved successfully.',
                'self_total_score' => $calculatedSelfPercent,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AppraisalApiController saveSelfReview: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/form/{submissionId}/acknowledge
     * Employee acknowledgment of finalized appraisal.
     */
    public function acknowledgeAppraisal(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || empty($ctx['employee'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $submission = AppraisalSubmission::findOrFail($submissionId);
            if ($submission->staff_id != $ctx['employee']->ID && !$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }

            $submission->update([
                'status' => 'acknowledged',
                'staff_feedback' => $request->input('staff_feedback'),
                'staff_acknowledged_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Appraisal successfully acknowledged.']);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController acknowledgeAppraisal: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /* =========================================================================
       4. APPRAISER / HOD TEAM HUB
       ========================================================================= */

    /**
     * GET /api/nextjs/appraisals/team-queue
     */
    public function getTeamQueue(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $query = DB::table('appraisal_submissions as s')
                ->join('appraisal_periods as p', 'p.id', '=', 's.period_id')
                ->join('appraisal_templates as t', 't.id', '=', 's.template_id')
                ->join('tblper as staff', 'staff.ID', '=', 's.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'staff.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'staff.designationID')


                ->select(
                    's.*',
                    'p.title as period_title',
                    'p.self_review_deadline',
                    'p.appraiser_review_deadline',
                    't.title as template_title',
                    'staff.surname as staff_surname',
                    'staff.first_name as staff_firstname',
                    'staff.fileNo as staff_file_no',
                    'staff.is_hod as staff_is_hod',
                    'd.department as department_name',
                    'des.designation as designation_name'
                );

            // If not SuperAdmin or HR, restrict to submissions where current user is appraiser or HOD of department
            if (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff'])) {
                $employeeId = $ctx['employee']->ID ?? null;

                $userDeptId = $ctx['employee']->departmentID ?? null;

                $query->where(function($q) use ($employeeId, $userDeptId) {
                    $q->where('s.appraiser_id', $employeeId);
                    if ($userDeptId) {
                        $q->orWhere('staff.departmentID', $userDeptId);
                    }
                });
            }



            if ($request->has('period_id') && !empty($request->period_id)) {
                $query->where('s.period_id', $request->period_id);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('s.status', $request->status);
            }

            $results = $query->orderBy('s.status', 'asc')->orderBy('staff.surname', 'asc')->get();

            return response()->json(['status' => 'success', 'data' => $results]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController getTeamQueue: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/form/{submissionId}/save-appraiser
     * Appraiser scores, comments, promotion/training recommendations, and submits to HR.
     */
    public function saveAppraiserReview(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $submission = AppraisalSubmission::findOrFail($submissionId);
            $isFinalSubmit = $request->boolean('is_submit');

            DB::beginTransaction();

            $totalWeightedAppraiserScore = 0;
            $totalPossibleWeight = 0;

            if ($request->has('scores') && is_array($request->scores)) {
                foreach ($request->scores as $scoreData) {
                    $criteriaItemId = $scoreData['criteria_item_id'];
                    $appraiserScore = isset($scoreData['appraiser_score']) && $scoreData['appraiser_score'] !== '' ? (float)$scoreData['appraiser_score'] : null;
                    $appraiserComment = $scoreData['appraiser_comment'] ?? null;

                    $item = AppraisalCriteriaItem::with('category')->find($criteriaItemId);
                    if ($item && $appraiserScore !== null) {
                        $maxScore = $item->max_score ?: 5;
                        $weight = $item->weight ?: 1;
                        $weightedItemScore = ($appraiserScore / $maxScore) * $weight;
                        $totalWeightedAppraiserScore += $weightedItemScore;
                        $totalPossibleWeight += $weight;
                    }

                    DB::table('appraisal_scores')->updateOrInsert(
                        ['submission_id' => $submissionId, 'criteria_item_id' => $criteriaItemId],
                        [
                            'appraiser_score' => $appraiserScore,
                            'appraiser_comment' => $appraiserComment,
                            'final_score' => $appraiserScore,
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            $calculatedAppraiserPercent = $totalPossibleWeight > 0 ? round(($totalWeightedAppraiserScore / $totalPossibleWeight) * 100, 2) : 0;

            // Determine Performance Grade
            // A: >= 80% (Exceptional) | B: 70 - 79% (Exceeds Expectations) | C: 50 - 69% (Meets Expectations) | D: < 50% (Unsatisfactory / PIP)
            $grade = 'D';
            if ($calculatedAppraiserPercent >= 80) {
                $grade = 'A (Exceptional)';
            } elseif ($calculatedAppraiserPercent >= 70) {
                $grade = 'B (Exceeds)';
            } elseif ($calculatedAppraiserPercent >= 50) {
                $grade = 'C (Meets)';
            } else {
                $grade = 'D (Unsatisfactory)';
            }

            $updateData = [
                'appraiser_strengths' => $request->appraiser_strengths,
                'appraiser_areas_for_growth' => $request->appraiser_areas_for_growth,
                'recommendation_type' => $request->recommendation_type ?? 'none',
                'recommendation_details' => $request->recommendation_details,
                'appraiser_total_score' => $calculatedAppraiserPercent,
                'final_weighted_score' => $calculatedAppraiserPercent,
                'performance_grade' => $grade,
                'updated_at' => now(),
            ];

            if ($isFinalSubmit) {
                $updateData['status'] = 'pending_hr_review';
                $updateData['appraiser_submitted_at'] = now();
            }

            $submission->update($updateData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $isFinalSubmit ? 'Appraisal review submitted to HR for calibration.' : 'Appraiser ratings saved as draft.',
                'appraiser_total_score' => $calculatedAppraiserPercent,
                'performance_grade' => $grade,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AppraisalApiController saveAppraiserReview: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/form/{submissionId}/return-to-staff
     */
    public function returnToStaff(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $submission = AppraisalSubmission::findOrFail($submissionId);
            $reason = $request->input('reason', 'Correction requested by appraiser.');

            $submission->update([
                'status' => 'pending_self_review',
                'appraiser_areas_for_growth' => $reason,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Appraisal returned to employee for revision.']);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController returnToStaff: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /* =========================================================================
       5. HR CALIBRATION & MODERATION (HR / SUPERADMIN)
       ========================================================================= */

    /**
     * GET /api/nextjs/appraisals/moderation-list
     */
    public function getModerationList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff']))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized: Only HR or Admins can access moderation hub.'], 403);
            }


            $query = DB::table('appraisal_submissions as s')
                ->join('appraisal_periods as p', 'p.id', '=', 's.period_id')
                ->join('appraisal_templates as t', 't.id', '=', 's.template_id')
                ->join('tblper as staff', 'staff.ID', '=', 's.staff_id')
                ->leftJoin('tblper as appraiser', 'appraiser.ID', '=', 's.appraiser_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'staff.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'staff.designationID')


                ->select(
                    's.*',
                    'p.title as period_title',
                    't.title as template_title',
                    'staff.surname as staff_surname',
                    'staff.first_name as staff_firstname',
                    'staff.fileNo as staff_file_no',
                    'd.department as department_name',
                    'des.designation as designation_name',
                    'appraiser.surname as appraiser_surname',
                    'appraiser.first_name as appraiser_firstname'
                );

            if ($request->has('period_id') && !empty($request->period_id)) {
                $query->where('s.period_id', $request->period_id);
            }

            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('staff.departmentID', $request->department_id);
            }



            if ($request->has('status') && !empty($request->status)) {
                $query->where('s.status', $request->status);
            }

            $data = $query->orderBy('s.created_at', 'desc')->get();

            // Summary Analytics
            $totalSubmissions = $data->count();
            $gradeA = $data->where('performance_grade', 'A (Exceptional)')->count();
            $gradeB = $data->where('performance_grade', 'B (Exceeds)')->count();
            $gradeC = $data->where('performance_grade', 'C (Meets)')->count();
            $gradeD = $data->where('performance_grade', 'D (Unsatisfactory)')->count();
            $avgScore = $totalSubmissions > 0 ? round($data->avg('final_weighted_score'), 1) : 0;

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'stats' => [
                    'total' => $totalSubmissions,
                    'grade_A' => $gradeA,
                    'grade_B' => $gradeB,
                    'grade_C' => $gradeC,
                    'grade_D' => $gradeD,
                    'average_score' => $avgScore,
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController getModerationList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/form/{submissionId}/calibrate
     * HR Calibration & Approval
     */
    public function moderateSubmission(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff']))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $submission = AppraisalSubmission::findOrFail($submissionId);
            $action = $request->input('action', 'approve'); // approve, calibrate, reject

            if ($action === 'reject') {
                $submission->update([
                    'status' => 'pending_appraiser',
                    'hr_comments' => $request->input('hr_comments', 'Returned by HR for appraiser calibration.'),
                    'updated_at' => now(),
                ]);

                return response()->json(['status' => 'success', 'message' => 'Appraisal returned to Appraiser for re-evaluation.']);
            }

            $finalScore = $request->has('final_weighted_score') && $request->final_weighted_score !== ''
                ? (float)$request->final_weighted_score
                : ($submission->final_weighted_score ?? $submission->appraiser_total_score);

            $grade = 'D (Unsatisfactory)';
            if ($finalScore >= 80) {
                $grade = 'A (Exceptional)';
            } elseif ($finalScore >= 70) {
                $grade = 'B (Exceeds)';
            } elseif ($finalScore >= 50) {
                $grade = 'C (Meets)';
            }

            $submission->update([
                'status' => 'approved',
                'final_weighted_score' => $finalScore,
                'performance_grade' => $grade,
                'hr_comments' => $request->input('hr_comments'),
                'recommendation_type' => $request->input('recommendation_type', $submission->recommendation_type),
                'recommendation_details' => $request->input('recommendation_details', $submission->recommendation_details),
                'hr_reviewed_at' => now(),
                'approved_at' => now(),
                'reviewer_id' => $ctx['userId'] ?? null,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal successfully calibrated and approved.',
                'final_score' => $finalScore,
                'performance_grade' => $grade,
            ]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController moderateSubmission: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/appraisals/form/{submissionId}/push-action
     * Auto-pushes recommendation to Salary Increment or Commendations
     */
    public function pushToAction(Request $request, $submissionId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && empty($ctx['isAdminStaff']))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }


            $submission = AppraisalSubmission::findOrFail($submissionId);
            $actionType = $request->input('action_type', $submission->recommendation_type); // increment, commendation

            if ($actionType === 'increment') {
                // If salary_increments table exists
                if (DB::getSchemaBuilder()->hasTable('salary_increments')) {
                    $staff = DB::table('tblper')->where('ID', $submission->staff_id)->first();
                    DB::table('salary_increments')->insert([
                        'staff_id' => $submission->staff_id,
                        'file_no' => $staff ? $staff->fileNo : '',
                        'increment_percentage' => $request->input('percentage', 5.00),
                        'effective_date' => $request->input('effective_date', now()->toDateString()),
                        'status' => 'pending',
                        'remarks' => "Automated Performance Increment from Appraisal (Score: {$submission->final_weighted_score}%, Grade: {$submission->performance_grade})",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($actionType === 'commendation') {
                if (DB::getSchemaBuilder()->hasTable('tblcensures_commendations')) {
                    $staff = DB::table('tblper')->where('ID', $submission->staff_id)->first();
                    DB::table('tblcensures_commendations')->insert([
                        'fileNo' => $staff ? $staff->fileNo : '',
                        'type' => 'Commendation',
                        'censure_commendation' => "Commendation for Outstanding Appraisal Performance (Score: {$submission->final_weighted_score}%, Grade: {$submission->performance_grade})",
                        'date' => now()->toDateString(),
                        'created_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Appraisal recommendation successfully pushed to " . ucfirst($actionType) . " module."
            ]);
        } catch (\Throwable $th) {
            Log::error('AppraisalApiController pushToAction: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
