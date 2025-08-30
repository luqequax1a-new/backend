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
            // Add missing fields
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 80)->nullable()->index();
            }
            if (!Schema::hasColumn('products', 'description')) {
                $table->longText('description')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
            if (!Schema::hasColumn('products', 'images')) {
                $table->json('images')->nullable();
            }
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->foreignId('brand_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories');
            }
            
            // Update existing fields to match requirements
            $table->string('name', 150)->change();
            $table->string('slug', 180)->change();
            $table->decimal('price', 12, 2)->change();
            $table->decimal('stock_qty', 12, 3)->change();
            $table->decimal('min_qty', 12, 3)->nullable()->change();
            $table->decimal('max_qty', 12, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'description', 'is_active', 'images', 'brand_id', 'category_id']);
        });
    }
};
