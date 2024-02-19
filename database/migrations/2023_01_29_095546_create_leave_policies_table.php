<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id');
            $table->string('leave_title');
            $table->string('leave_short_code');
            $table->integer('total_days')->default(0);
            $table->boolean('is_applicable_for_all')->default(1);
            $table->string('applicable_for')->default('Both');
            $table->boolean('is_leave_cut_applicable')->default(1);
            $table->boolean('is_carry_forward')->default(1);
            $table->boolean('is_document_upload')->default(1);
            $table->boolean('is_holiday_deduct')->default(1);
            $table->boolean('document_upload_after_days')->default(1);
            $table->boolean('max_carry_forward_days')->default(1);
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
        Schema::dropIfExists('leave_policies');
    }
}
