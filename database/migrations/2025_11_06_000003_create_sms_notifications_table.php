<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSmsNotificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sms_notifications', function (Blueprint $table) {
			$table->bigIncrements('SMSID');
			$table->unsignedBigInteger('UserID');
			$table->string('PhoneNumber');
			$table->text('Message');
			$table->dateTime('DateSent')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->enum('Status', ['Sent', 'Failed']);
			$table->timestamps();

			$table->foreign('UserID')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('sms_notifications');
	}
}


