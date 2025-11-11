<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUserTypesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_types', function (Blueprint $table) {
			$table->increments('UserTypeID');
			$table->string('TypeName');
			$table->timestamps();
		});

		// Seed default records
		DB::table('user_types')->insert([
			['UserTypeID' => 1, 'TypeName' => 'SuperAdmin', 'created_at' => now(), 'updated_at' => now()],
			['UserTypeID' => 2, 'TypeName' => 'Tenant', 'created_at' => now(), 'updated_at' => now()],
			['UserTypeID' => 3, 'TypeName' => 'User', 'created_at' => now(), 'updated_at' => now()],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('user_types');
	}
}


