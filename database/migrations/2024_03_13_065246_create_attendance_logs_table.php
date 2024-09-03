<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('finger_print_id');
            $table->date('log_date');
            $table->string('punch_log')->nullable();
            $table->time('start_time')->format('H:i')->nullable();
            $table->time('end_time')->format('H:i')->nullable();
            $table->time('total_time')->format('H:i')->nullable();
            $table->boolean('is_processed')->default(0)->nullable();
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
        Schema::dropIfExists('attendance_logs');
    }
}
