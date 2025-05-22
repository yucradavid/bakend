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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('document_id')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->string('profile_image')->nullable();
        });
    }
    
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'document_id', 'birth_date', 'address', 'profile_image']);
        });
    }
    
};
