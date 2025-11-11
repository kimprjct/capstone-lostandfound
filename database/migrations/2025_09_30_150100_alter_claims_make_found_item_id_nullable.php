<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('claims')) {
            if (Schema::hasColumn('claims', 'found_item_id')) {
                Schema::table('claims', function (Blueprint $table) {
                    $table->dropForeign(['found_item_id']);
                });
                DB::statement('ALTER TABLE `claims` MODIFY `found_item_id` BIGINT UNSIGNED NULL');
                Schema::table('claims', function (Blueprint $table) {
                    $table->foreign('found_item_id')->references('id')->on('found_items')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('claims')) {
            if (Schema::hasColumn('claims', 'found_item_id')) {
                Schema::table('claims', function (Blueprint $table) {
                    $table->dropForeign(['found_item_id']);
                });
                DB::statement('ALTER TABLE `claims` MODIFY `found_item_id` BIGINT UNSIGNED NOT NULL');
                Schema::table('claims', function (Blueprint $table) {
                    $table->foreign('found_item_id')->references('id')->on('found_items')->onDelete('cascade');
                });
            }
        }
    }
};


