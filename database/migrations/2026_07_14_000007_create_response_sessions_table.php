<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete();
            $table->foreignId('questionnaireId')->constrained('questionnaires')->cascadeOnDelete();
            $table->enum('status', ['inProgress', 'submitted', 'timeout'])->default('inProgress');
            $table->dateTime('startedAt');
            $table->dateTime('submittedAt')->nullable();
            $table->integer('remainingSeconds');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_sessions');
    }
};
