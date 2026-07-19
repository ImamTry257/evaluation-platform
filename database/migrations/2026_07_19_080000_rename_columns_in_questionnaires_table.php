<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->renameColumn('evaluationPeriodId', 'evaluation_period_id');
            $table->renameColumn('durationMinutes', 'duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->renameColumn('evaluation_period_id', 'evaluationPeriodId');
            $table->renameColumn('duration_minutes', 'durationMinutes');
        });
    }
};
