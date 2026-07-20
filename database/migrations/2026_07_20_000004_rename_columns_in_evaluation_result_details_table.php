<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_result_details', function (Blueprint $table) {
            $table->renameColumn('evaluationResultId', 'evaluation_result_id');
            $table->renameColumn('indicatorId', 'indicator_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_result_details', function (Blueprint $table) {
            $table->renameColumn('evaluation_result_id', 'evaluationResultId');
            $table->renameColumn('indicator_id', 'indicatorId');
        });
    }
};
