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
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); # Quien realiza el movimiento
            $table->foreignId('branch_id')->constrained()->onDelete('cascade'); # Sucursal donde se realiza el movimiento
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); # Producto afectado
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');

            # Detalles del movimiento
            $table->enum('type', ['purchase', 'sale', 'adjustment_in', 'adjustment_out', 'transfer']);
            $table->decimal('quantity', 10, 2); # Cantidad movida

            # Auditoría
            $table->decimal('cost_at_movement', 10, 2); # Costo del producto al momento del movimiento
            $table->decimal('price_at_movement', 10, 2)->nullable(); # A cuanto se vendió, puede ser nulo si no es una venta
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
