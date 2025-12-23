<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');

            // Detalles del movimiento
            // 👇 CORRECCIÓN IMPORTANTE: Estos son los valores exactos que usa tu sistema
            $table->enum('type', [
                'purchase',      // Compra
                'sale',          // Venta
                'adjustment',    // Merma/Ajuste (Este fallaba antes)
                'transfer_in',   // Entrada por Traspaso
                'transfer_out'   // Salida por Traspaso
            ]);

            $table->decimal('quantity', 10, 2);

            // Auditoría económica
            $table->decimal('cost_at_movement', 10, 2)->nullable(); // Lo hacemos nullable por seguridad
            $table->decimal('price_at_movement', 10, 2)->nullable();

            // 👇 AGREGAMOS NOTAS DIRECTAMENTE AQUÍ
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};