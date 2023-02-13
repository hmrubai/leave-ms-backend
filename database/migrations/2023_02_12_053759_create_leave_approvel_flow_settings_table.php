<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveApprovelFlowSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_approvel_flow_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->nullable();
            $table->bigInteger('approval_authority_id')->nullable();
            $table->integer('step')->default(1);
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
        Schema::dropIfExists('leave_approvel_flow_settings');
    }
}
