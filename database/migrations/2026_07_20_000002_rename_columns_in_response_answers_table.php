<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('response_answers', function (Blueprint $table) {
            $table->renameColumn('responseSessionId', 'response_session_id');
            $table->renameColumn('questionId', 'question_id');
        });
    }

    public function down(): void
    {
        Schema::table('response_answers', function (Blueprint $table) {
            $table->renameColumn('response_session_id', 'responseSessionId');
            $table->renameColumn('question_id', 'questionId');
        });
    }
};
