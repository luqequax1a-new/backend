<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get units
        $meterUnit = Unit::where('text', 'Metre')->first();
        $pieceUnit = Unit::where('text', 'Adet')->first();

        if (!$meterUnit || !$pieceUnit) {
            $this->command->error('Required units (Metre, Adet) not found. Please run UnitSeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'Pamuk Kumaş',
                'slug' => Str::slug('Pamuk Kumaş'),
                'price' => 25.50,
                'unit_id' => $meterUnit->id,
                'stock_qty' => 150.75,
                'min_qty' => 10.00,
                'max_qty' => 500.00,
                'status' => 1,
                'stock_status' => 'in_stock',
            ],
            [
                'name' => 'Düğme',
                'slug' => Str::slug('Düğme'),
                'price' => 0.75,
                'unit_id' => $pieceUnit->id,
                'stock_qty' => 1000,
                'min_qty' => 50,
                'max_qty' => 5000,
                'status' => 1,
                'stock_status' => 'in_stock',
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['slug' => $productData['slug']],
                $productData
            );
        }

        $this->command->info('Products seeded successfully.');
    }
}
