<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('operation_code')->nullable()->after('note'); // código Yape/Plin
            $table->string('receipt_url')->nullable()->after('operation_code');    // link comprobante PDF
            $table->boolean('is_confirmed')->default(false)->after('receipt_url');  // pago local confirmado
            $table->timestamp('confirmation_time')->nullable()->after('is_confirmed');   // fecha confirmación
            $table->foreignId('confirmation_by')->nullable()->constrained('users')->after('confirmation_time'); // usuario que confirmó
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'operation_code',
                'receipt_url',
                'is_confirmed',
                'confirmation_time',
                'confirmation_by'
            ]);
        });
    }
};
