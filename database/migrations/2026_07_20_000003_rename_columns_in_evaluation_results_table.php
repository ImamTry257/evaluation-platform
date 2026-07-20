<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->renameColumn('responseSessionId', 'response_session_id');
            $table->renameColumn('overallScore', 'overall_score');
            $table->renameColumn('overallPercentage', 'overall_percentage');
            $table->renameColumn('overallCategory', 'overall_category');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->renameColumn('response_session_id', 'responseSessionId');
            $table->renameColumn('overall_score', 'overallScore');
            $table->renameColumn('overall_percentage', 'overallPercentage');
            $table->renameColumn('overall_category', 'overallCategory');
        });
    }
};
