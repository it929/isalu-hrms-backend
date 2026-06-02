<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoopSavingsSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coop_savings_setups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staffId');
            $table->decimal('monthly_saving', 15, 2);
            $table->string('start_month', 7); // Format: YYYY-MM
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            // Foreign key relation can be set if needed
            // $table->foreign('staffId')->references('ID')->on('tblper')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coop_savings_setups');
    }
}
