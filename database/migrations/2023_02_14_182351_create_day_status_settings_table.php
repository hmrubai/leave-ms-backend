<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDayStatusSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('day_status_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saturday');
            $table->bigInteger('sunday');
            $table->bigInteger('monday');
            $table->bigInteger('tuesday');
            $table->bigInteger('wednesday');
            $table->bigInteger('thursday');
            $table->bigInteger('friday');
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
        Schema::dropIfExists('day_status_settings');
    }
}
