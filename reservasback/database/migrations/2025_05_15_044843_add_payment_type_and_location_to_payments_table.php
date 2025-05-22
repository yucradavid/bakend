<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('payments', function (Blueprint $table) {
        $table->enum('payment_type', ['virtual', 'presencial'])->default('virtual')->after('payment_method');
        $table->string('payment_location')->nullable()->after('payment_type');
    });
}

public function down(): void
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn(['payment_type', 'payment_location']);
    });
}

};
