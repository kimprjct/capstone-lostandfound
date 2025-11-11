<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotoToClaimsTable extends Migration
{
  
    public function up(): void
{
    Schema::table('claims', function (Blueprint $table) {
        // Check if photo column doesn't exist before adding it
        if (!Schema::hasColumn('claims', 'photo')) {
            $table->string('photo')->nullable()->after('claim_reason');
        }
    });
}

public function down(): void
{
    Schema::table('claims', function (Blueprint $table) {
        $table->dropColumn('photo');
    });
}

}