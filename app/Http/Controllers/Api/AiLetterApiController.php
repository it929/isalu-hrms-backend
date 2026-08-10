<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiLetterApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/hr/letter-templates
     * List available letter templates.
     */
    public function getTemplates(Request $request)
    {
        $templates = [
            [
                'id'          => 'resignation_acceptance',
                'name'        => 'Resignation Acceptance Letter',
                'description' => 'Formal acceptance of employee resignation with exit date & handover terms.',
            ],
            [
                'id'          => 'job_offer',
                'name'        => 'Employment Offer Letter',
                'description' => 'Official job offer and appointment details for new hires.',
            ],
            [
                'id'          => 'query_notice',
                'name'        => 'Query / Disciplinary Notice',
                'description' => 'Formal administrative query or disciplinary explanation demand.',
            ],
            [
                'id'          => 'promotion_letter',
                'name'        => 'Promotion & Salary Adjustment Letter',
                'description' => 'Official announcement of staff rank promotion and salary step update.',
            ],
            [
                'id'          => 'custom_letter',
                'name'        => 'Custom AI Letter (Prompt Guided)',
                'description' => 'Generate any custom HR letter using AI instructions.',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data'   => $templates,
        ]);
    }

    /**
     * POST /api/nextjs/hr/generate-letter
     * Generates a formal HR letter with ISALU HOSPITAL letterhead metadata.
     */
    public function generateLetter(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                // Graceful fallback for authenticated session without explicit X-User-Id header
                $ctx = [
                    'userId' => $request->header('X-User-Id') ?? 1,
                    'isSuperAdmin' => true,
                    'isAdminStaff' => true,
                ];
            }

            $type = $request->input('type') ?: 'resignation_acceptance';
            $staffId = $request->input('staff_id');
            if (!is_numeric($staffId)) {
                $staffId = null;
            } else {
                $staffId = (int)$staffId;
            }

            $resignationId = $request->input('resignation_id');
            if (!is_numeric($resignationId)) {
                $resignationId = null;
            } else {
                $resignationId = (int)$resignationId;
            }

            $customPrompt = trim((string)$request->input('custom_prompt', ''));
            $effectiveDate = $request->input('effective_date') ?: date('Y-m-d');
            $customRecipient = $request->input('custom_recipient');
            $customDesignation = $request->input('custom_designation');
            $customDepartment = $request->input('custom_department');

            // 1. Resolve Staff Info
            $staff = null;
            $resignation = null;

            if ($staffId) {
                $staff = DB::table('tblper as p')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                    ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                    ->where('p.ID', $staffId)
                    ->select(
                        'p.ID as id',
                        'p.fileNo',
                        'p.surname',
                        'p.first_name',
                        'p.othernames',
                        'd.department',
                        'p.appointment_date',
                        'p.resumption_date',
                        'ss.basic_salary',
                        'ss.declare_salary'
                    )
                    ->first();
            }

            // Check resignation details if applicable
            if ($resignationId) {
                $resignation = DB::table('resignation_requests')->where('id', $resignationId)->first();
            } elseif ($staffId) {
                $resignation = DB::table('resignation_requests')
                    ->where('staff_id', $staffId)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            // Fallback staff parameters
            $staffName = $staff ? trim("{$staff->surname} {$staff->first_name} {$staff->othernames}") : ($customRecipient ?: 'Staff Member');
            $staffIdVal = $staff ? (string)$staff->id : ($staffId ? (string)$staffId : '—');
            $department = $staff ? ($staff->department ?? 'General Operations') : ($customDepartment ?: 'Operations');
            $designation = $customDesignation ?: 'Staff Member';

            // Generate Reference Number
            $refNumber = "IH/HR/LT/" . date('Y') . "/" . sprintf("%04d", rand(100, 9999));
            $todayFormatted = date('d F, Y');

            // Check if OpenAI API Key is configured
            $openAiKey = env('OPENAI_API_KEY');
            $aiGeneratedBody = null;

            if (!empty($openAiKey) && !empty($customPrompt)) {
                $aiGeneratedBody = $this->callOpenAiLlm($openAiKey, $type, $staffName, $department, $customPrompt);
            }

            // Fallback to Smart Structured Template Engine if AI key not present or returned null
            if (!$aiGeneratedBody) {
                $aiGeneratedBody = $this->buildTemplateContent(
                    $type,
                    $staffName,
                    $staffIdVal,
                    $department,
                    $designation,
                    $effectiveDate,
                    $resignation,
                    $customPrompt
                );
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'organization'  => 'ISALU HOSPITAL',
                    'tagline'       => 'Excellence in Healthcare & Patient Services',
                    'ref_number'    => $refNumber,
                    'date'          => $todayFormatted,
                    'recipient'     => [
                        'name'        => $staffName,
                        'staffID'     => $staffIdVal,
                        'fileNo'      => $staffIdVal, // backward compatibility
                        'department'  => $department,
                        'designation' => $designation,
                    ],
                    'subject'       => $aiGeneratedBody['subject'],
                    'salutation'    => "Dear {$staffName},",
                    'body'          => $aiGeneratedBody['body'],
                    'signatory'     => [
                        'name'  => 'Head of Human Resources',
                        'title' => 'Human Resources Department',
                        'org'   => 'ISALU HOSPITAL',
                    ],
                ]
            ]);

        } catch (\Throwable $th) {
            Log::error('AiLetterApiController generateLetter: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Build smart structured template content.
     */
    private function buildTemplateContent($type, $staffName, $staffFileNo, $department, $designation, $effectiveDate, $resignation, $customPrompt)
    {
        $effDateFmt = date('d F, Y', strtotime($effectiveDate));
        
        switch ($type) {
            case 'resignation_acceptance':
                $noticeDate = $resignation ? date('d F, Y', strtotime($resignation->resignation_date)) : date('d F, Y');
                $exitDate = $resignation ? date('d F, Y', strtotime($resignation->resignation_date . ' + 30 days')) : date('d F, Y', strtotime('+30 days'));

                $subject = "ACCEPTANCE OF RESIGNATION — STAFF ID: {$staffFileNo}";
                $body = "We write to acknowledge receipt of your resignation notice dated {$noticeDate}.\n\n"
                    . "Management has accepted your resignation from ISALU HOSPITAL as {$designation} in the {$department} Department. Your last official working day with the hospital will be {$exitDate}.\n\n"
                    . "Please ensure that all hospital properties, identity cards, documentation, and operational records in your custody are fully handed over to your Head of Department prior to your final departure date.\n\n"
                    . ($customPrompt ? "Note / Directive: {$customPrompt}\n\n" : "")
                    . "We express our sincere appreciation for your valuable contributions to ISALU HOSPITAL during your period of service, and we wish you success in your future professional endeavors.";
                break;

            case 'job_offer':
                $subject = "LETTER OF APPOINTMENT / EMPLOYMENT OFFER";
                $body = "On behalf of Management, we are pleased to offer you appointment as {$designation} in the {$department} Department at ISALU HOSPITAL, with effect from {$effDateFmt}.\n\n"
                    . "Your appointment is subject to the terms and administrative policies governing service at ISALU HOSPITAL. You will be expected to adhere strictly to healthcare standards, confidentiality agreements, and operational directives.\n\n"
                    . ($customPrompt ? "Special Terms: {$customPrompt}\n\n" : "")
                    . "Please sign and return the duplicate copy of this letter as confirmation of your formal acceptance of this appointment offer.\n\n"
                    . "We welcome you to the ISALU HOSPITAL team and look forward to a rewarding professional relationship.";
                break;

            case 'query_notice':
                $subject = "ADMINISTRATIVE QUERY / FORMAL EXPLANATION DEMAND";
                $body = "It has been brought to the attention of Management that on or about {$effDateFmt}, an administrative concern was raised regarding your official conduct/performance in the {$department} Department.\n\n"
                    . ($customPrompt ? "Specific Grounds / Incident: {$customPrompt}\n\n" : "You are required to explain why disciplinary measures should not be instituted against you regarding this matter.\n\n")
                    . "You are hereby directed to submit a written explanation to the undersigned within forty-eight (48) hours of receiving this query letter.\n\n"
                    . "Failure to respond within the stipulated timeline will be interpreted as a waiver of your right to be heard, and appropriate administrative actions will be taken accordingly.";
                break;

            case 'promotion_letter':
                $subject = "PROMOTION AND SALARY ADJUSTMENT ANNOUNCEMENT";
                $body = "Management is pleased to inform you that in recognition of your dedication, performance, and outstanding service to ISALU HOSPITAL, you have been promoted to the rank of {$designation} in the {$department} Department, effective {$effDateFmt}.\n\n"
                    . "With this promotion, your remuneration structure and administrative responsibilities have been adjusted in accordance with the hospital's current staff scale.\n\n"
                    . ($customPrompt ? "Additional Details: {$customPrompt}\n\n" : "")
                    . "Management trusts that this advancement will inspire you to maintain high standards of clinical and operational excellence in your new role. Congratulations!";
                break;

            case 'custom_letter':
            default:
                $subject = "OFFICIAL CORRESPONDENCE — ISALU HOSPITAL";
                $body = "We write regarding your service as {$designation} in the {$department} Department at ISALU HOSPITAL.\n\n"
                    . ($customPrompt ? "{$customPrompt}\n\n" : "Please be informed of administrative updates regarding your staff profile and operational directives.\n\n")
                    . "Should you require further clarification, please do not hesitate to contact the Human Resources Department.";
                break;
        }

        return [
            'subject' => $subject,
            'body'    => $body,
        ];
    }

    /**
     * Optional LLM Integration via OpenAI API.
     */
    private function callOpenAiLlm($apiKey, $type, $staffName, $department, $prompt)
    {
        try {
            $systemPrompt = "You are the Head of Human Resources at ISALU HOSPITAL. Write a formal, professional HR letter for staff member '{$staffName}' in '{$department}'. Return a JSON object with keys 'subject' and 'body'.";
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Type: {$type}\nInstructions: {$prompt}"],
                ],
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $contentStr = $json['choices'][0]['message']['content'] ?? '';
                $parsed = json_decode($contentStr, true);
                if (isset($parsed['subject']) && isset($parsed['body'])) {
                    return [
                        'subject' => strtoupper($parsed['subject']),
                        'body'    => $parsed['body'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI LLM call failed, falling back to template: ' . $e->getMessage());
        }

        return null;
    }
}
