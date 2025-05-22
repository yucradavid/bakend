<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('places', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('excerpt');          // breve descripción
        $table->text('activities')->nullable(); // JSON o texto con lista
        $table->json('stats')->nullable(); // location, altitude, climate…
        $table->string('image_url')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
