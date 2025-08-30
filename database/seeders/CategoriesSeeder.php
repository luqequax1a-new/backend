<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'slug' => 'elektronik',
                'description' => 'Elektronik ürünler kategorisi',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Giyim',
                'slug' => 'giyim',
                'description' => 'Giyim ürünleri kategorisi',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ev & Yaşam',
                'slug' => 'ev-yasam',
                'description' => 'Ev ve yaşam ürünleri kategorisi',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('categories')->insert($categories);
    }
}
