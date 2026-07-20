<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('response_sessions', function (Blueprint $table) {
            $table->renameColumn('userId', 'user_id');
            $table->renameColumn('questionnaireId', 'questionnaire_id');
            $table->renameColumn('startedAt', 'started_at');
            $table->renameColumn('submittedAt', 'submitted_at');
            $table->renameColumn('remainingSeconds', 'remaining_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('response_sessions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'userId');
            $table->renameColumn('questionnaire_id', 'questionnaireId');
            $table->renameColumn('started_at', 'startedAt');
            $table->renameColumn('submitted_at', 'submittedAt');
            $table->renameColumn('remaining_seconds', 'remainingSeconds');
        });
    }
};
