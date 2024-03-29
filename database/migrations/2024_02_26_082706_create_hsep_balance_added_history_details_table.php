<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHsepBalanceAddedHistoryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hsep_balance_added_history_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hsep_balance_added_history_id');
            $table->bigInteger('employee_id');
            $table->bigInteger('user_id');
            $table->bigInteger('leave_policy_id');
            $table->float('added_balances')->default(0.00);
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
        Schema::dropIfExists('hsep_balance_added_history_details');
    }
}
