<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // notification type (claim_update, new_item, new_claim, etc.)
            $table->morphs('notifiable'); // polymorphic relationship (user, organization, etc.)
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // additional data (item_id, claim_id, etc.)
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('category')->default('general'); // claim, item, system, announcement
            $table->timestamps();
            
            $table->index(['is_read', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
