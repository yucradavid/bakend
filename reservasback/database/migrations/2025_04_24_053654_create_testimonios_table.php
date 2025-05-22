<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('testimonios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // si está autenticado
            $table->string('nombre')->nullable(); // si no está autenticado
            //$table->foreignId('lugar_id')->constrained('lugares')->onDelete('cascade'); // relación con lugares turísticos
            $table->tinyInteger('estrellas'); // de 1 a 5
            $table->text('comentario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonios');
    }
};
