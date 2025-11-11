<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUserTypeIdToUsersAndMigrateRoles extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'UserTypeID')) {
				$table->unsignedInteger('UserTypeID')->nullable()->after('password');
			}
		});

		// Migrate existing role values to UserTypeID
		if (Schema::hasColumn('users', 'role')) {
			DB::table('users')->where('role', 'admin')->update(['UserTypeID' => 1]);
			DB::table('users')->where('role', 'tenant')->update(['UserTypeID' => 2]);
			DB::table('users')->where('role', 'user')->update(['UserTypeID' => 3]);
		}

		// Add foreign key constraint
		Schema::table('users', function (Blueprint $table) {
			$table->foreign('UserTypeID')->references('UserTypeID')->on('user_types')->onDelete('restrict');
		});

		// Drop the old role column
		if (Schema::hasColumn('users', 'role')) {
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn('role');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Recreate role column (enum) and backfill from UserTypeID, then drop FK and column
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'role')) {
				$table->enum('role', ['admin', 'user', 'tenant'])->default('user')->after('password');
			}
		});

		// Backfill role from UserTypeID
		DB::table('users')->where('UserTypeID', 1)->update(['role' => 'admin']);
		DB::table('users')->where('UserTypeID', 2)->update(['role' => 'tenant']);
		DB::table('users')->where('UserTypeID', 3)->update(['role' => 'user']);

		Schema::table('users', function (Blueprint $table) {
			if (Schema::hasColumn('users', 'UserTypeID')) {
				$table->dropForeign(['UserTypeID']);
				$table->dropColumn('UserTypeID');
			}
		});
	}
}


