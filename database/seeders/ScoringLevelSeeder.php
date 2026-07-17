<?php

namespace Database\Seeders;

use App\Models\ScoringLevel;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class ScoringLevelSeeder extends Seeder
{
    public function run(): void
    {
        $scoringLevels = [
            ['title' => 'Sangat Tidak Sesuai', 'value' => 1, 'is_active' => 1, 'description' => 'Sangat Tidak Sesuai'],
            ['title' => 'Tidak Sesuai', 'value' => 2, 'is_active' => 1, 'description' => 'Tidak Sesuai'],
            ['title' => 'Kurang Sesuai', 'value' => 3, 'is_active' => 1, 'description' => 'Kurang Sesuai'],
            ['title' => 'Netral', 'value' => 4, 'is_active' => 1, 'description' => 'Netral'],
            ['title' => 'Cukup Sesuai', 'value' => 5, 'is_active' => 1, 'description' => 'Cukup Sesuai'],
            ['title' => 'Sesuai', 'value' => 6, 'is_active' => 1, 'description' => 'Sesuai'],
            ['title' => 'Sangat Sesuai', 'value' => 7, 'is_active' => 1, 'description' => 'Sangat Sesuai'],
        ];

        foreach ($scoringLevels as $scoringLevel) {
            ScoringLevel::firstOrCreate(
                $scoringLevel
            );
        }
    }
}
