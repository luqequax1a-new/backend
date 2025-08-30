<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Store oluştur
        $store = Store::create([
            'name' => 'Ana Mağaza',
            'address' => 'İstanbul, Türkiye',
            'phone' => '+90 212 555 0123',
            'email' => 'info@anamağaza.com',
            'is_active' => true
        ]);

        // Brand'lar oluştur
        $nike = Brand::create([
            'name' => 'Nike',
            'description' => 'Spor giyim ve ayakkabı markası',
            'is_active' => true
        ]);

        $adidas = Brand::create([
            'name' => 'Adidas',
            'description' => 'Spor giyim ve ayakkabı markası',
            'is_active' => true
        ]);

        echo "Test verileri MySQL'e başarıyla eklendi.\n";
    }
}
