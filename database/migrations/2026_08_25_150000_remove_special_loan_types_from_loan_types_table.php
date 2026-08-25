<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveSpecialLoanTypesFromLoanTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('loan_types')) {
            DB::table('loan_types')
                ->whereIn(DB::raw('LOWER(name)'), [
                    'salary advance',
                    'cooperative loan',
                    'medical loan'
                ])
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
        if (Schema::hasTable('loan_types')) {
            DB::table('loan_types')->insertOrIgnore([
                ['name' => 'Salary Advance', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cooperative Loan', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Medical Loan', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
