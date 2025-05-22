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
    Schema::table('homepage_settings', function (Blueprint $table) {
        $table->boolean('active')->default(false)->after('image_path');
    });
}

public function down()
{
    Schema::table('homepage_settings', function (Blueprint $table) {
        $table->dropColumn('active');
    });
}

};
