<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoundItemPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('found_item_photos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('found_item_id')->constrained()->onDelete('cascade'); 
        $table->string('path'); // store photo path
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('found_item_photos');
    }
}
