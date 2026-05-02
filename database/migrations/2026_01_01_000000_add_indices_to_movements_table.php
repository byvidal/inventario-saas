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
        Schema::table('movements', function (Blueprint $table) {
            // Índices para reportes y queries frecuentes
            $table->index(['company_id', 'type']);           // Para reportes por tipo
            $table->index(['branch_id', 'created_at']);      // Para histórico de sucursal
            $table->index(['product_id', 'created_at']);     // Para kardex de producto
            $table->index(['user_id', 'created_at']);        // Para auditoría de usuario
            $table->index(['supplier_id', 'created_at']);    // Para compras de proveedor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'type']);
            $table->dropIndex(['branch_id', 'created_at']);
            $table->dropIndex(['product_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['supplier_id', 'created_at']);
        });
    }
};
