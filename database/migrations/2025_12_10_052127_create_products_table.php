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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            #Clasifiaicaciones
            $table->foreignId('category_id')->constrained();
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->foreignId('unit_id')->constrained();

            $table->foreignId('tax_id')->nullable()->constrained('taxes')->onDelete('set null');

            #Datos
            $table->string('sku')->index(); #Codigo de barras o SKU
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            #Precios base
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('min_stock')->default(5);

            #Unique compuesto: El SKU debe ser unico por empresa
            $table->unique(['company_id', 'sku']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
