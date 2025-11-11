<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserReadColumnsToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('user_read')->default(false)->after('is_read');
            $table->timestamp('user_read_at')->nullable()->after('user_read');
            
            // Add index for better performance
            $table->index(['user_read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_read', 'created_at']);
            $table->dropColumn(['user_read', 'user_read_at']);
        });
    }
}
