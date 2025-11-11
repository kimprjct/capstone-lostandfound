<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeFoundToFoundItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('found_items', function (Blueprint $table) {
            $table->time('time_found')->nullable()->after('date_found');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('found_items', function (Blueprint $table) {
            $table->dropColumn('time_found');
        });
    }
}
