<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddOrganizationIdToClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('found_item_id')->constrained()->onDelete('cascade');
        });
        
        // Populate organization_id for existing claims based on their found_item's organization
        DB::statement('
            UPDATE claims c 
            JOIN found_items fi ON c.found_item_id = fi.id 
            SET c.organization_id = fi.organization_id
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
}
