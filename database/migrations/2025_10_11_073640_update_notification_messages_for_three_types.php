<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateNotificationMessagesForThreeTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clear existing notifications to start fresh
        DB::table('notifications')->truncate();
        
        // Update the notification table to ensure proper structure
        Schema::table('notifications', function (Blueprint $table) {
            // Ensure we have the necessary columns
            if (!Schema::hasColumn('notifications', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('notifiable_id');
                $table->index('organization_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the organization_id column if it was added
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'organization_id')) {
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });
    }
}