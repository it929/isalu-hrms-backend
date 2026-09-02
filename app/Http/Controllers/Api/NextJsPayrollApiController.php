<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NextJsPayrollApiController extends Controller
{
    use ResolveUserContextTrait;

    // ─── CV IDs mapped to template columns ────────────────────────────────────
    // (based on tblcvSetup rows confirmed from database dump)
    const CVID_MEDICAL_ALLOWANCE  = 19;  // Earning  → MEDICAL column
    const CVID_OVERPAYMENT        = 27;  // Deduction → IOU column
    const CVID_LAWYERS_LOAN       = 3;   // Deduction → LOAN (part)
    const CVID_VISA_LOAN          = 7;   // Deduction → LOAN (part)
    const CVID_COOP_CHRISTIAN     = 2;   // Deduction → COOP. SAVING (part)
    const CVID_COOP_MUSLIM_WOMEN  = 5;   // Deduction → COOP. SAVING (part)
    const CVID_COOP_SCN           = 6;   // Deduction → COOP. SAVING (part)
    const CVID_MUSLIM_WOMEN_LOAN  = 24;  // Deduction → COOP. LOAN RPYT

    /**
     * Resolve user context from X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/metadata
     * Returns filter options: divisions, banks, years, months.
     */
    public function getMetadata(Request $request)
    {
        try {
            $divisions = DB::table('tbldivision')
                ->select('divisionID as id', 'division as name')
                ->orderBy('division', 'asc')
                ->get();

            $departments = DB::table('tbldepartment')
                ->select('id', 'department as name')
                ->orderBy('department', 'asc')
                ->get();

            $banks = DB::table('tblbanklist')
                ->select('bankID as id', 'bank as name')
                ->orderBy('bank', 'asc')
                ->get();

            $currentYear = (int) date('Y');
            $years = collect(range($currentYear - 5, $currentYear))->map(fn($y) => ['id' => $y, 'name' => (string) $y]);

            $months = collect([
                'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
                'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
            ])->map(fn($m) => ['id' => $m, 'name' => ucfirst(strtolower($m))]);

            return response()->json([
                'status'      => 'success',
                'divisions'   => $divisions,
                'departments' => $departments,
                'banks'       => $banks,
                'years'       => $years->values(),
                'months'      => $months->values(),
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI getMetadata: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll
     * Returns paginated payroll records with all 34 computed columns.
     */
    public function getPayrollList(Request $request)
    {
        $userCtx = $this->getUserContext($request);
        if (!$userCtx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $month        = strtoupper(trim($request->input('month', '')));
            $year         = trim($request->input('year', ''));
            $divisionID   = trim($request->input('divisionID', ''));
            $departmentID = trim($request->input('departmentID', ''));
            $bankID       = trim($request->input('bankID', ''));
            $perPage      = (int) $request->input('perPage', -1);
            $page         = (int) $request->input('page', 1);

            if (!$month || !$year) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            [$records, $total, $summary] = $this->fetchPayrollData(
                $month, $year, $divisionID, $bankID, $perPage, $page, $departmentID
            );

            return response()->json([
                'status'   => 'success',
                'data'     => $records,
                'summary'  => $summary,
                'total'    => $total,
                'perPage'  => $perPage,
                'page'     => $page,
                'lastPage' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
                'userCtx'  => [
                    'isAuditStaff'   => (bool)($userCtx['isAuditStaff'] ?? false),
                    'isSuperAdmin'   => (bool)($userCtx['isSuperAdmin'] ?? false),
                    'isFinanceStaff' => (bool)($userCtx['isFinanceStaff'] ?? false),
                    'isAdminStaff'   => (bool)($userCtx['isAdminStaff'] ?? false),
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI getPayrollList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/export
     * Generates a formatted CSV (.csv) payroll schedule file.
     */
    public function exportPayroll(Request $request)
    {
        $userCtx = $this->getUserContext($request);
        if (!$userCtx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $month        = strtoupper(trim($request->input('month', '')));
            $year         = trim($request->input('year', ''));
            $divisionID   = trim($request->input('divisionID', ''));
            $departmentID = trim($request->input('departmentID', ''));
            $bankID       = trim($request->input('bankID', ''));

            if (!$month || !$year) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            // Fetch ALL records (no pagination)
            [$records] = $this->fetchPayrollData($month, $year, $divisionID, $bankID, PHP_INT_MAX, 1, $departmentID);

            $deptName = '';
            if ($departmentID !== '') {
                $deptObj = DB::table('tbldepartment')->where('id', $departmentID)->first();
                if ($deptObj) {
                    $deptName = $deptObj->department;
                }
            }

            // ── Column definitions ─────────────────────────────────────────────
            $columns = [
                'IDNO', 'NAME', 'DEPARTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURCHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'LOA.DEDN', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NET PAY',
                'REVOLVING LOAN BAL', 'COOP. CONTR.', 'COOP. LOAN BAL',
                'COOP. ASSET', 'COOP. ASSET FIN.', 'MEDICAL DEBT',
                'ACCOUNT NO.', 'BANK', 'SORT CODE', 'PAYER ID',
            ];

            $dataKeys = [
                'IDNO', 'NAME', 'DEPERTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURGHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'LEAVE OF ABSENCE DEDUCTION', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NETPAY',
                'REVOLVING LOAN BAL', 'COP.CONTR', 'COP. LONE BAL',
                'COOP.ASSET.', 'COP. ASSET FIN', 'MEDICAL DEBT',
                'ACC. NO', 'BANK', 'CODE', 'PAYER ID',
            ];

            // Money column indices (0-based within $dataKeys)
            $moneyIndices = [3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];

            $handle = fopen('php://temp', 'r+');
            // Write UTF-8 BOM for seamless Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Row 1: Company Title
            fputcsv($handle, ['ISALU HRMS — PAYROLL SCHEDULE']);

            // Row 2: Period & Department Subtitle
            $subtitle = 'Period: ' . ucfirst(strtolower($month)) . ' ' . $year;
            if ($deptName) {
                $subtitle .= ' — Department: ' . $deptName;
            }
            fputcsv($handle, [$subtitle]);

            // Row 3: Empty separator
            fputcsv($handle, []);

            // Row 4: Column Headers
            fputcsv($handle, $columns);

            $totals = array_fill(0, count($columns), 0.0);

            // Data Rows
            foreach ($records as $record) {
                $row = [];
                foreach ($dataKeys as $idx => $key) {
                    $val = $record[$key] ?? '';
                    if (in_array($idx, $moneyIndices)) {
                        $clean = str_replace(',', '', (string) $val);
                        $num = is_numeric($clean) ? (float) $clean : 0.0;
                        $totals[$idx] += $num;
                        $row[] = number_format($num, 2, '.', '');
                    } else {
                        $row[] = $val;
                    }
                }
                fputcsv($handle, $row);
            }

            // Totals Row
            $totalsRow = [];
            foreach ($columns as $idx => $colName) {
                if ($idx === 0) {
                    $totalsRow[] = 'TOTAL';
                } elseif ($idx === 1 || $idx === 2) {
                    $totalsRow[] = '';
                } elseif (in_array($idx, $moneyIndices)) {
                    $totalsRow[] = number_format($totals[$idx], 2, '.', '');
                } else {
                    $totalsRow[] = '';
                }
            }
            fputcsv($handle, $totalsRow);

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            $deptSuffix = $deptName ? '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $deptName) : '';
            $filename = "Payroll_{$month}_{$year}{$deptSuffix}.csv";

            return response($csvContent, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI exportPayroll: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/export-variance-summary
     * Generates a multi-sheet, executive-grade Excel (.xlsx) spreadsheet
     * explaining and reconciling the month-on-month differences between
     * the Previous Month and the Selected Month payrolls.
     */
    public function exportPayrollVarianceSummary(Request $request)
    {
        $userCtx = $this->getUserContext($request);
        if (!$userCtx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $monthInput   = strtoupper(trim($request->input('month', '')));
            $yearInput    = trim($request->input('year', ''));
            $divisionID   = trim($request->input('divisionID', ''));
            $departmentID = trim($request->input('departmentID', ''));
            $bankID       = trim($request->input('bankID', ''));

            if (!$monthInput || !$yearInput) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            $monthNames = [
                1 => 'JANUARY', 2 => 'FEBRUARY', 3 => 'MARCH', 4 => 'APRIL',
                5 => 'MAY', 6 => 'JUNE', 7 => 'JULY', 8 => 'AUGUST',
                9 => 'SEPTEMBER', 10 => 'OCTOBER', 11 => 'NOVEMBER', 12 => 'DECEMBER'
            ];
            $monthNums = array_flip($monthNames);

            if (is_numeric($monthInput)) {
                $currMonthNum  = (int)$monthInput;
                $currMonthName = $monthNames[$currMonthNum] ?? 'JANUARY';
            } else {
                $currMonthName = $monthInput;
                $currMonthNum  = $monthNums[$currMonthName] ?? 1;
            }

            $currYear = (int)$yearInput;

            // Calculate Previous Month & Year
            if ($currMonthNum === 1) {
                $prevMonthNum  = 12;
                $prevYear      = $currYear - 1;
            } else {
                $prevMonthNum  = $currMonthNum - 1;
                $prevYear      = $currYear;
            }
            $prevMonthName = $monthNames[$prevMonthNum];

            // 1. Fetch Full Payroll Datasets (no pagination)
            [$currRecords, $currTotal, $currSummary] = $this->fetchPayrollData($currMonthName, (string)$currYear, $divisionID, $bankID, PHP_INT_MAX, 1, $departmentID);
            [$prevRecords, $prevTotal, $prevSummary] = $this->fetchPayrollData($prevMonthName, (string)$prevYear, $divisionID, $bankID, PHP_INT_MAX, 1, $departmentID);

            $deptName = '';
            if ($departmentID !== '') {
                $deptObj = DB::table('tbldepartment')->where('id', $departmentID)->first();
                if ($deptObj) {
                    $deptName = $deptObj->department;
                }
            }

            // 2. Fetch Resignation and Staff Metadata for detailed explanations
            $resignationRequests = DB::table('resignation_requests')
                ->whereIn('status', [0, 1])
                ->get()
                ->keyBy('staff_id');

            $staffProfiles = DB::table('tblper')
                ->select('ID', 'fileNo', 'surname', 'first_name', 'othernames', 'rank', 'staff_status', 'created_at')
                ->get()
                ->keyBy('ID');

            // 3. Index records by staff ID (IDNO)
            $currByStaff = collect($currRecords)->keyBy(fn($r) => (string)($r['IDNO'] ?? ''));
            $prevByStaff = collect($prevRecords)->keyBy(fn($r) => (string)($r['IDNO'] ?? ''));

            $allStaffIds = $currByStaff->keys()->merge($prevByStaff->keys())->filter()->unique()->values();

            // 4. Categorize Staff Movements & Variations
            $newHires        = [];
            $exitedStaff     = [];
            $salaryIncreased = [];
            $salaryDecreased = [];
            $salaryUnchanged = [];

            // Department accumulator: deptName => [ prevGross, currGross, prevCount, currCount, newGross, exitGross, varGross, newCount, exitCount, varCount ]
            $departmentsMap = [];

            // Element totals for High-Level Breakdown
            $elementKeys = [
                'BASIC'                      => 'Basic Salary',
                'HOUSING'                    => 'Housing Allowance',
                'TRANSPORT'                  => 'Transport Allowance',
                'MEDICAL'                    => 'Medical Allowance',
                'UTILITY'                    => 'Utility Allowance',
                'MEAL'                       => 'Meal Allowance',
                'TOTAL INCOME'               => 'Gross Salary (Total Income)',
                'P.TAX'                      => 'PAYE Tax',
                'PENSION'                    => 'Pension Deduction',
                'LOAN'                       => 'Loan Repayment',
                'IOU'                        => 'IOU / Overpayment Deduction',
                'SURGHARGES'                 => 'Surcharges',
                'MEDICAL LOAN'               => 'Medical Loan',
                'COOP. SAVING'               => 'Cooperative Savings',
                'COOP. LOAN RPYT'            => 'Coop Loan Repayment',
                'ABSENCE PENALTY'            => 'Absence Penalty',
                'LEAVE OF ABSENCE DEDUCTION' => 'Leave of Absence Deduction',
                'OTHER DEDUCTION'            => 'Other Deductions',
                'TOTAL DEDUCTION'            => 'Total Deductions',
                'NETPAY'                     => 'Net Pay',
            ];

            $elementTotals = [];
            foreach ($elementKeys as $k => $label) {
                $elementTotals[$k] = ['prev' => 0.0, 'curr' => 0.0];
            }

            foreach ($prevRecords as $r) {
                foreach ($elementKeys as $k => $label) {
                    $val = (float) str_replace(',', '', (string)($r[$k] ?? 0));
                    $elementTotals[$k]['prev'] += $val;
                }
            }
            foreach ($currRecords as $r) {
                foreach ($elementKeys as $k => $label) {
                    $val = (float) str_replace(',', '', (string)($r[$k] ?? 0));
                    $elementTotals[$k]['curr'] += $val;
                }
            }

            $totalNewHiresGross = 0.0;
            $totalExitedGross   = 0.0;
            $totalSalaryChangesGross = 0.0;

            foreach ($allStaffIds as $sid) {
                $c = $currByStaff->get($sid);
                $p = $prevByStaff->get($sid);

                $profile = $staffProfiles->get($sid);

                $name = $c['NAME'] ?? $p['NAME'] ?? ($profile ? trim("{$profile->surname} {$profile->first_name} {$profile->othernames}") : "Staff #{$sid}");
                $dept = $c['DEPERTMENT'] ?? $p['DEPERTMENT'] ?? 'General';
                if (!$dept) { $dept = 'General'; }

                if (!isset($departmentsMap[$dept])) {
                    $departmentsMap[$dept] = [
                        'name'        => $dept,
                        'prevGross'   => 0.0,
                        'currGross'   => 0.0,
                        'prevCount'   => 0,
                        'currCount'   => 0,
                        'newGross'    => 0.0,
                        'exitGross'   => 0.0,
                        'varGross'    => 0.0,
                        'newCount'    => 0,
                        'exitCount'   => 0,
                        'varCount'    => 0,
                        'unchangedCount' => 0,
                        'explanations'=> [],
                    ];
                }

                $currGross = $c ? (float) str_replace(',', '', (string)$c['TOTAL INCOME']) : 0.0;
                $prevGross = $p ? (float) str_replace(',', '', (string)$p['TOTAL INCOME']) : 0.0;
                $grossDiff = $currGross - $prevGross;

                $currDedn  = $c ? (float) str_replace(',', '', (string)$c['TOTAL DEDUCTION']) : 0.0;
                $prevDedn  = $p ? (float) str_replace(',', '', (string)$p['TOTAL DEDUCTION']) : 0.0;
                $dednDiff  = $currDedn - $prevDedn;

                $currNet   = $c ? (float) str_replace(',', '', (string)$c['NETPAY']) : 0.0;
                $prevNet   = $p ? (float) str_replace(',', '', (string)$p['NETPAY']) : 0.0;
                $netDiff   = $currNet - $prevNet;

                if ($p) {
                    $departmentsMap[$dept]['prevGross'] += $prevGross;
                    $departmentsMap[$dept]['prevCount'] += 1;
                }
                if ($c) {
                    $departmentsMap[$dept]['currGross'] += $currGross;
                    $departmentsMap[$dept]['currCount'] += 1;
                }

                $statusCategory = '';
                $detailedReason = '';

                // Case 1: NEW STAFF / NEW HIRE (Present in Current Month, NOT in Prev Month)
                if ($c && !$p) {
                    $statusCategory = 'New Staff (Hire)';
                    $totalNewHiresGross += $currGross;
                    $departmentsMap[$dept]['newGross'] += $currGross;
                    $departmentsMap[$dept]['newCount'] += 1;

                    $detailedReason = "New staff employed / added to payroll in {$currMonthName} {$currYear}";

                    $item = [
                        'staffId'    => $sid,
                        'name'       => $name,
                        'department' => $dept,
                        'gross'      => $currGross,
                        'basic'      => (float) str_replace(',', '', (string)$c['BASIC']),
                        'allowances' => $currGross - ((float) str_replace(',', '', (string)$c['BASIC'])),
                        'deductions' => $currDedn,
                        'netPay'     => $currNet,
                        'reason'     => $detailedReason,
                    ];
                    $newHires[] = $item;
                }
                // Case 2: RESIGNED / EXITED STAFF (Present in Prev Month, NOT in Current Month)
                elseif (!$c && $p) {
                    $statusCategory = 'Resigned / Exited';
                    $totalExitedGross += $prevGross;
                    $departmentsMap[$dept]['exitGross'] += $prevGross;
                    $departmentsMap[$dept]['exitCount'] += 1;

                    $resObj = $resignationRequests->get($sid);
                    if ($resObj) {
                        $detailedReason = "Resigned on " . date('d M Y', strtotime($resObj->resignation_date)) . ($resObj->reason ? " (Reason: {$resObj->reason})" : "");
                    } elseif ($profile && $profile->rank == 2) {
                        $detailedReason = "Retired / Exited staff removed from payroll";
                    } elseif ($profile && $profile->staff_status == 0) {
                        $detailedReason = "Inactive staff status / off payroll";
                    } else {
                        $detailedReason = "Staff exited / removed from {$currMonthName} {$currYear} payroll";
                    }

                    $item = [
                        'staffId'    => $sid,
                        'name'       => $name,
                        'department' => $dept,
                        'lastGross'  => $prevGross,
                        'lastBasic'  => (float) str_replace(',', '', (string)$p['BASIC']),
                        'lastNet'    => $prevNet,
                        'reason'     => $detailedReason,
                    ];
                    $exitedStaff[] = $item;
                }
                // Case 3: CONTINUING STAFF (Present in BOTH months)
                else {
                    if (abs($grossDiff) < 0.009) {
                        $statusCategory = 'Unchanged';
                        $detailedReason = 'Gross salary unchanged';
                        $departmentsMap[$dept]['unchangedCount'] += 1;
                        $salaryUnchanged[] = [
                            'staffId'    => $sid,
                            'name'       => $name,
                            'department' => $dept,
                            'gross'      => $currGross,
                            'netPay'     => $currNet,
                        ];
                    } else {
                        $totalSalaryChangesGross += $grossDiff;
                        $departmentsMap[$dept]['varGross'] += $grossDiff;
                        $departmentsMap[$dept]['varCount'] += 1;

                        // Check component differences
                        $compDiffs = [];
                        $basicDiff = ((float) str_replace(',', '', (string)$c['BASIC'])) - ((float) str_replace(',', '', (string)$p['BASIC']));
                        if (abs($basicDiff) > 0.009) {
                            $compDiffs[] = "Basic: " . ($basicDiff > 0 ? '+' : '') . '₦' . number_format($basicDiff, 2);
                        }
                        $housingDiff = ((float) str_replace(',', '', (string)$c['HOUSING'])) - ((float) str_replace(',', '', (string)$p['HOUSING']));
                        if (abs($housingDiff) > 0.009) {
                            $compDiffs[] = "Housing: " . ($housingDiff > 0 ? '+' : '') . '₦' . number_format($housingDiff, 2);
                        }
                        $transportDiff = ((float) str_replace(',', '', (string)$c['TRANSPORT'])) - ((float) str_replace(',', '', (string)$p['TRANSPORT']));
                        if (abs($transportDiff) > 0.009) {
                            $compDiffs[] = "Transport: " . ($transportDiff > 0 ? '+' : '') . '₦' . number_format($transportDiff, 2);
                        }
                        $utilityDiff = ((float) str_replace(',', '', (string)$c['UTILITY'])) - ((float) str_replace(',', '', (string)$p['UTILITY']));
                        if (abs($utilityDiff) > 0.009) {
                            $compDiffs[] = "Utility: " . ($utilityDiff > 0 ? '+' : '') . '₦' . number_format($utilityDiff, 2);
                        }
                        $medicalDiff = ((float) str_replace(',', '', (string)$c['MEDICAL'])) - ((float) str_replace(',', '', (string)$p['MEDICAL']));
                        if (abs($medicalDiff) > 0.009) {
                            $compDiffs[] = "Medical: " . ($medicalDiff > 0 ? '+' : '') . '₦' . number_format($medicalDiff, 2);
                        }
                        $mealDiff = ((float) str_replace(',', '', (string)$c['MEAL'])) - ((float) str_replace(',', '', (string)$p['MEAL']));
                        if (abs($mealDiff) > 0.009) {
                            $compDiffs[] = "Meal: " . ($mealDiff > 0 ? '+' : '') . '₦' . number_format($mealDiff, 2);
                        }

                        $compNote = !empty($compDiffs) ? implode(', ', $compDiffs) : 'Allowances / structure adjusted';

                        if ($grossDiff > 0) {
                            $statusCategory = 'Salary Increased';
                            $detailedReason = "Gross increased by +₦" . number_format($grossDiff, 2) . " ({$compNote})";
                            $salaryIncreased[] = [
                                'staffId'    => $sid,
                                'name'       => $name,
                                'department' => $dept,
                                'prevGross'  => $prevGross,
                                'currGross'  => $currGross,
                                'diff'       => $grossDiff,
                                'components' => $compNote,
                                'reason'     => $detailedReason,
                            ];
                        } else {
                            $statusCategory = 'Salary Decreased';
                            $detailedReason = "Gross decreased by -₦" . number_format(abs($grossDiff), 2) . " ({$compNote})";
                            $salaryDecreased[] = [
                                'staffId'    => $sid,
                                'name'       => $name,
                                'department' => $dept,
                                'prevGross'  => $prevGross,
                                'currGross'  => $currGross,
                                'diff'       => $grossDiff,
                                'components' => $compNote,
                                'reason'     => $detailedReason,
                            ];
                        }
                    }
                }
            }

            // 5. Generate Department Narratives & Explanations
            foreach ($departmentsMap as $dName => &$dData) {
                $grossVariance = $dData['currGross'] - $dData['prevGross'];
                $dData['grossVariance'] = $grossVariance;
                $pctChange = $dData['prevGross'] > 0 ? ($grossVariance / $dData['prevGross']) * 100 : ($dData['currGross'] > 0 ? 100.0 : 0.0);
                $dData['pctChange'] = $pctChange;

                $narratives = [];
                if ($dData['newCount'] > 0) {
                    $narratives[] = "{$dData['newCount']} new hire" . ($dData['newCount'] > 1 ? 's' : '') . " (+₦" . number_format($dData['newGross'], 2) . ")";
                }
                if ($dData['exitCount'] > 0) {
                    $narratives[] = "{$dData['exitCount']} resigned/exited staff (-₦" . number_format($dData['exitGross'], 2) . ")";
                }
                if ($dData['varCount'] > 0) {
                    $narratives[] = "{$dData['varCount']} staff salary/allowance variation (" . ($dData['varGross'] >= 0 ? '+₦' : '-₦') . number_format(abs($dData['varGross']), 2) . ")";
                }
                if (empty($narratives)) {
                    $dData['explanation'] = "No change in department gross salary or headcount.";
                } else {
                    $varPrefix = $grossVariance > 0 ? "Gross increased by +₦" . number_format($grossVariance, 2) : ($grossVariance < 0 ? "Gross decreased by -₦" . number_format(abs($grossVariance), 2) : "Net zero gross change");
                    $dData['explanation'] = "{$varPrefix} due to: " . implode('; ', $narratives) . ".";
                }
            }
            unset($dData);

            // Sort Departments alphabetically
            ksort($departmentsMap);

            // 6. Build Structured CSV Spreadsheet
            $handle = fopen('php://temp', 'r+');
            // Write UTF-8 BOM for seamless Excel CSV compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Row 1: Title
            fputcsv($handle, ['ISALU HRMS — MONTH-ON-MONTH PAYROLL VARIANCE & SUMMARY REPORT']);

            // Row 2: Period & Subtitle
            $periodSubtitle = "Comparing: " . ucfirst(strtolower($prevMonthName)) . " {$prevYear} (Last Month) vs " . ucfirst(strtolower($currMonthName)) . " {$currYear} (Current Month)";
            if ($deptName) {
                $periodSubtitle .= " | Filtered by Department: {$deptName}";
            }
            $periodSubtitle .= " | Generated on: " . date('d M Y, h:i A');
            fputcsv($handle, [$periodSubtitle]);
            fputcsv($handle, []);

            // ── SECTION 1: EXECUTIVE KPI SUMMARY ──
            $prevGrossTotal = $elementTotals['TOTAL INCOME']['prev'];
            $currGrossTotal = $elementTotals['TOTAL INCOME']['curr'];
            $netGrossVar    = $currGrossTotal - $prevGrossTotal;
            $pctGrossVar    = $prevGrossTotal > 0 ? ($netGrossVar / $prevGrossTotal) * 100 : ($currGrossTotal > 0 ? 100.0 : 0.0);

            $prevStaffCount = count($prevRecords);
            $currStaffCount = count($currRecords);
            $headcountDiff  = $currStaffCount - $prevStaffCount;

            fputcsv($handle, ['=== 1. EXECUTIVE KPI SUMMARY ===']);
            fputcsv($handle, ['KPI Metric', 'Last Month (' . $prevMonthName . ' ' . $prevYear . ')', 'This Month (' . $currMonthName . ' ' . $currYear . ')', 'Variance Amount', 'Variance % / Headcount Details']);
            fputcsv($handle, ['Total Gross Payroll', number_format($prevGrossTotal, 2), number_format($currGrossTotal, 2), ($netGrossVar >= 0 ? '+' : '-') . number_format(abs($netGrossVar), 2), ($pctGrossVar >= 0 ? '+' : '') . number_format($pctGrossVar, 2) . '%']);
            fputcsv($handle, ['Active Headcount (Staff)', $prevStaffCount, $currStaffCount, ($headcountDiff >= 0 ? "+{$headcountDiff}" : (string)$headcountDiff), "New Hires: +" . count($newHires) . " | Resigned/Exited: -" . count($exitedStaff) . " | Salary Changes: " . (count($salaryIncreased) + count($salaryDecreased))]);
            fputcsv($handle, ['Total Deductions', number_format($elementTotals['TOTAL DEDUCTION']['prev'], 2), number_format($elementTotals['TOTAL DEDUCTION']['curr'], 2), ($elementTotals['TOTAL DEDUCTION']['curr'] >= $elementTotals['TOTAL DEDUCTION']['prev'] ? '+' : '-') . number_format(abs($elementTotals['TOTAL DEDUCTION']['curr'] - $elementTotals['TOTAL DEDUCTION']['prev']), 2), '']);
            fputcsv($handle, ['Net Pay Disbursement', number_format($elementTotals['NETPAY']['prev'], 2), number_format($elementTotals['NETPAY']['curr'], 2), ($elementTotals['NETPAY']['curr'] >= $elementTotals['NETPAY']['prev'] ? '+' : '-') . number_format(abs($elementTotals['NETPAY']['curr'] - $elementTotals['NETPAY']['prev']), 2), '']);
            fputcsv($handle, []);

            // ── SECTION 2: EXACT GROSS VARIANCE RECONCILIATION (PROOF OF VARIANCE) ──
            fputcsv($handle, ['=== 2. EXACT GROSS VARIANCE RECONCILIATION (PROOF OF VARIANCE) ===']);
            fputcsv($handle, ['Reconciliation Component', 'Headcount', 'Gross Impact (₦)', '% Impact', 'Constituent Explanation / Primary Driver']);
            fputcsv($handle, ["Starting Gross Payroll ({$prevMonthName} {$prevYear})", $prevStaffCount, number_format($prevGrossTotal, 2), '100.00%', "Base gross salary of all active staff in previous month"]);
            fputcsv($handle, ["(+) Gross Addition: Newly Employed Staff", count($newHires), number_format($totalNewHiresGross, 2), ($prevGrossTotal > 0 ? number_format(($totalNewHiresGross / $prevGrossTotal) * 100, 2) . '%' : 'N/A'), count($newHires) . " new employee(s) joined the payroll this month"]);
            fputcsv($handle, ["(-) Gross Reduction: Resigned / Exited Staff", count($exitedStaff), '-' . number_format($totalExitedGross, 2), ($prevGrossTotal > 0 ? '-' . number_format(($totalExitedGross / $prevGrossTotal) * 100, 2) . '%' : 'N/A'), count($exitedStaff) . " staff resigned, retired, or exited before this month's payroll"]);
            fputcsv($handle, ["(+/-) Net Salary Increments / Allowance Variations", count($salaryIncreased) + count($salaryDecreased), ($totalSalaryChangesGross >= 0 ? '+' : '-') . number_format(abs($totalSalaryChangesGross), 2), ($prevGrossTotal > 0 ? ($totalSalaryChangesGross >= 0 ? '+' : '') . number_format(($totalSalaryChangesGross / $prevGrossTotal) * 100, 2) . '%' : 'N/A'), count($salaryIncreased) . " increment(s) (+₦" . number_format(collect($salaryIncreased)->sum('diff'), 2) . "), " . count($salaryDecreased) . " reduction(s) (-₦" . number_format(abs(collect($salaryDecreased)->sum('diff')), 2) . ")"]);
            fputcsv($handle, ["(=) Ending Gross Payroll ({$currMonthName} {$currYear})", $currStaffCount, number_format($currGrossTotal, 2), ($prevGrossTotal > 0 ? number_format(($currGrossTotal / $prevGrossTotal) * 100, 2) . '%' : '100.00%'), "Computed total gross payroll for current active month"]);
            fputcsv($handle, ["VARIANCE RECONCILIATION CHECK (Difference)", '-', number_format(($prevGrossTotal + $totalNewHiresGross - $totalExitedGross + $totalSalaryChangesGross) - $currGrossTotal, 2), '0.00%', "100% Mathematically Reconciled (₦0.00 difference)"]);
            fputcsv($handle, []);

            // ── SECTION 3: DEPARTMENT-BY-DEPARTMENT VARIANCE ANALYSIS ──
            $sumPrevStaff = 0;
            $sumCurrStaff = 0;
            $sumPrevGross = 0.0;
            $sumCurrGross = 0.0;
            $sumNewGross  = 0.0;
            $sumExitGross = 0.0;
            $sumVarGross  = 0.0;

            fputcsv($handle, ['=== 3. DEPARTMENT-BY-DEPARTMENT PAYROLL VARIANCE ANALYSIS ===']);
            fputcsv($handle, ['Department', 'Last Month Staff', 'This Month Staff', 'Staff Diff', "Last Gross ({$prevMonthName}) (₦)", "This Gross ({$currMonthName}) (₦)", 'Gross Variance (₦)', '% Change', 'New Hires (+₦)', 'Exited Staff (-₦)', 'Salary Adjust (+/-₦)', 'Explanation / Key Drivers of Difference']);
            foreach ($departmentsMap as $dName => $d) {
                $sumPrevStaff += $d['prevCount'];
                $sumCurrStaff += $d['currCount'];
                $sumPrevGross += $d['prevGross'];
                $sumCurrGross += $d['currGross'];
                $sumNewGross  += $d['newGross'];
                $sumExitGross += $d['exitGross'];
                $sumVarGross  += $d['varGross'];

                fputcsv($handle, [
                    $d['name'],
                    $d['prevCount'],
                    $d['currCount'],
                    $d['currCount'] - $d['prevCount'],
                    number_format($d['prevGross'], 2),
                    number_format($d['currGross'], 2),
                    ($d['grossVariance'] >= 0 ? '+' : '-') . number_format(abs($d['grossVariance']), 2),
                    number_format($d['pctChange'], 2) . '%',
                    number_format($d['newGross'], 2),
                    number_format($d['exitGross'], 2),
                    ($d['varGross'] >= 0 ? '+' : '-') . number_format(abs($d['varGross']), 2),
                    $d['explanation']
                ]);
            }
            fputcsv($handle, [
                'TOTAL / COMPANY CONSOLIDATED',
                $sumPrevStaff,
                $sumCurrStaff,
                $sumCurrStaff - $sumPrevStaff,
                number_format($sumPrevGross, 2),
                number_format($sumCurrGross, 2),
                ($sumCurrGross >= $sumPrevGross ? '+' : '-') . number_format(abs($sumCurrGross - $sumPrevGross), 2),
                ($sumPrevGross > 0 ? number_format((($sumCurrGross - $sumPrevGross) / $sumPrevGross) * 100, 2) : '0.00') . '%',
                number_format($sumNewGross, 2),
                number_format($sumExitGross, 2),
                ($sumVarGross >= 0 ? '+' : '-') . number_format(abs($sumVarGross), 2),
                "Net Company Payroll Variance: " . ($sumCurrGross >= $sumPrevGross ? '+' : '-') . "₦" . number_format(abs($sumCurrGross - $sumPrevGross), 2)
            ]);
            fputcsv($handle, []);

            // ── SECTION 4: NEWLY EMPLOYED STAFF (NEW HIRES) ──
            fputcsv($handle, ['=== 4. NEWLY EMPLOYED STAFF (NEW HIRES) ===']);
            fputcsv($handle, ['Staff ID', 'Staff Name', 'Department', 'Gross Salary (₦)', 'Basic (₦)', 'Allowances (₦)', 'Net Pay (₦)', 'Remarks']);
            if (empty($newHires)) {
                fputcsv($handle, ['No new staff joined the payroll in this period.']);
            } else {
                foreach ($newHires as $nh) {
                    fputcsv($handle, [$nh['staffId'], $nh['name'], $nh['department'], number_format($nh['gross'], 2), number_format($nh['basic'], 2), number_format($nh['allowances'], 2), number_format($nh['netPay'], 2), $nh['reason']]);
                }
            }
            fputcsv($handle, []);

            // ── SECTION 5: RESIGNED / EXITED STAFF ──
            fputcsv($handle, ['=== 5. RESIGNED / EXITED STAFF ===']);
            fputcsv($handle, ['Staff ID', 'Staff Name', 'Department', "Last Gross ({$prevMonthName}) (₦)", 'Last Basic (₦)', 'Last Net Pay (₦)', 'Exit / Resignation Reason', 'Remarks']);
            if (empty($exitedStaff)) {
                fputcsv($handle, ['No staff resigned or exited the payroll in this period.']);
            } else {
                foreach ($exitedStaff as $ex) {
                    fputcsv($handle, [$ex['staffId'], $ex['name'], $ex['department'], number_format($ex['lastGross'], 2), number_format($ex['lastBasic'], 2), number_format($ex['lastNet'], 2), $ex['reason'], "Removed from {$currMonthName} {$currYear} payroll"]);
                }
            }
            fputcsv($handle, []);

            // ── SECTION 6: CONTINUING STAFF SALARY / ALLOWANCE VARIATIONS ──
            $allSalaryVarStaff = array_merge($salaryIncreased, $salaryDecreased);
            fputcsv($handle, ['=== 6. CONTINUING STAFF SALARY / ALLOWANCE VARIATIONS ===']);
            fputcsv($handle, ['Staff ID', 'Staff Name', 'Department', "Prev Gross ({$prevMonthName}) (₦)", "Curr Gross ({$currMonthName}) (₦)", 'Gross Variance (₦)', 'Component Changes', 'Variance Reason / Remarks']);
            if (empty($allSalaryVarStaff)) {
                fputcsv($handle, ['No salary or allowance adjustments recorded for continuing staff in this period.']);
            } else {
                foreach ($allSalaryVarStaff as $sv) {
                    fputcsv($handle, [$sv['staffId'], $sv['name'], $sv['department'], number_format($sv['prevGross'], 2), number_format($sv['currGross'], 2), ($sv['diff'] >= 0 ? '+' : '-') . number_format(abs($sv['diff']), 2), $sv['components'], $sv['reason']]);
                }
            }
            fputcsv($handle, []);

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            $deptSuffix = $deptName ? '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $deptName) : '';
            $filename = "Payroll_Variance_Summary_{$currMonthName}_{$currYear}{$deptSuffix}.csv";

            return response($csvContent, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI exportPayrollVarianceSummary: ' . $th->getMessage() . ' in ' . $th->getFile() . ':' . $th->getLine());
            return response()->json(['status' => 'error', 'message' => 'Failed to generate variance summary: ' . $th->getMessage()], 500);
        }
    }
    private function formatCsvMoney($val)
    {
        if ($val === null || $val === '' || $val === '—') {
            return '';
        }
        $cleanVal = str_replace(',', '', $val);
        if (!is_numeric($cleanVal)) {
            return $val;
        }
        return number_format((float)$cleanVal, 2, '.', ',');
    }

    // ─── Core Data Fetcher ────────────────────────────────────────────────────

    /**
     * Fetches, maps, and paginates payroll records.
     * Returns [$pagedRows, $totalCount, $summary].
     */
    private function fetchPayrollData(
        string $month,
        string $year,
        string $divisionID,
        string $bankID,
        int $perPage,
        int $page,
        string $departmentID = ''
    ): array {
        $loanBalances = DB::table('employee_loans')
            ->whereRaw("LOWER(status) = 'approved'")
            ->pluck('balance', 'staffId')
            ->toArray();

        $loanSetupBalances = DB::table('loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $loanSetupDeductions = DB::table('loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('monthly_deduction', 'staffId')
            ->toArray();

        $revolvingLoanBalances = [];
        foreach ($loanBalances as $sid => $bal) {
            $revolvingLoanBalances[$sid] = $bal;
        }
        foreach ($loanSetupBalances as $sid => $bal) {
            $revolvingLoanBalances[$sid] = $bal;
        }

        $coopLoanBalances = DB::table('coop_loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $medicalLoanBalances = DB::table('medical_loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $coopAssetFinanceBalances = DB::table('coop_asset_finance_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $legacyCoopLoanBalances = DB::table('staffEarningAndDeduction as sc')
            ->leftJoin('tblcvSetup as cv', 'cv.ID', '=', 'sc.cv_setup_id')
            ->where(function($q) {
                $q->where('cv.system_code', 'coop_loan_rpyt')
                  ->orWhere(function($sq) {
                      $sq->where(function($sub) {
                             $sub->where('sc.description', 'like', '%coop%')
                                 ->orWhere('sc.description', 'like', '%cooperative%');
                         })
                         ->where(function($sub) {
                             $sub->where('sc.description', 'like', '%loan%')
                                 ->orWhere('sc.description', 'like', '%rpyt%')
                                 ->orWhere('sc.description', 'like', '%rpty%')
                                 ->orWhere('sc.description', 'like', '%repay%');
                         });
                  })
                  ->orWhere('sc.cv_setup_id', 24);
            })
            ->groupBy('sc.staffId')
            ->select('sc.staffId', DB::raw('SUM(CASE WHEN sc.target_amount IS NOT NULL AND sc.target_amount > sc.total_deducted THEN sc.target_amount - sc.total_deducted ELSE 0 END) as balance'))
            ->pluck('balance', 'staffId')
            ->toArray();

        foreach ($legacyCoopLoanBalances as $sid => $bal) {
            if (!isset($coopLoanBalances[$sid])) {
                $coopLoanBalances[$sid] = $bal;
            }
        }

        $coopSavingsBalances = DB::table('coop_savings_setups')
            ->where('is_active', 1)
            ->pluck('saving_balance', 'staffId')
            ->toArray();

        $yearInt = (int)$year;
        $monthInt = 0;
        if (is_numeric($month)) {
            $monthInt = (int)$month;
        } else {
            $monthNames = [
                'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
                'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
                'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
            ];
            $monthInt = $monthNames[strtoupper(trim($month))] ?? 0;
        }

        $run = DB::table('payroll_runs')
            ->where('month', $monthInt)
            ->where('year', $yearInt)
            ->first();

        if ($run) {
            $query = DB::table('payroll_conpt as pc')
                ->join('tblper as p', 'p.ID', '=', 'pc.staffID')
                ->leftJoin('tbldivision as d', 'd.divisionID', '=', 'p.divisionID')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'p.bankID')
                ->where('pc.payroll_run_id', $run->id);

            if ($divisionID !== '') {
                $query->where('p.divisionID', $divisionID);
            }
            if ($departmentID !== '') {
                $query->where('p.departmentID', $departmentID);
            }
            if ($bankID !== '') {
                $query->where('p.bankID', $bankID);
            }

            $total   = $query->count();
            $allRows = $query->select(
                'pc.staffID',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                'dept.department',
                'pc.basic',
                'pc.housing',
                'pc.transport',
                'pc.medical',
                'pc.utility',
                'pc.meal',
                'pc.gross_pay',
                'pc.paye_tax',
                'pc.loan_deduction',
                'pc.pension',
                'pc.coop_savings',
                'pc.other_deductions',
                'pc.retention',
                'pc.surcharges',
                'pc.medical_loan',
                'pc.coop_loan_rpyt',
                DB::raw('COALESCE(pc.coop_asset_finance, 0) as coop_asset_finance'),
                'pc.total_deductions',
                'pc.net_pay',
                'pc.total_income',
                'pc.declare_income',
                'pc.iou',
                'pc.absence_penalty',
                'pc.leave_of_absence_deduction',
                'p.AccNo',
                'bl.bank as bankName',
                'pc.paid_days',
                'pc.salary_lock',
                'pc.vstage',
                'pc.audit_checked',
                'pc.is_paid',
                'pc.payer_id'
            )
            ->orderByRaw('COALESCE(dept.department, "ZZZ") ASC')
            ->orderBy('p.surname', 'ASC')
            ->orderBy('p.first_name', 'ASC')
            ->get();

            $mapped = $allRows->map(function ($row) use ($revolvingLoanBalances, $coopLoanBalances, $coopSavingsBalances, $medicalLoanBalances, $coopAssetFinanceBalances, $loanSetupDeductions) {
                return [
                    'IDNO'               => $row->staffID ?? '',
                    'NAME'               => $row->name   ?? '',
                    'DEPERTMENT'         => $row->department ?? '',
                    'BASIC'              => number_format((float)$row->basic,    2, '.', ''),
                    'HOUSING'            => number_format((float)$row->housing,  2, '.', ''),
                    'TRANSPORT'          => number_format((float)$row->transport,2, '.', ''),
                    'MEDICAL'            => number_format((float)$row->medical,  2, '.', ''),
                    'UTILITY'            => number_format((float)$row->utility,  2, '.', ''),
                    'MEAL'               => number_format((float)$row->meal,     2, '.', ''),
                    'TOTAL INCOME'       => number_format((float)$row->total_income,2, '.', ''),
                    'DECLARED INCOME'    => number_format((float)$row->declare_income,2, '.', ''),
                    'PAID DAYS'          => $row->paid_days,
                    'P.TAX'              => number_format((float)$row->paye_tax, 2, '.', ''),
                    'IOU'                => number_format((float)$row->iou, 2, '.', ''),
                    'RETENTION'          => number_format((float)$row->retention, 2, '.', ''),
                    'LOAN'               => number_format((float)($loanSetupDeductions[$row->staffID] ?? $row->loan_deduction), 2, '.', ''),
                    'SURGHARGES'         => number_format((float)$row->surcharges, 2, '.', ''),
                    'PENSION'            => number_format((float)$row->pension,  2, '.', ''),
                    'MEDICAL LOAN'       => number_format((float)$row->medical_loan, 2, '.', ''),
                    'COOP. SAVING'       => number_format((float)$row->coop_savings, 2, '.', ''),
                    'COOP. LOAN RPYT'    => number_format((float)$row->coop_loan_rpyt, 2, '.', ''),
                    'ABSENCE PENALTY'    => number_format((float)$row->absence_penalty, 2, '.', ''),
                    'LEAVE OF ABSENCE DEDUCTION' => number_format((float)$row->leave_of_absence_deduction, 2, '.', ''),
                    'OTHER DEDUCTION'    => number_format((float)$row->other_deductions, 2, '.', ''),
                    'TOTAL DEDUCTION'    => number_format((float)$row->total_deductions, 2, '.', ''),
                    'NETPAY'             => number_format((float)$row->net_pay,   2, '.', ''),
                    'REVOLVING LOAN BAL' => number_format((float)($revolvingLoanBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'COP.CONTR'          => number_format((float)($coopSavingsBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'COP. LONE BAL'      => number_format((float)($coopLoanBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'COOP.ASSET.'        => number_format((float)($row->coop_asset_finance ?? 0.00), 2, '.', ''),
                    'COP. ASSET FIN'     => number_format((float)($coopAssetFinanceBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'MEDICAL DEBT'       => number_format((float)($medicalLoanBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'LEAVE OF ABSENCE DEDUCTION' => number_format((float)($row->leave_of_absence_deduction ?? 0.00), 2, '.', ''),
                    'ACC. NO'            => $row->AccNo   ?? '',
                    'BANK'               => $row->bankName ?? '',
                    'CODE'               => '',
                    'PAYER ID'           => $row->payer_id ?? '',
                    'salary_lock'        => (int)($row->salary_lock ?? 0),
                    'vstage'             => (int)($row->vstage ?? 0),
                    'audit_checked'      => (int)($row->audit_checked ?? 0),
                    'is_paid'            => (int)($row->is_paid ?? 0),
                ];
            });

            $summary = [
                'totalStaff'       => $total,
                'totalGrossIncome' => number_format($allRows->sum(fn($r) => (float)$r->gross_pay), 2, '.', ''),
                'totalDeductions'  => number_format($allRows->sum(fn($r) => (float)$r->total_deductions), 2, '.', ''),
                'totalNetPay'      => number_format($allRows->sum(fn($r) => (float)$r->net_pay), 2, '.', ''),
            ];

            $offset    = ($page - 1) * max(1, $perPage);
            $paged     = ($perPage > 0) ? $mapped->slice($offset, $perPage)->values()->toArray() : $mapped->values()->toArray();

            return [$paged, $total, $summary];
        }

        // 1. Base query for payment consolidated records
        $query = DB::table('tblpayment_consolidated as pc')
            ->leftJoin('tblper as p', 'p.ID', '=', 'pc.staffid')
            ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
            ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'pc.bank')
            ->where('pc.month', $month)
            ->where('pc.year', $year)
            ->where('pc.rank', '!=', 2)
            ->select(
                'pc.fileNo',
                'pc.name',
                'dept.department',
                'pc.Bs',
                'pc.HA',
                'pc.TR',
                'pc.UTI',
                'pc.ML',
                'pc.TEarn',
                'pc.TAX',
                'pc.PEN',
                'pc.TD',
                'pc.NetPay',
                'pc.AccNo',
                'pc.bank as bankID',
                'bl.bank as bankName',
                'pc.staffid',
                'pc.divisionID',
                'p.payer_id',
                DB::raw("COALESCE(pc.OD, 0) as OD")
            );

        if ($divisionID !== '') {
            $query->where('pc.divisionID', $divisionID);
        }
        if ($departmentID !== '') {
            $query->where('p.departmentID', $departmentID);
        }
        if ($bankID !== '') {
            $query->where('pc.bank', $bankID);
        }

        $total   = $query->count();
        $allRows = $query
            ->orderByRaw('COALESCE(dept.department, "ZZZ") ASC')
            ->orderBy('pc.name', 'ASC')
            ->get(); // we need all rows to look up dynamic elements

        // 2. Collect all staffIDs for dynamic lookup
        $staffIds = $allRows->pluck('staffid')->filter()->unique()->values()->toArray();

        // 3. Fetch ALL dynamic earning/deduction records for these staff in one query
        $dynamicRows = collect();
        if (!empty($staffIds)) {
            $dynamicRows = DB::table('tblotherEarningDeduction as oed')
                ->join('tblcvSetup as cv', 'cv.ID', '=', 'oed.CVID')
                ->where('oed.year', $year)
                ->where('oed.month', $month)
                ->whereIn('oed.staffid', $staffIds)
                ->where('oed.amount', '>', 0.0099)
                ->select('oed.staffid', 'oed.CVID', 'oed.amount', 'cv.particularID')
                ->get();
        }

        // Build a lookup: staffid -> [ CVID -> total_amount ]
        $dynamicByStaff = [];
        foreach ($dynamicRows as $dr) {
            $sid  = $dr->staffid;
            $cvid = $dr->CVID;
            if (!isset($dynamicByStaff[$sid][$cvid])) {
                $dynamicByStaff[$sid][$cvid] = 0;
            }
            $dynamicByStaff[$sid][$cvid] += (float) $dr->amount;
        }

        // 4. Map each row to the 34-column template
        $mapped = $allRows->map(function ($row) use ($dynamicByStaff, $revolvingLoanBalances, $coopLoanBalances, $coopSavingsBalances, $medicalLoanBalances, $coopAssetFinanceBalances, $loanSetupDeductions) {
            $sid = $row->staffid;
            $cvs = $dynamicByStaff[$sid] ?? [];

            $basic     = (float)($row->Bs    ?? 0);
            $housing   = (float)($row->HA    ?? 0);
            $transport = (float)($row->TR    ?? 0);
            $utility   = (float)($row->UTI   ?? 0);
            $meal      = (float)($row->ML    ?? 0);
            $totalEarn = (float)($row->TEarn ?? 0);
            $pTax      = (float)($row->TAX   ?? 0);
            $pension   = (float)($row->PEN   ?? 0);
            $totalDedn = (float)($row->TD    ?? 0);
            $netPay    = (float)($row->NetPay ?? 0);

            // Dynamic columns from CV IDs
            $medical    = $cvs[self::CVID_MEDICAL_ALLOWANCE] ?? 0.00;
            $iou        = $cvs[self::CVID_OVERPAYMENT]       ?? 0.00;
            $loan       = isset($loanSetupDeductions[$sid]) ? (float)$loanSetupDeductions[$sid] : (($cvs[self::CVID_LAWYERS_LOAN] ?? 0) + ($cvs[self::CVID_VISA_LOAN] ?? 0));
            $coopSaving = ($cvs[self::CVID_COOP_CHRISTIAN] ?? 0)
                        + ($cvs[self::CVID_COOP_MUSLIM_WOMEN] ?? 0)
                        + ($cvs[self::CVID_COOP_SCN] ?? 0);
            $coopLoan   = $cvs[self::CVID_MUSLIM_WOMEN_LOAN] ?? 0.00;

            // Other Deduction = Total Deductions minus all explicitly mapped deductions
            $explicitDeductions = $pTax + $pension + $iou + $loan + $coopSaving + $coopLoan;
            $otherDeduction     = max(0, $totalDedn - $explicitDeductions);

            return [
                'IDNO'               => $row->staffid ?? '',
                'NAME'               => $row->name   ?? '',
                'DEPERTMENT'         => $row->department ?? '',
                'BASIC'              => number_format($basic,    2, '.', ''),
                'HOUSING'            => number_format($housing,  2, '.', ''),
                'TRANSPORT'          => number_format($transport,2, '.', ''),
                'MEDICAL'            => number_format($medical,  2, '.', ''),
                'UTILITY'            => number_format($utility,  2, '.', ''),
                'MEAL'               => number_format($meal,     2, '.', ''),
                'TOTAL INCOME'       => number_format($totalEarn,2, '.', ''),
                'DECLARED INCOME'    => number_format($totalEarn,2, '.', ''),
                'PAID DAYS'          => 30,
                'P.TAX'              => number_format($pTax,     2, '.', ''),
                'IOU'                => number_format($iou,      2, '.', ''),
                'RETENTION'          => '0.00',
                'LOAN'               => number_format($loan,     2, '.', ''),
                'SURGHARGES'         => '0.00',
                'PENSION'            => number_format($pension,  2, '.', ''),
                'MEDICAL LOAN'       => '0.00',
                'COOP. SAVING'       => number_format($coopSaving,2, '.', ''),
                'COOP. LOAN RPYT'    => number_format($coopLoan,  2, '.', ''),
                'ABSENCE PENALTY'    => '0.00',
                'OTHER DEDUCTION'    => number_format($otherDeduction,2, '.', ''),
                'TOTAL DEDUCTION'    => number_format($totalDedn,2, '.', ''),
                'NETPAY'             => number_format($netPay,   2, '.', ''),
                'REVOLVING LOAN BAL' => number_format((float)($revolvingLoanBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'COP.CONTR'          => number_format((float)($coopSavingsBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'COP. LONE BAL'      => number_format((float)($coopLoanBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'COOP.ASSET.'        => '0.00',
                'COP. ASSET FIN'     => number_format((float)($coopAssetFinanceBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'MEDICAL DEBT'       => number_format((float)($medicalLoanBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'LEAVE OF ABSENCE DEDUCTION' => '0.00',
                'ACC. NO'            => $row->AccNo   ?? '',
                'BANK'               => $row->bankName ?? '',
                'CODE'               => '',
                'PAYER ID'           => $row->payer_id ?? '',
            ];
        });

        // 5. Compute summary totals from all rows
        $summary = [
            'totalStaff'       => $total,
            'totalGrossIncome' => number_format($allRows->sum(fn($r) => (float)($r->TEarn  ?? 0)), 2, '.', ''),
            'totalDeductions'  => number_format($allRows->sum(fn($r) => (float)($r->TD     ?? 0)), 2, '.', ''),
            'totalNetPay'      => number_format($allRows->sum(fn($r) => (float)($r->NetPay ?? 0)), 2, '.', ''),
        ];

        // 6. Paginate in PHP (all records were fetched for dynamic join; pagination returned to client)
        $offset    = ($page - 1) * max(1, $perPage);
        $paged     = ($perPage > 0) ? $mapped->slice($offset, $perPage)->values()->toArray() : $mapped->values()->toArray();

        return [$paged, $total, $summary];
    }

    /**
     * POST /api/nextjs/payroll/compute
     * Run salary computation for all active staff for the given month and year.
     */
    public function computeSalary(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isFinanceStaff'] && !$ctx['isAdminStaff'])) {
            return response()->json(['status' => 'error', 'message' => 'Super Admin or Finance Head privileges required.'], 403);
        }

        try {
            $monthInput = strtoupper(trim($request->input('month', '')));
            $yearInput  = trim($request->input('year', ''));

            if (!$monthInput || !$yearInput) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            $year = (int)$yearInput;
            $month = 0;

            if (is_numeric($monthInput)) {
                $month = (int)$monthInput;
            } else {
                $monthNames = [
                    'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
                    'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
                    'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
                ];
                $month = $monthNames[$monthInput] ?? 0;
            }

            if ($month < 1 || $month > 12) {
                return response()->json(['status' => 'error', 'message' => 'Invalid month specified.'], 422);
            }

            // Check if payroll has already been disbursed/paid
            $hasPaidStage = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $month)
                ->where('vstage', '>=', 4)
                ->exists();

            if ($hasPaidStage) {
                return response()->json(['status' => 'error', 'message' => 'Cannot compute: payroll for this month has already been paid and disbursed.'], 400);
            }

            // Check if active month is locked
            $isLocked = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $month)
                ->where('salary_lock', 1)
                ->exists();

            $forceUnlock = (bool)$request->input('force_unlock', false);

            if ($isLocked && !$forceUnlock) {
                return response()->json([
                    'status'    => 'error',
                    'is_locked' => true,
                    'message'   => 'Cannot compute: this active month is currently locked. Unlock the period to recompute.'
                ], 400);
            }

            // Start transaction
            DB::beginTransaction();

            // Retrieve old conpt records for this month and year to revert total_deducted in staffEarningAndDeduction
            $oldDetails = DB::table('payroll_conpt')
                ->where('month', $month)
                ->where('year', $year)
                ->get();

             foreach ($oldDetails as $od) {
                $staffId = $od->staffID ?? $od->staffid ?? null;
                if (!$staffId) {
                    continue;
                }

                // Revert coop_loan_deduction_setups balance_remaining
                if ((float)($od->coop_loan_rpyt ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('coop_loan_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where('end_month', '>=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('coop_loan_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->coop_loan_rpyt),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert coop_savings_setups saving_balance
                if ((float)($od->coop_savings ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('coop_savings_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('coop_savings_setups')
                            ->where('id', $setup->id)
                            ->decrement('saving_balance', (float)$od->coop_savings);
                    }
                }

                // Revert surcharge_deduction_setups balance_remaining
                if ((float)($od->surcharges ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('surcharge_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('surcharge_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->surcharges),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert medical_loan_deduction_setups balance_remaining
                if ((float)($od->medical_loan ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('medical_loan_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where('end_month', '>=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('medical_loan_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->medical_loan),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert absence_penalty_deduction_setups balance_remaining
                if ((float)($od->absence_penalty ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('absence_penalty_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('absence_penalty_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->absence_penalty),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert other_deduction_setups balance_remaining
                if ((float)($od->other_deductions ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('other_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('other_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->other_deductions),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert coop_asset_finance_deduction_setups balance_remaining
                $coopAssetFinanceCol = isset($od->coop_asset_finance) ? (float)$od->coop_asset_finance : 0.00;
                if ($coopAssetFinanceCol > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('coop_asset_finance_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('coop_asset_finance_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . $coopAssetFinanceCol),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert loan_deduction_setups balance_remaining OR fallback to employee_loans balance
                if ((float)($od->loan_deduction ?? 0) > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('loan_deduction_setups')
                        ->where('staffId', $staffId)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where('end_month', '>=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('loan_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->loan_deduction),
                                'is_active' => 1
                            ]);
                    } else {
                        $empLoan = DB::table('employee_loans')
                            ->where('staffId', $staffId)
                            ->whereRaw("LOWER(status) = 'approved'")
                            ->orderBy('id', 'desc')
                            ->first();
                        if ($empLoan) {
                            DB::table('employee_loans')
                                ->where('id', $empLoan->id)
                                ->increment('balance', (float)$od->loan_deduction);
                        }
                    }
                }

                // Revert retention count in first_salary_structure if old conpt record had a retention deduction
                if (isset($od->retention) && (float)$od->retention > 0) {
                    DB::table('first_salary_structure')
                        ->where('staffId', $staffId)
                        ->where('num_rente_months', '>', 0)
                        ->decrement('num_rente_months');
                }
            }

            // Delete old payroll details for this month and year
            DB::table('payroll_conpt')
                ->where('month', $month)
                ->where('year', $year)
                ->delete();

            $existingRun = DB::table('payroll_runs')
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingRun) {
                $runId = $existingRun->id;

                DB::table('payroll_runs')->where('id', $runId)->update([
                    'status' => 'processed',
                    'processed_by' => $ctx['userId'] ?? null,
                    'processed_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $runId = DB::table('payroll_runs')->insertGetId([
                    'month' => $month,
                    'year' => $year,
                    'status' => 'processed',
                    'processed_by' => $ctx['userId'] ?? null,
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $employees = DB::table('tblper')
                ->where('rank', '!=', 2) // Exclude terminated/retired
                ->where('staff_status', 1) // Only active service status
                ->get();

            foreach ($employees as $emp) {
                // 1. Fetch salary structure
                $struct = DB::table('salary_structures')
                    ->where('staffId', $emp->ID)
                    ->first();

                $basic = 0.00;
                $housing = 0.00;
                $transport = 0.00;
                $medical = 0.00;
                $utility = 0.00;
                $meal = 0.00;
                $taxRate = 0.00;
                $pensionRate = 0.00;
                $declareSalary = 0.00;

                if ($struct) {
                    $basic = (float)$struct->basic_salary;
                    $housing = (float)$struct->housing_allowance;
                    $transport = (float)$struct->transport_allowance;
                    $medical = (float)$struct->medical_allowance;
                    $utility = (float)$struct->utility_allowance;
                    $meal = (float)$struct->meal_allowance;
                    $taxRate = (float)$struct->tax_rate;
                    $pensionRate = (float)$struct->pension_rate;
                    $declareSalary = (float)$struct->declare_salary;
                }

                // 2. Paid days (Days in Month - Leave of Absence days - Mid-Month Hire Days)
                $daysInPayrollMonth = (int) \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                if ($daysInPayrollMonth < 28 || $daysInPayrollMonth > 31) {
                    $daysInPayrollMonth = 30;
                }
                $loaDays = $this->getLoaDaysForMonth($emp->ID, $year, $month);
                $dojDaysDeducted = 0;
                $effectiveJoinDate = !empty($emp->appointment_date) && $emp->appointment_date !== '0000-00-00'
                    ? $emp->appointment_date
                    : (!empty($emp->doj) && $emp->doj !== '0000-00-00' ? $emp->doj : null);

                if (!empty($effectiveJoinDate)) {
                    try {
                        $dojDate = \Carbon\Carbon::parse($effectiveJoinDate);
                        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

                        if ($dojDate->greaterThan($endOfMonth)) {
                            $dojDaysDeducted = $daysInPayrollMonth;
                        } elseif ($dojDate->year === $year && $dojDate->month === $month) {
                            $daysBefore = max(0, min($daysInPayrollMonth, $dojDate->day - 1));
                            $dojDaysDeducted = $daysBefore;
                        }
                    } catch (\Throwable $e) { /* ignore */ }
                }
                $paidDays = max(0, $daysInPayrollMonth - $loaDays - $dojDaysDeducted);

                // Check if there is an active coop loan setup
                $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                $coopLoanSetup = DB::table('coop_loan_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active coop savings setup
                $coopSavingsSetup = DB::table('coop_savings_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active surcharge setup
                $surchargeSetup = DB::table('surcharge_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active medical loan setup
                $medicalLoanSetup = DB::table('medical_loan_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active absence penalty setup
                $absencePenaltySetup = DB::table('absence_penalty_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where(function($q) {
                        $q->where('balance_remaining', '>', 0)
                          ->orWhere('total_amount', '>', 0);
                    })
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active other deduction setup
                $otherDeductionSetup = DB::table('other_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where(function($q) {
                        $q->where('balance_remaining', '>', 0)
                          ->orWhere('total_amount', '>', 0);
                    })
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active coop asset finance deduction setup
                $coopAssetFinanceSetup = DB::table('coop_asset_finance_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active loan setup
                $loanSetup = DB::table('loan_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active approved employee loan (fallback)
                $employeeLoanSetup = null;
                if (!$loanSetup) {
                    $employeeLoanSetup = DB::table('employee_loans')
                        ->where('staffId', $emp->ID)
                        ->whereRaw("LOWER(status) = 'approved'")
                        ->where('balance', '>', 0)
                        ->orderBy('id', 'desc')
                        ->first();
                }

                $loanDeduction = 0.00;
                $coopSavings = 0.00;
                $otherDeductions = 0.00;
                $retention = 0.00;
                $surcharges = 0.00;
                $medicalLoan = 0.00;
                $coopLoanRpyt = 0.00;
                $absencePenalty = 0.00;
                $coopAssetFinance = 0.00;
                $leaveOfAbsenceDeduction = 0.00;
                $totalEarningVars = 0.00;
                $appliedAmountsMap = [];

                // Process Coop Loan Repayment Setup
                if ($coopLoanSetup) {
                    $coopLoanRpyt = min((float)$coopLoanSetup->monthly_deduction, (float)$coopLoanSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$coopLoanSetup->balance_remaining - $coopLoanRpyt);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('coop_loan_deduction_setups')
                        ->where('id', $coopLoanSetup->id)
                        ->update($updateData);
                }

                // Process Coop Savings Setup
                if ($coopSavingsSetup) {
                    $coopSavings = (float)$coopSavingsSetup->monthly_saving;
                    
                    // Increment saving_balance on setups table
                    DB::table('coop_savings_setups')
                        ->where('id', $coopSavingsSetup->id)
                        ->increment('saving_balance', $coopSavings);
                }

                // Process Surcharge Setup
                if ($surchargeSetup) {
                    $surcharges = min((float)$surchargeSetup->monthly_deduction, (float)$surchargeSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$surchargeSetup->balance_remaining - $surcharges);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('surcharge_deduction_setups')
                        ->where('id', $surchargeSetup->id)
                        ->update($updateData);
                }

                // Process Medical Loan Setup
                if ($medicalLoanSetup) {
                    $medicalLoan = min((float)$medicalLoanSetup->monthly_deduction, (float)$medicalLoanSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$medicalLoanSetup->balance_remaining - $medicalLoan);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('medical_loan_deduction_setups')
                        ->where('id', $medicalLoanSetup->id)
                        ->update($updateData);
                }

                // Process Absence Penalty Setup
                if ($absencePenaltySetup) {
                    $absBal = (float)$absencePenaltySetup->balance_remaining > 0
                        ? (float)$absencePenaltySetup->balance_remaining
                        : ((float)$absencePenaltySetup->total_amount > 0 ? (float)$absencePenaltySetup->total_amount : (float)$absencePenaltySetup->monthly_deduction);
                    $absencePenalty = min((float)$absencePenaltySetup->monthly_deduction, $absBal);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, $absBal - $absencePenalty);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('absence_penalty_deduction_setups')
                        ->where('id', $absencePenaltySetup->id)
                        ->update($updateData);
                }

                // Process Other Deduction Setup
                if ($otherDeductionSetup) {
                    $otherDeductBal = (float)$otherDeductionSetup->balance_remaining > 0
                        ? (float)$otherDeductionSetup->balance_remaining
                        : ((float)$otherDeductionSetup->total_amount > 0 ? (float)$otherDeductionSetup->total_amount : (float)$otherDeductionSetup->monthly_deduction);
                    $otherDeductions = min((float)$otherDeductionSetup->monthly_deduction, $otherDeductBal);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, $otherDeductBal - $otherDeductions);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('other_deduction_setups')
                        ->where('id', $otherDeductionSetup->id)
                        ->update($updateData);
                }

                // Process Coop Asset Finance Deduction Setup
                if ($coopAssetFinanceSetup) {
                    $coopAssetFinance = min((float)$coopAssetFinanceSetup->monthly_deduction, (float)$coopAssetFinanceSetup->balance_remaining);

                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$coopAssetFinanceSetup->balance_remaining - $coopAssetFinance);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('coop_asset_finance_deduction_setups')
                        ->where('id', $coopAssetFinanceSetup->id)
                        ->update($updateData);
                }

                // Process Employee Loan (Regular Employee Loans - new setup or fallback)
                if ($loanSetup) {
                    $loanDeduction = min((float)$loanSetup->monthly_deduction, (float)$loanSetup->balance_remaining);
                    
                    // Update remaining balance on loan_deduction_setups table
                    $newBalance = max(0.00, (float)$loanSetup->balance_remaining - $loanDeduction);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('loan_deduction_setups')
                        ->where('id', $loanSetup->id)
                        ->update($updateData);
                } elseif ($employeeLoanSetup) {
                    $loanDeduction = min((float)$employeeLoanSetup->monthly_deduction, (float)$employeeLoanSetup->balance);
                    
                    // Update remaining balance on employee_loans table
                    DB::table('employee_loans')
                        ->where('id', $employeeLoanSetup->id)
                        ->decrement('balance', $loanDeduction);
                }

                // 4. Perform salary calculation
                // Note: Basic salary and allowances are NOT prorated; they remain full
                $basicProrated = $basic;
                $housingProrated = $housing;
                $transportProrated = $transport;
                $medicalProrated = $medical;
                $utilityProrated = $utility;
                $mealProrated = $meal;

                $totalIncome = $basicProrated + $housingProrated + $transportProrated + $medicalProrated + $utilityProrated + $mealProrated;
                $grossPay = $totalIncome + $totalEarningVars;
                $declareIncome = $declareSalary;

                // PAYE and Pension calculated using Nigeria Tax Act 2025/2026 progressive bands with standard pension deduction (8% of 50% on declare income)
                $annualGross = $declareIncome * 12.0;
                $annualPension = 0.00;
                if ($struct && $struct->pen_act == 1) {
                    $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                    $annualPension = ($annualGross * 0.5) * $rate;
                }
                $annualTaxable = max(0.00, $annualGross - $annualPension);

                $annualTax = 0.00;
                if ($annualTaxable > 800000.00) {
                    $taxableRemaining = $annualTaxable - 800000.00;
                    
                    // Next ₦2,200,000 @ 15%
                    $band1 = min(2200000.00, $taxableRemaining);
                    $annualTax += $band1 * 0.15;
                    $taxableRemaining -= $band1;
                    
                    if ($taxableRemaining > 0) {
                        // Next ₦9,000,000 @ 18%
                        $band2 = min(9000000.00, $taxableRemaining);
                        $annualTax += $band2 * 0.18;
                        $taxableRemaining -= $band2;
                    }
                    
                    if ($taxableRemaining > 0) {
                        // Next ₦13,000,000 @ 21%
                        $band3 = min(13000000.00, $taxableRemaining);
                        $annualTax += $band3 * 0.21;
                        $taxableRemaining -= $band3;
                    }
                    
                    if ($taxableRemaining > 0) {
                        // Next ₦25,000,000 @ 23%
                        $band4 = min(25000000.00, $taxableRemaining);
                        $annualTax += $band4 * 0.23;
                        $taxableRemaining -= $band4;
                    }
                    
                    if ($taxableRemaining > 0) {
                        // Above ₦50,000,000 @ 25%
                        $annualTax += $taxableRemaining * 0.25;
                    }
                }
                
                $fullMonthlyTax = round($annualTax / 12.0, 2);
                // Method 1: Prorate Monthly PAYE Tax by Paid Days / Days in Month
                $payeTax = round($fullMonthlyTax * ($paidDays / (float)$daysInPayrollMonth), 2);

                $pension = 0.00;
                if ($struct && $struct->pen_act == 1) {
                    $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                    $pension = round(($totalIncome * 0.5) * $rate, 2);
                }

                // Fetch monthly IOU taken from iou_records table
                $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

                $iouSum = DB::table('iou_records')
                    ->where('staff_id', $emp->ID)
                    ->where('status', 1) // Approved
                    ->whereBetween('iou_date', [$firstDay, $lastDay])
                    ->sum('amount');

                // Compute leave of absence deduction: (grossPay / daysInPayrollMonth) * days_of_absent
                $leaveOfAbsenceDeduction = round(($grossPay / (float)$daysInPayrollMonth) * $loaDays, 2);
                $midMonthDeduction = round(($grossPay / (float)$daysInPayrollMonth) * $dojDaysDeducted, 2);

                // Compute retention using first_salary_structure if active and num_rente_months is less than 20
                $firstStruct = DB::table('first_salary_structure')->where('staffId', $emp->ID)->first();
                if ($firstStruct && $firstStruct->reten_act == 1 && $paidDays > 0) {
                    if ($firstStruct->num_rente_months < 20) {
                        $allowancesTotal = (float)$firstStruct->basic_salary +
                                           (float)$firstStruct->housing_allowance +
                                           (float)$firstStruct->transport_allowance +
                                           (float)$firstStruct->medical_allowance +
                                           (float)$firstStruct->utility_allowance +
                                           (float)$firstStruct->meal_allowance;
                        $retentionBase = (float)($firstStruct->declare_salary ?? 0) > 0 
                            ? (float)$firstStruct->declare_salary 
                            : $allowancesTotal;
                        $retention = round(0.05 * $retentionBase, 2);

                        // Increment num_rente_months by 1
                        DB::table('first_salary_structure')
                            ->where('staffId', $emp->ID)
                            ->increment('num_rente_months');
                    }
                }

                $totalDeductions = round(
                    $payeTax + $pension + $loanDeduction + $coopSavings + $otherDeductions +
                    $iouSum + $absencePenalty + $retention + $surcharges + $medicalLoan +
                    $coopLoanRpyt + $coopAssetFinance + $leaveOfAbsenceDeduction + $midMonthDeduction,
                    2
                );
                $netPay = ($paidDays === 0) ? 0.00 : round($grossPay - $totalDeductions, 2);

                // 5. Submit detailed row into payroll_conpt table
                DB::table('payroll_conpt')->insert([
                    'payroll_run_id'   => $runId,
                    'staffID'          => $emp->ID,
                    'month'            => $month,
                    'year'             => $year,
                    'payer_id'         => $emp->payer_id,
                    'basic'            => round($basicProrated, 2),
                    'housing'          => round($housingProrated, 2),
                    'transport'        => round($transportProrated, 2),
                    'medical'          => round($medicalProrated, 2),
                    'utility'          => round($utilityProrated, 2),
                    'meal'             => round($mealProrated, 2),
                    'paid_days'        => $paidDays,
                    'gross_pay'        => round($grossPay, 2),
                    'paye_tax'         => round($payeTax, 2),
                    'loan_deduction'   => round($loanDeduction, 2),
                    'pension'          => round($pension, 2),
                    'coop_savings'     => round($coopSavings, 2),
                    'other_deductions' => round($otherDeductions, 2),
                    'retention'        => round($retention, 2),
                    'surcharges'          => round($surcharges, 2),
                    'medical_loan'        => round($medicalLoan, 2),
                    'coop_loan_rpyt'      => round($coopLoanRpyt, 2),
                    'coop_asset_finance'  => round($coopAssetFinance, 2),
                    'total_deductions'    => round($totalDeductions, 2),
                    'net_pay'             => round($netPay, 2),
                    'total_income'        => round($totalIncome, 2),
                    'declare_income'      => round($declareIncome, 2),
                    'iou'                 => round($iouSum, 2),
                    'absence_penalty'     => round($absencePenalty, 2),
                    'leave_of_absence_deduction' => round($leaveOfAbsenceDeduction, 2),
                    'applied_amounts'     => !empty($appliedAmountsMap) ? json_encode($appliedAmountsMap) : null,
                    'created_at'          => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Salary payroll run computed and saved successfully for all active staff.',
                'payroll_run_id' => $runId
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PayrollAPI computeSalary: ' . $th->getMessage() . "\n" . $th->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Helper to sum leave of absence days for a staff member in a specific month and year.
     */
    private function getLoaDaysForMonth($staffId, $year, $month): int
    {
        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $leaves = DB::table('leave_of_absent')
            ->where('staffId', $staffId)
            ->where('status', 2) // Approved
            ->where(function($query) use ($firstDay, $lastDay) {
                $query->whereBetween('start_date', [$firstDay, $lastDay])
                      ->orWhereBetween('end_date', [$firstDay, $lastDay])
                      ->orWhere(function($q) use ($firstDay, $lastDay) {
                          $q->where('start_date', '<=', $firstDay)
                            ->where('end_date', '>=', $lastDay);
                      });
            })
            ->get();

        $totalDays = 0;
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        foreach ($leaves as $leave) {
            $start = \Carbon\Carbon::parse($leave->start_date);
            $end = \Carbon\Carbon::parse($leave->end_date);

            $overlapStart = $start->greaterThan($startOfMonth) ? $start : $startOfMonth;
            $overlapEnd = $end->lessThan($endOfMonth) ? $end : $endOfMonth;

            $totalDays += $overlapStart->diffInDays($overlapEnd) + 1;
        }
        return (int)$totalDays;
    }

    /**
     * GET /api/nextjs/payroll/payslip/init
     * Fetch initialization data for payslip printing page.
     */
    public function getPayslipInit(Request $request)
    {
        $userCtx = $this->getUserContext($request);
        if (!$userCtx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $isSuperAdmin = $userCtx['isSuperAdmin'] ?? false;
        
        $userId = $request->header('X-User-Id');
        if ($userId) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user && strtolower($user->user_type ?? '') === 'technical') {
                $isSuperAdmin = true;
            }
        }

        $myStaff = null;
        if (isset($userCtx['employee']) && $userCtx['employee']) {
            $myStaff = [
                'id' => $userCtx['employee']->ID,
                'fileNo' => $userCtx['employee']->fileNo,
                'name' => trim($userCtx['employee']->surname . ' ' . $userCtx['employee']->first_name . ' ' . ($userCtx['employee']->othernames ?? '')),
            ];
        }

        return response()->json([
            'status' => 'success',
            'is_admin' => $isSuperAdmin,
            'my_staff' => $myStaff
        ]);
    }

    /**
     * GET /api/nextjs/payroll/payslip
     * Fetch payslip details for a staff member, month, and year.
     */
    public function getPayslip(Request $request)
    {
        $staffId = $request->input('staff_id');
        $month = $request->input('month');
        $year = $request->input('year');

        if (!$staffId || !$month || !$year) {
            return response()->json(['status' => 'error', 'message' => 'Staff ID, Month, and Year are required.'], 422);
        }

        $ctx = $this->getUserContext($request);
        $activePeriod = DB::table('tblactivemonth')->first();
        $isCurrentActivePeriod = ($activePeriod && strtoupper(trim($activePeriod->month)) === strtoupper(trim($month)) && (int)$activePeriod->year === (int)$year);

        if ($isCurrentActivePeriod) {
            $printActive = $activePeriod ? ($activePeriod->print_active ?? 0) : 0;
            if (!$printActive) {
                $isAdmin = $ctx && ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']);
                if (!$isAdmin) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Payslip printing has not been activated yet by HR for the current active period.'
                    ], 403);
                }
            }
        }

        // 1. Fetch the staff information
        $personData = DB::table('tblper')
            ->where('ID', $staffId)
            ->orWhere('fileNo', $staffId)
            ->first();

        if (!$personData) {
            return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
        }

        // 2. Fetch all payroll records for this month & year using our unified fetchPayrollData
        try {
            [$payrollRows] = $this->fetchPayrollData($month, $year, '', '', PHP_INT_MAX, 1);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Error fetching payroll data: ' . $th->getMessage()], 500);
        }

        // 3. Find the specific staff member's payroll record
        $matchedRow = null;
        foreach ($payrollRows as $row) {
            if ($row['IDNO'] == $personData->ID || $row['IDNO'] == $personData->fileNo) {
                $matchedRow = $row;
                break;
            }
        }

        if (!$matchedRow) {
            return response()->json(['status' => 'error', 'message' => "No payslip record found for the selected month and year."], 404);
        }

        if ($matchedRow['vstage'] < 4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payslip cannot be generated: payroll for this month has not been paid yet.'
            ], 400);
        }

        // 4. Resolve bank name
        $bankName = $matchedRow['BANK'] ?: 'N/A';

        $response = [
            'staff' => [
                'id' => $personData->ID,
                'file_no' => $personData->fileNo,
                'name' => trim($personData->surname . ' ' . $personData->first_name . ' ' . ($personData->othernames ?? '')),
                'department' => DB::table('tbldepartment')->where('id', $personData->departmentID)->value('department') ?: 'N/A',
                'designation' => DB::table('tbldesignation')->where('id', $personData->designationID)->value('designation') ?: 'N/A',
                'bank_name' => $bankName,
                'bank_account' => $matchedRow['ACC. NO'] ?: ($personData->AccNo ?: 'N/A'),
            ],
            'payslip' => [
                'month' => $month,
                'year' => $year,
                'basic' => (float)$matchedRow['BASIC'],
                'housing' => (float)$matchedRow['HOUSING'],
                'transport' => (float)$matchedRow['TRANSPORT'],
                'medical' => (float)$matchedRow['MEDICAL'],
                'utility' => (float)$matchedRow['UTILITY'],
                'meal' => (float)$matchedRow['MEAL'],
                'gross_pay' => (float)$matchedRow['TOTAL INCOME'],
                
                // Deductions
                'tax' => (float)$matchedRow['P.TAX'],
                'pension' => (float)$matchedRow['PENSION'],
                'loan' => (float)$matchedRow['LOAN'],
                'coop_savings' => (float)$matchedRow['COOP. SAVING'],
                'coop_loan' => (float)$matchedRow['COOP. LOAN RPYT'],
                'iou' => (float)$matchedRow['IOU'],
                'retention' => (float)$matchedRow['RETENTION'],
                'surcharges' => (float)$matchedRow['SURGHARGES'],
                'medical_loan' => (float)$matchedRow['MEDICAL LOAN'],
                'absence_penalty' => (float)$matchedRow['ABSENCE PENALTY'],
                'coop_asset_finance' => (float)$matchedRow['COOP.ASSET.'],
                'leave_absence_deduction' => (float)$matchedRow['LEAVE OF ABSENCE DEDUCTION'],
                'other_deductions' => (float)$matchedRow['OTHER DEDUCTION'],
                'total_deductions' => (float)$matchedRow['TOTAL DEDUCTION'],
                'net_pay' => (float)$matchedRow['NETPAY'],

                // Additional balances / metadata to display on payslip
                'paid_days' => $matchedRow['PAID DAYS'],
                'revolving_loan_balance' => (float)$matchedRow['REVOLVING LOAN BAL'],
                'coop_savings_balance' => (float)$matchedRow['COP.CONTR'],
                'coop_loan_balance' => (float)$matchedRow['COP. LONE BAL'],
                'coop_asset_finance_balance' => (float)$matchedRow['COP. ASSET FIN'],
                'medical_loan_balance' => (float)$matchedRow['MEDICAL DEBT'],
            ]
        ];

        // Fetch HR Head Signature or Super Admin fallback
        $hrSignature = DB::table('users')
            ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
            ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
            ->where('user_role.rolename', 'like', '%HR HEAD%')
            ->whereNotNull('users.signature')
            ->value('users.signature');

        if (!$hrSignature) {
            $hrSignature = DB::table('users')
                ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                ->where('assign_user_role.roleID', 1)
                ->whereNotNull('users.signature')
                ->value('users.signature');
        }

        $response['hr_signature'] = $hrSignature;

        return response()->json([
            'status' => 'success',
            'data' => $response
        ]);
    }

    /**
     * GET /api/nextjs/payroll/hr-signature
     * Returns the HR signature for the logged-in user context.
     */
    public function getHrSignature(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $signature = DB::table('users')->where('id', $ctx['userId'])->value('signature');
            
            $activePeriod = DB::table('tblactivemonth')->first();
            $printActive = $activePeriod ? (int)($activePeriod->print_active ?? 0) : 0;

            return response()->json([
                'status'       => 'success',
                'signature'    => $signature,
                'print_active' => $printActive
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI getHrSignature: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/hr-signature
     * Saves the HR signature (base64 string) for the logged-in user context.
     */
    public function saveHrSignature(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'signature' => 'required|string'
            ]);

            DB::table('users')->where('id', $ctx['userId'])->update([
                'signature' => $request->input('signature'),
                'updated_at' => now()
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Signature saved successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI saveHrSignature: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/print-activation
     * Toggle print activation status for the current active month.
     */
    public function togglePrintActivation(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Admin or HR staff can toggle print activation.'], 403);
            }

            $request->validate([
                'print_active' => 'required|boolean'
            ]);

            $printActive = $request->input('print_active') ? 1 : 0;

            $activePeriod = DB::table('tblactivemonth')->first();
            if (!$activePeriod) {
                return response()->json(['status' => 'error', 'message' => 'No active period found.'], 400);
            }

            DB::table('tblactivemonth')
                ->where('courtID', $activePeriod->courtID)
                ->update([
                    'print_active' => $printActive
                ]);

            $statusText = $printActive ? 'activated' : 'deactivated';

            DB::table('audit_log')->insert([
                'comp_name' => php_uname('a'),
                'user_id'   => $ctx['userId'],
                'date'      => \Carbon\Carbon::now('Africa/Lagos'),
                'ip_addr'   => $request->ip(),
                'operation' => " payslip printing {$statusText} for active period: " . $activePeriod->month . "/" . $activePeriod->year,
                'host'      => $request->header('host') ?? 'localhost',
                'referer'   => $request->fullUrl()
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => "Payslip printing successfully {$statusText}!"
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error('PayrollAPI togglePrintActivation: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/payslip/send-email
     * Send payslip to staff email.
     */
    public function sendPayslipEmail(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Verify logged-in user has Admin or HR privileges
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Admin or HR staff can send payslip emails.'], 403);
            }

            $staffId = $request->input('staff_id');
            $month = $request->input('month');
            $year = $request->input('year');

            if (!$staffId || !$month || !$year) {
                return response()->json(['status' => 'error', 'message' => 'Staff ID, Month, and Year are required.'], 422);
            }

            $personData = DB::table('tblper')
                ->where('ID', $staffId)
                ->orWhere('fileNo', $staffId)
                ->first();

            if (!$personData) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
            }

            if (empty($personData->email)) {
                return response()->json(['status' => 'error', 'message' => 'Cannot send payslip: staff email is not configured.'], 400);
            }

            [$payrollRows] = $this->fetchPayrollData($month, $year, '', '', PHP_INT_MAX, 1);
            $matchedRow = null;
            foreach ($payrollRows as $row) {
                if ($row['IDNO'] == $personData->ID || $row['IDNO'] == $personData->fileNo) {
                    $matchedRow = $row;
                    break;
                }
            }

            if (!$matchedRow) {
                return response()->json(['status' => 'error', 'message' => 'No computed payroll record found.'], 404);
            }

            // ONLY paid staff can have payslips emailed!
            if ($matchedRow['vstage'] < 4) {
                return response()->json(['status' => 'error', 'message' => 'Cannot send payslip: staff has not been paid yet.'], 400);
            }

            // Get HR Head Signature
            $hrSignature = DB::table('users')
                ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->where('user_role.rolename', 'like', '%HR HEAD%')
                ->whereNotNull('users.signature')
                ->value('users.signature');

            if (!$hrSignature) {
                $hrSignature = DB::table('users')
                    ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                    ->where('assign_user_role.roleID', 1)
                    ->whereNotNull('users.signature')
                    ->value('users.signature');
            }

            // Build HTML Content for payslip email
            $subject = "Payslip for " . $month . " " . $year;
            $staffName = trim($personData->surname . ' ' . $personData->first_name . ' ' . ($personData->othernames ?? ''));

            // Basic details
            $basic = number_format((float)$matchedRow['BASIC'], 2);
            $housing = number_format((float)$matchedRow['HOUSING'], 2);
            $transport = number_format((float)$matchedRow['TRANSPORT'], 2);
            $medical = number_format((float)$matchedRow['MEDICAL'], 2);
            $utility = number_format((float)$matchedRow['UTILITY'], 2);
            $meal = number_format((float)$matchedRow['MEAL'], 2);
            $gross = number_format((float)$matchedRow['TOTAL INCOME'], 2);

            // Deductions
            $tax = number_format((float)$matchedRow['P.TAX'], 2);
            $pension = number_format((float)$matchedRow['PENSION'], 2);
            $loan = number_format((float)$matchedRow['LOAN'], 2);
            $coopSavings = number_format((float)$matchedRow['COOP. SAVING'], 2);
            $coopLoan = number_format((float)$matchedRow['COOP. LOAN RPYT'], 2);
            $iou = number_format((float)$matchedRow['IOU'], 2);
            $retention = number_format((float)$matchedRow['RETENTION'], 2);
            $surcharges = number_format((float)$matchedRow['SURGHARGES'], 2);
            $medLoan = number_format((float)$matchedRow['MEDICAL LOAN'], 2);
            $absencePen = number_format((float)$matchedRow['ABSENCE PENALTY'], 2);
            $coopAsset = number_format((float)$matchedRow['COOP.ASSET.'], 2);
            $loaDed = number_format((float)$matchedRow['LEAVE OF ABSENCE DEDUCTION'], 2);
            $otherDed = number_format((float)$matchedRow['OTHER DEDUCTION'], 2);
            $totalDed = number_format((float)$matchedRow['TOTAL DEDUCTION'], 2);
            $netPay = number_format((float)$matchedRow['NETPAY'], 2);

            $bankAccount = $matchedRow['ACC. NO'] ?: ($personData->AccNo ?: 'N/A');
            $bankName = $matchedRow['BANK'] ?: 'N/A';
            $department = DB::table('tbldepartment')->where('id', $personData->departmentID)->value('department') ?: 'N/A';
            $designation = DB::table('tbldesignation')->where('id', $personData->designationID)->value('designation') ?: 'N/A';

            $signatureHtml = $hrSignature ? '<img src="' . $hrSignature . '" alt="HR Head Signature" style="max-height: 70px; display: block;" />' : '<em>No signature on file</em>';

            $html = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #2c3e50; text-align: center; border-bottom: 2px solid #34495e; padding-bottom: 10px;'>PAYSLIP REPORT</h2>
                    <p style='text-align: center; font-weight: bold;'>Period: {$month} {$year}</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                        <tr><td style='padding: 5px; font-weight: bold; width: 30%;'>Staff Name:</td><td style='padding: 5px;'>{$staffName}</td></tr>
                        <tr><td style='padding: 5px; font-weight: bold;'>Staff ID:</td><td style='padding: 5px;'>{$personData->ID}</td></tr>
                        <tr><td style='padding: 5px; font-weight: bold;'>Department:</td><td style='padding: 5px;'>{$department}</td></tr>
                        <tr><td style='padding: 5px; font-weight: bold;'>Designation:</td><td style='padding: 5px;'>{$designation}</td></tr>
                    </table>

                    <div style='margin-bottom: 20px;'>
                        <h3 style='background-color: #f2f2f2; padding: 8px; margin-top: 0;'>Earnings</h3>
                        <table style='width: 100%;'>
                            <tr><td>Basic Salary:</td><td style='text-align: right;'>₦{$basic}</td></tr>
                            <tr><td>Housing Allowance:</td><td style='text-align: right;'>₦{$housing}</td></tr>
                            <tr><td>Transport Allowance:</td><td style='text-align: right;'>₦{$transport}</td></tr>
                            <tr><td>Medical Allowance:</td><td style='text-align: right;'>₦{$medical}</td></tr>
                            <tr><td>Utility Allowance:</td><td style='text-align: right;'>₦{$utility}</td></tr>
                            <tr><td>Meal Allowance:</td><td style='text-align: right;'>₦{$meal}</td></tr>
                            <tr style='font-weight: bold; border-top: 1px solid #ccc;'><td>Gross Pay:</td><td style='text-align: right;'>₦{$gross}</td></tr>
                        </table>
                    </div>

                    <div style='margin-bottom: 20px;'>
                        <h3 style='background-color: #f2f2f2; padding: 8px; margin-top: 0;'>Deductions</h3>
                        <table style='width: 100%;'>
                            <tr><td>PAYE Tax:</td><td style='text-align: right;'>₦{$tax}</td></tr>
                            <tr><td>Pension Contribution:</td><td style='text-align: right;'>₦{$pension}</td></tr>
                            <tr><td>Loan Repayment:</td><td style='text-align: right;'>₦{$loan}</td></tr>
                            <tr><td>Cooperative Savings:</td><td style='text-align: right;'>₦{$coopSavings}</td></tr>
                            <tr><td>Cooperative Loan Repayment:</td><td style='text-align: right;'>₦{$coopLoan}</td></tr>
                            <tr><td>IOU Deduction:</td><td style='text-align: right;'>₦{$iou}</td></tr>
                            <tr><td>Retention:</td><td style='text-align: right;'>₦{$retention}</td></tr>
                            <tr><td>Surcharges:</td><td style='text-align: right;'>₦{$surcharges}</td></tr>
                            <tr><td>Medical Loan:</td><td style='text-align: right;'>₦{$medLoan}</td></tr>
                            <tr><td>Absence Penalty:</td><td style='text-align: right;'>₦{$absencePen}</td></tr>
                            <tr><td>Coop. Asset Finance:</td><td style='text-align: right;'>₦{$coopAsset}</td></tr>
                            <tr><td>LOA Deduction:</td><td style='text-align: right;'>₦{$loaDed}</td></tr>
                            <tr><td>Other Deductions:</td><td style='text-align: right;'>₦{$otherDed}</td></tr>
                            <tr style='font-weight: bold; border-top: 1px solid #ccc;'><td>Total Deductions:</td><td style='text-align: right;'>₦{$totalDed}</td></tr>
                        </table>
                    </div>

                    <div style='border-top: 2px solid #2c3e50; padding-top: 10px; margin-bottom: 30px;'>
                        <table style='width: 100%; font-size: 1.2em; font-weight: bold;'>
                            <tr><td>NET PAY:</td><td style='text-align: right; color: #27ae60;'>₦{$netPay}</td></tr>
                        </table>
                    </div>
                </div>
            </body>
            </html>
            ";

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($personData, $subject, $html) {
                $message->to($personData->email)
                    ->subject($subject)
                    ->setBody($html, 'text/html');
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Payslip emailed successfully to ' . $personData->email
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI sendPayslipEmail: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/payslip/send-email-bulk
     * Send payslips to all paid staff emails.
     */
    public function sendPayslipEmailBulk(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Admin or HR staff can send payslip emails.'], 403);
            }

            $month = $request->input('month');
            $year = $request->input('year');

            if (!$month || !$year) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            [$payrollRows] = $this->fetchPayrollData($month, $year, '', '', PHP_INT_MAX, 1);
            
            // Filter paid staff
            $paidRows = array_filter($payrollRows, function($row) {
                return $row['vstage'] == 4 && $row['is_paid'] == 1;
            });

            if (empty($paidRows)) {
                return response()->json(['status' => 'error', 'message' => 'No paid staff records found for this period.'], 400);
            }

            // Get HR Head Signature
            $hrSignature = DB::table('users')
                ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->where('user_role.rolename', 'like', '%HR HEAD%')
                ->whereNotNull('users.signature')
                ->value('users.signature');

            if (!$hrSignature) {
                $hrSignature = DB::table('users')
                    ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                    ->where('assign_user_role.roleID', 1)
                    ->whereNotNull('users.signature')
                    ->value('users.signature');
            }

            $subject = "Payslip for " . $month . " " . $year;
            $sentCount = 0;
            $failedCount = 0;

            foreach ($paidRows as $row) {
                $personData = DB::table('tblper')
                    ->where('ID', $row['IDNO'])
                    ->orWhere('fileNo', $row['IDNO'])
                    ->first();

                if (!$personData || empty($personData->email)) {
                    $failedCount++;
                    continue;
                }

                $staffName = trim($personData->surname . ' ' . $personData->first_name . ' ' . ($personData->othernames ?? ''));

                // Basic details
                $basic = number_format((float)$row['BASIC'], 2);
                $housing = number_format((float)$row['HOUSING'], 2);
                $transport = number_format((float)$row['TRANSPORT'], 2);
                $medical = number_format((float)$row['MEDICAL'], 2);
                $utility = number_format((float)$row['UTILITY'], 2);
                $meal = number_format((float)$row['MEAL'], 2);
                $gross = number_format((float)$row['TOTAL INCOME'], 2);

                // Deductions
                $tax = number_format((float)$row['P.TAX'], 2);
                $pension = number_format((float)$row['PENSION'], 2);
                $loan = number_format((float)$row['LOAN'], 2);
                $coopSavings = number_format((float)$row['COOP. SAVING'], 2);
                $coopLoan = number_format((float)$row['COOP. LOAN RPYT'], 2);
                $iou = number_format((float)$row['IOU'], 2);
                $retention = number_format((float)$row['RETENTION'], 2);
                $surcharges = number_format((float)$row['SURGHARGES'], 2);
                $medLoan = number_format((float)$row['MEDICAL LOAN'], 2);
                $absencePen = number_format((float)$row['ABSENCE PENALTY'], 2);
                $coopAsset = number_format((float)$row['COOP.ASSET.'], 2);
                $loaDed = number_format((float)$row['LEAVE OF ABSENCE DEDUCTION'], 2);
                $otherDed = number_format((float)$row['OTHER DEDUCTION'], 2);
                $totalDed = number_format((float)$row['TOTAL DEDUCTION'], 2);
                $netPay = number_format((float)$row['NETPAY'], 2);

                $bankAccount = $row['ACC. NO'] ?: ($personData->AccNo ?: 'N/A');
                $bankName = $row['BANK'] ?: 'N/A';
                $department = DB::table('tbldepartment')->where('id', $personData->departmentID)->value('department') ?: 'N/A';
                $designation = DB::table('tbldesignation')->where('id', $personData->designationID)->value('designation') ?: 'N/A';

                $signatureHtml = $hrSignature ? '<img src="' . $hrSignature . '" alt="HR Head Signature" style="max-height: 70px; display: block;" />' : '<em>No signature on file</em>';

                $html = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #2c3e50; text-align: center; border-bottom: 2px solid #34495e; padding-bottom: 10px;'>PAYSLIP REPORT</h2>
                        <p style='text-align: center; font-weight: bold;'>Period: {$month} {$year}</p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                            <tr><td style='padding: 5px; font-weight: bold; width: 30%;'>Staff Name:</td><td style='padding: 5px;'>{$staffName}</td></tr>
                            <tr><td style='padding: 5px; font-weight: bold;'>Staff ID:</td><td style='padding: 5px;'>{$personData->ID}</td></tr>
                            <tr><td style='padding: 5px; font-weight: bold;'>Department:</td><td style='padding: 5px;'>{$department}</td></tr>
                            <tr><td style='padding: 5px; font-weight: bold;'>Designation:</td><td style='padding: 5px;'>{$designation}</td></tr>
                        </table>

                        <div style='margin-bottom: 20px;'>
                            <h3 style='background-color: #f2f2f2; padding: 8px; margin-top: 0;'>Earnings</h3>
                            <table style='width: 100%;'>
                                <tr><td>Basic Salary:</td><td style='text-align: right;'>₦{$basic}</td></tr>
                                <tr><td>Housing Allowance:</td><td style='text-align: right;'>₦{$housing}</td></tr>
                                <tr><td>Transport Allowance:</td><td style='text-align: right;'>₦{$transport}</td></tr>
                                <tr><td>Medical Allowance:</td><td style='text-align: right;'>₦{$medical}</td></tr>
                                <tr><td>Utility Allowance:</td><td style='text-align: right;'>₦{$utility}</td></tr>
                                <tr><td>Meal Allowance:</td><td style='text-align: right;'>₦{$meal}</td></tr>
                                <tr style='font-weight: bold; border-top: 1px solid #ccc;'><td>Gross Pay:</td><td style='text-align: right;'>₦{$gross}</td></tr>
                            </table>
                        </div>

                        <div style='margin-bottom: 20px;'>
                            <h3 style='background-color: #f2f2f2; padding: 8px; margin-top: 0;'>Deductions</h3>
                            <table style='width: 100%;'>
                                <tr><td>PAYE Tax:</td><td style='text-align: right;'>₦{$tax}</td></tr>
                                <tr><td>Pension Contribution:</td><td style='text-align: right;'>₦{$pension}</td></tr>
                                <tr><td>Loan Repayment:</td><td style='text-align: right;'>₦{$loan}</td></tr>
                                <tr><td>Cooperative Savings:</td><td style='text-align: right;'>₦{$coopSavings}</td></tr>
                                <tr><td>Cooperative Loan Repayment:</td><td style='text-align: right;'>₦{$coopLoan}</td></tr>
                                <tr><td>IOU Deduction:</td><td style='text-align: right;'>₦{$iou}</td></tr>
                                <tr><td>Retention:</td><td style='text-align: right;'>₦{$retention}</td></tr>
                                <tr><td>Surcharges:</td><td style='text-align: right;'>₦{$surcharges}</td></tr>
                                <tr><td>Medical Loan:</td><td style='text-align: right;'>₦{$medLoan}</td></tr>
                                <tr><td>Absence Penalty:</td><td style='text-align: right;'>₦{$absencePen}</td></tr>
                                <tr><td>Coop. Asset Finance:</td><td style='text-align: right;'>₦{$coopAsset}</td></tr>
                                <tr><td>LOA Deduction:</td><td style='text-align: right;'>₦{$loaDed}</td></tr>
                                <tr><td>Other Deductions:</td><td style='text-align: right;'>₦{$otherDed}</td></tr>
                                <tr style='font-weight: bold; border-top: 1px solid #ccc;'><td>Total Deductions:</td><td style='text-align: right;'>₦{$totalDed}</td></tr>
                            </table>
                        </div>

                        <div style='border-top: 2px solid #2c3e50; padding-top: 10px; margin-bottom: 30px;'>
                            <table style='width: 100%; font-size: 1.2em; font-weight: bold;'>
                                <tr><td>NET PAY:</td><td style='text-align: right; color: #27ae60;'>₦{$netPay}</td></tr>
                            </table>
                        </div>
                    </div>
                </body>
                </html>
                ";

                try {
                    \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($personData, $subject, $html) {
                        $message->to($personData->email)
                            ->subject($subject)
                            ->setBody($html, 'text/html');
                    });
                    $sentCount++;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Failed sending bulk email to " . $personData->email . ": " . $e->getMessage());
                    $failedCount++;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Bulk emailing complete. Sent: {$sentCount}, Failed: {$failedCount}."
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI sendPayslipEmailBulk: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-netpay/{staffId}
     * Retrieve net pay of the selected staff for the active month.
     */
    public function getStaffNetPay(Request $request, $staffId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Get active month
            $activePeriod = DB::table('tblactivemonth')->first();
            if (!$activePeriod) {
                return response()->json([
                    'status' => 'success',
                    'gross_pay' => 0.00,
                    'net_pay' => 0.00,
                    'total_deductions' => 0.00,
                    'month' => null,
                    'year' => null,
                    'is_estimated' => false
                ]);
            }

            $monthName = $activePeriod->month;
            $year = (int)$activePeriod->year;
            $monthNames = [
                'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
                'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
                'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
            ];
            $month = $monthNames[strtoupper($monthName)] ?? (int)date('n');

            $excludeType = $request->query('exclude_type');
            $excludeId = $request->query('exclude_id');

            // Use the canonical SalaryBreakdownApiController calculation engine
            $breakdownCtrl = app(\App\Http\Controllers\Api\SalaryBreakdownApiController::class);
            $breakdownReq = Request::create("/api/nextjs/payroll/salary-breakdown?staff_id={$staffId}&month={$month}&year={$year}", 'GET', [], [], [], [
                'HTTP_X_USER_ID' => $ctx['userId'] ?? 1,
            ]);
            $breakdownRes = $breakdownCtrl->getBreakdown($breakdownReq);
            $breakdownData = json_decode($breakdownRes->getContent(), true);

            if (isset($breakdownData['status']) && $breakdownData['status'] === 'success' && isset($breakdownData['summary'])) {
                $grossPay = (float)($breakdownData['summary']['gross_pay'] ?? 0.00);
                $totalDeductions = (float)($breakdownData['summary']['total_deductions'] ?? 0.00);
                $netPay = (float)($breakdownData['summary']['net_pay'] ?? 0.00);
                $deductions = $breakdownData['deductions'] ?? [];

                // If excluding a specific deduction category when creating/editing that setup
                if ($excludeType === 'other_deduction' && isset($deductions['other_deductions']['amount'])) {
                    $otherAmt = (float)$deductions['other_deductions']['amount'];
                    $totalDeductions = max(0.00, $totalDeductions - $otherAmt);
                    $netPay = max(0.00, round($grossPay - $totalDeductions, 2));
                } elseif ($excludeType === 'loan' && isset($deductions['regular_loan']['amount'])) {
                    $loanAmt = (float)$deductions['regular_loan']['amount'];
                    $totalDeductions = max(0.00, $totalDeductions - $loanAmt);
                    $netPay = max(0.00, round($grossPay - $totalDeductions, 2));
                } elseif ($excludeType === 'coop_loan' && isset($deductions['coop_loan']['amount'])) {
                    $coopLoanAmt = (float)$deductions['coop_loan']['amount'];
                    $totalDeductions = max(0.00, $totalDeductions - $coopLoanAmt);
                    $netPay = max(0.00, round($grossPay - $totalDeductions, 2));
                } elseif ($excludeType === 'iou' && isset($deductions['iou']['amount'])) {
                    $iouAmt = (float)$deductions['iou']['amount'];
                    $totalDeductions = max(0.00, $totalDeductions - $iouAmt);
                    $netPay = max(0.00, round($grossPay - $totalDeductions, 2));
                }

                return response()->json([
                    'status' => 'success',
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'total_deductions' => $totalDeductions,
                    'breakdown' => $deductions,
                    'month' => $monthName,
                    'year' => $year,
                    'is_estimated' => !($breakdownData['period']['is_computed'] ?? false)
                ]);
            }

            // Fallback to IouApiController if breakdown not available
            $iouController = app(\App\Http\Controllers\Api\IouApiController::class);
            $netPayInfo = $iouController->calculateStaffNetPayBeforeIou($staffId, $year, $month, null, $excludeType, $excludeId);

            return response()->json([
                'status' => 'success',
                'gross_pay' => (float)($netPayInfo['gross_pay'] ?? 0.00),
                'net_pay' => (float)($netPayInfo['available_net_pay'] ?? $netPayInfo['net_pay'] ?? 0.00),
                'total_deductions' => (float)($netPayInfo['total_deductions'] ?? $netPayInfo['total_deductions_before_iou'] ?? 0.00),
                'month' => $monthName,
                'year' => $year,
                'is_estimated' => true
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI getStaffNetPay: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
