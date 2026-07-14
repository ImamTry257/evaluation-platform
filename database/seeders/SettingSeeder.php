<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'activePeriodId', 'value' => null, 'description' => 'Periode evaluasi yang aktif'],
            ['key' => 'evaluationDuration', 'value' => '60', 'description' => 'Durasi evaluasi dalam menit'],
            ['key' => 'autoSaveInterval', 'value' => '30', 'description' => 'Interval auto save dalam detik'],
            ['key' => 'allowResume', 'value' => 'true', 'description' => 'Izinkan resume sesi terputus'],
            ['key' => 'timeoutMinutes', 'value' => '5', 'description' => 'Timeout sebelum sesi dianggap selesai'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
