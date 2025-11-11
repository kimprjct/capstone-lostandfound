<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class AddLocationAndClaimDatetimeToClaimsTable extends Migration
{
    public function up()
    {
        Schema::table('claims', function (Blueprint $table) {
            // Check if location column doesn't exist before adding it
            if (!Schema::hasColumn('claims', 'location')) {
                $table->string('location')->nullable()->after('claim_reason');
            }
            // Check if claim_datetime column doesn't exist before adding it
            if (!Schema::hasColumn('claims', 'claim_datetime')) {
                $table->dateTime('claim_datetime')->nullable()->after('location');
            }
        });
    }

    public function down()
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['location', 'claim_datetime']);
        });
    }
}
