<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title_text')->default('Bienvenido a Capachica');
            $table->string('title_color')->default('#1f2937'); // gris oscuro
            $table->string('title_size')->default('3rem');
            $table->text('description')->nullable();
            $table->string('background_color')->default('#ffffff');
            $table->json('image_path')->nullable();
            $table->timestamps();
        });

        // Insertar valores por defecto
        DB::table('homepage_settings')->insert([
            'description'      => 'Explora nuestros tours inolvidables...',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('homepage_settings');
    }
};
