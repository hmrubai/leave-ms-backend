<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->bigInteger('user_id');
            $table->bigInteger('leave_policy_id');
            $table->bigInteger('fiscal_year_id');
            $table->float('total_days')->default(0.00);
            $table->float('availed_days')->default(0.00);
            $table->float('remaining_days')->default(0.00);
            $table->float('carry_forward_balance')->default(0.00);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_balances');
    }
}
