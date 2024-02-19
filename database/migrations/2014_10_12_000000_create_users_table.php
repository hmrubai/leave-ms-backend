<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('contact_no')->nullable();
            $table->string('address')->nullable();
            $table->string('institution')->nullable();
            $table->string('education')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(1);
            $table->enum('user_type', ['GlobalAdmin', 'LocalAdmin', 'SchoolAdmin', 'Expert', 'Student'])->default('Student');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
