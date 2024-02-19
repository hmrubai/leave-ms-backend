<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibilityCarriedByFieldInLeaveApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->string('responsibility_carried_by')->nullable()->after('half_day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('responsibility_carried_by');
        });
    }
}
