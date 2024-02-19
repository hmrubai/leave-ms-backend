<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->bigInteger('user_id');
            $table->bigInteger('leave_policy_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->float('total_applied_days')->default(0.00);
            $table->string('leave_reason')->nullable();
            $table->boolean('is_half_day')->default(0);
            $table->enum('half_day', ['Not Applicable', '1st Half', '2nd Half'])->default('Not Applicable');
            $table->enum('leave_status', ['Pending', 'Rejected', 'Approved'])->default('Pending');
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
        Schema::dropIfExists('leave_applications');
    }
}
