<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'metre',
                'text' => 'Metre',
                'step' => 0.01,
                'is_active' => true,
            ],
            [
                'name' => 'adet',
                'text' => 'Adet',
                'step' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($units as $unitData) {
            Unit::updateOrCreate(
                ['name' => $unitData['name']],
                $unitData
            );
        }

        $this->command->info('Units seeded successfully.');
    }
}
