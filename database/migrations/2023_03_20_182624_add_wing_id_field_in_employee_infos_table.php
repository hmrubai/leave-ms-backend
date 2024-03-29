<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWingIdFieldInEmployeeInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_infos', function (Blueprint $table) {
            $table->bigInteger('wing_id')->after('designation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_infos', function (Blueprint $table) {
            $table->dropColumn('wing_id');
        });
    }
}
