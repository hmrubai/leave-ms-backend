<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveBalanceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_balance_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id');
            $table->bigInteger('employment_type_id');
            $table->bigInteger('leave_policy_id');
            $table->float('total_days')->default(0.00);
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
        Schema::dropIfExists('leave_balance_settings');
    }
}
