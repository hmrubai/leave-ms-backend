<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpouseFieldInEmployeeInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_infos', function (Blueprint $table) {
            $table->string('office_contact_number')->after('nid')->nullable();
            $table->string('finger_print_id')->nullable();
            $table->string('personal_alt_contact_number')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('spouse_number')->nullable();
            $table->string('fathers_contact_number')->after('father_name')->nullable();
            $table->string('mothers_contact_number')->after('mother_name')->nullable();
            $table->string('referee_office')->nullable();
            $table->string('referee_relative')->nullable();
            $table->string('referee_contact_details')->nullable();
            $table->string('key_skills')->nullable();
            $table->string('highest_level_of_study')->nullable();
            $table->string('e_tin')->nullable();
            $table->string('applicable_tax_amount')->nullable();
            $table->string('official_achievement')->nullable();
            $table->string('remarks')->nullable();
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
            $table->dropColumn('office_contact_number');
            $table->dropColumn('finger_print_id');
            $table->dropColumn('personal_alt_contact_number');
            $table->dropColumn('personal_email');
            $table->dropColumn('passport_number');
            $table->dropColumn('spouse_name');
            $table->dropColumn('spouse_number');
            $table->dropColumn('fathers_contact_number');
            $table->dropColumn('mothers_contact_number');
            $table->dropColumn('referee_office');
            $table->dropColumn('referee_relative');
            $table->dropColumn('referee_contact_details');
            $table->dropColumn('key_skills');
            $table->dropColumn('highest_level_of_study');
            $table->dropColumn('e_tin');
            $table->dropColumn('applicable_tax_amount');
            $table->dropColumn('official_achievement');
            $table->dropColumn('remarks');
        });
    }
}
