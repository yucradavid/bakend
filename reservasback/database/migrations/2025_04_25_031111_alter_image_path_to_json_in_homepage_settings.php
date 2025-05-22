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
    public function up(): void
    {
        // Renombramos temporalmente la columna vieja
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->renameColumn('image_path', 'image_path_old');
        });

        // Agregamos la nueva columna tipo json
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->json('image_path')->nullable()->after('image_path_old');
        });

        // Copiamos el contenido de la vieja a la nueva, convirtiéndola a JSON array
        DB::table('homepage_settings')->get()->each(function ($row) {
            $paths = $row->image_path_old ? json_encode([$row->image_path_old]) : null;
            DB::table('homepage_settings')
                ->where('id', $row->id)
                ->update(['image_path' => $paths]);
        });

        // Borramos la columna vieja
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn('image_path_old');
        });
    }

    public function down(): void
    {
        // En caso de rollback
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->string('image_path_old')->nullable();
        });

        DB::table('homepage_settings')->get()->each(function ($row) {
            $firstPath = null;
            if (is_array(json_decode($row->image_path))) {
                $firstPath = json_decode($row->image_path)[0] ?? null;
            }
            DB::table('homepage_settings')
                ->where('id', $row->id)
                ->update(['image_path_old' => $firstPath]);
        });

        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn('image_path');
            $table->renameColumn('image_path_old', 'image_path');
        });
    }
};
