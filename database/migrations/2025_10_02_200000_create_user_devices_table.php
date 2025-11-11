<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('expo_push_token')->index();
            $table->string('platform')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'expo_push_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};


