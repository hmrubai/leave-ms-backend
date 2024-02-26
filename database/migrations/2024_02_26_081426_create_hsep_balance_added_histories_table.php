<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHsepBalanceAddedHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hsep_balance_added_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('month_in_number');
            $table->integer('year');
            $table->bigInteger('added_by');
            $table->dateTime('added_at');
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
        Schema::dropIfExists('hsep_balance_added_histories');
    }
}
