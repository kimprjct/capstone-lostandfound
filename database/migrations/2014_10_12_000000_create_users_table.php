<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); // required
            $table->string('middle_name'); // required now
            $table->string('last_name');  // required
            $table->string('phone_number'); // required
            $table->string('email')->unique(); // required
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // required
            $table->enum('role', ['admin', 'user', 'tenant'])->default('user'); // required, default user
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
