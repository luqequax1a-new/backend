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
        Schema::table('products', function (Blueprint $table) {
            // Check if unit_id doesn't exist and add it
            if (!Schema::hasColumn('products', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->constrained('units');
            }
            
            // Add stock quantity fields
            if (!Schema::hasColumn('products', 'stock_qty')) {
                $table->decimal('stock_qty', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('products', 'min_qty')) {
                $table->decimal('min_qty', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'max_qty')) {
                $table->decimal('max_qty', 12, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_qty', 'min_qty', 'max_qty']);
            if (Schema::hasColumn('products', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });
    }
};
