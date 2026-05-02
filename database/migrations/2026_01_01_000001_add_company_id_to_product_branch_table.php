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
        Schema::table('product_branch', function (Blueprint $table) {
            // Add company_id for multi-tenant support
            $table->foreignId('company_id')->default(1)->constrained()->onDelete('cascade')->after('id');
            
            // Update unique constraint to include company_id
            $table->dropUnique(['product_id', 'branch_id']);
            $table->unique(['company_id', 'product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_branch', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'product_id', 'branch_id']);
            $table->unique(['product_id', 'branch_id']);
            $table->dropForeignIdFor('Company');
            $table->dropColumn('company_id');
        });
    }
};
