<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLoanTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->unique();
            $table->timestamps();
        });

        // Seed default loan types
        DB::table('loan_types')->insert([
            ['name' => 'Personal Loan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Car Loan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Housing Loan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salary Advance', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cooperative Loan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medical Loan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_types');
    }
}
