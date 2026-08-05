<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\ScoringLevelComponent;
use Illuminate\Database\Seeder;

class ScoringLevelComponentSeeder extends Seeder
{
    /**
     * Pemetaan tingkat skala → score_title & range nilai (start_from / end_at).
     *
     * Band ini sudah di-hardcode; score_title & range ditentukan di sini,
     * tanpa perlu mengambil data dari tabel scoring_level.
     */
    private const LEVEL_BANDS = [
        1 => ['start' => 0.00, 'end' => 14.00, 'score_title' => 'SANGAT_TIDAK_SESUAI'],
        2 => ['start' => 15.00, 'end' => 21, 'score_title' => 'TIDAK_SESUAI'],
        3 => ['start' => 21, 'end' => 26, 'score_title' => 'CUKUP_SESUAI'],
        4 => ['start' => 27, 'end' => 32,  'score_title' => 'SESUAI'],
        5 => ['start' => 33, 'end' => 10000, 'score_title' => 'SANGAT_SESUAI'],
    ];

    public function run(): void
    {
        $components = Component::with('questionnaire')
            ->where('is_active', 1)
            ->get();

        if ($components->isEmpty()) {
            $this->command?->warn('Belum ada komponen aktif, seeder dilewati.');
            return;
        }

        $count = 0;

        foreach ($components as $component) {
            foreach (self::LEVEL_BANDS as $band) {
                $exists = ScoringLevelComponent::where('component_id', $component->id)
                    ->where('start_from', $band['start'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                ScoringLevelComponent::create([
                    'questionnaire_id' => $component->questionnaire_id,
                    'component_id' => $component->id,
                    'score_title' => $band['score_title'],
                    'start_from' => $band['start'],
                    'end_at' => $band['end'],
                    'is_active' => true,
                    'action_by' => 'seeder',
                ]);

                $count++;
            }
        }

        $this->command?->info("Scoring level component berhasil di-seed: {$count} baris.");
    }
}