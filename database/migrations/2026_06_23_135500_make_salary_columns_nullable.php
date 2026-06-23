<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeSalaryColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE salary_structures MODIFY tax_rate decimal(5,2) NULL');
        DB::statement('ALTER TABLE salary_structures MODIFY declare_salary decimal(15,2) NULL');
        DB::statement('ALTER TABLE first_salary_structure MODIFY declare_salary decimal(15,2) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE salary_structures MODIFY tax_rate decimal(5,2) NOT NULL DEFAULT 0.00');
        DB::statement('ALTER TABLE salary_structures MODIFY declare_salary decimal(15,2) NOT NULL DEFAULT 0.00');
        DB::statement('ALTER TABLE first_salary_structure MODIFY declare_salary decimal(15,2) NOT NULL DEFAULT 0.00');
    }
}
