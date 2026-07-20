<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->renameColumn('indicatorId', 'indicator_id');
            $table->renameColumn('minScore', 'min_score');
            $table->renameColumn('maxScore', 'max_score');
            $table->renameColumn('recommendationText', 'recommendation_text');
        });
    }

    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->renameColumn('indicator_id', 'indicatorId');
            $table->renameColumn('min_score', 'minScore');
            $table->renameColumn('max_score', 'maxScore');
            $table->renameColumn('recommendation_text', 'recommendationText');
        });
    }
};
