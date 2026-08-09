<?php

namespace Database\Seeders;

use App\Models\RespondentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RespondentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $titles = ['KS', 'GURU', 'MURID'];

        foreach ($titles as $title) {
            RespondentType::updateOrCreate(
                ['title' => $title],
                ['title' => $title]
            );
        }
    }
}
