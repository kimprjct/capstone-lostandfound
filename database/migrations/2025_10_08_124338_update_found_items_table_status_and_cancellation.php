<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFoundItemsTableStatusAndCancellation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('found_items', function (Blueprint $table) {
            // Drop the old status column
            $table->dropColumn('status');
        });
        
        Schema::table('found_items', function (Blueprint $table) {
            // Add new status enum with new values
            $table->enum('status', ['unclaimed', 'under_review', 'claimed', 'cancelled'])
                  ->default('unclaimed')
                  ->after('image');
            
            // Add cancellation_reason field
            $table->text('cancellation_reason')->nullable()->after('status');
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
            // Drop the new status column and cancellation_reason
            $table->dropColumn(['status', 'cancellation_reason']);
        });
        
        Schema::table('found_items', function (Blueprint $table) {
            // Revert status enum to old values
            $table->enum('status', ['reported', 'found', 'returned'])
                  ->default('reported')
                  ->after('image');
        });
    }
}
