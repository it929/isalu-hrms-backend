<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertMidMonthUnworkedLoaToOtherDeduction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Fetch any legacy auto-created LOA records for mid-month new hires
        if (Schema::hasTable('leave_of_absent')) {
            $legacyLoas = DB::table('leave_of_absent')
                ->where('reason_of_leave', 'like', '%unworked days before appointment%')
                ->orWhere('reason_of_leave', 'like', 'Leave of absence for new staff%')
                ->get();

            // 2. Convert each into an other_deduction_setups record
            if (Schema::hasTable('other_deduction_setups')) {
                foreach ($legacyLoas as $loa) {
                    try {
                        $start = \Carbon\Carbon::parse($loa->start_date);
                        $end = \Carbon\Carbon::parse($loa->end_date);
                        $days = $start->diffInDays($end) + 1;
                        $monthStr = $start->format('Y-m');
                        $daysInMonth = (int)$start->daysInMonth;

                        $exists = DB::table('other_deduction_setups')
                            ->where('staffId', $loa->staffId)
                            ->where('start_month', $monthStr)
                            ->where(function ($q) {
                                $q->where('calculation_mode', 'days')
                                  ->orWhere('remarks', 'like', '%unworked days%')
                                  ->orWhere('remarks', 'like', '%Mid-month%');
                            })
                            ->exists();

                        if (!$exists) {
                            DB::table('other_deduction_setups')->insert([
                                'staffId'          => $loa->staffId,
                                'deduction_type'   => 'one_time',
                                'calculation_mode' => 'days',
                                'deduction_days'   => $days,
                                'days_in_month'    => $daysInMonth,
                                'daily_rate'       => 0.00,
                                'monthly_salary'   => 0.00,
                                'total_amount'     => 0.00,
                                'duration_months'  => 1,
                                'monthly_deduction'=> 0.00,
                                'balance_remaining'=> 0.00,
                                'start_month'      => $monthStr,
                                'end_month'        => $monthStr,
                                'remarks'          => "Mid-month appointment deduction (1st to {$end->day}th - {$days} unworked days)",
                                'is_active'        => 1,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    } catch (\Throwable $e) { /* ignore */ }
                }
            }

            // 3. Remove these LOA records completely so they are never treated as LOA again
            DB::table('leave_of_absent')
                ->where('reason_of_leave', 'like', '%unworked days before appointment%')
                ->orWhere('reason_of_leave', 'like', 'Leave of absence for new staff%')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No reverse needed
    }
}
