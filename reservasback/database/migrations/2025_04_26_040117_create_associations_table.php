<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('associations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('region')->nullable(); // Ej: "Cusco", "Arequipa"
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('associations');
    }
};
