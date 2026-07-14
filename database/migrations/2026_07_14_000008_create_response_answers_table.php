<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responseSessionId')->constrained('response_sessions')->cascadeOnDelete();
            $table->foreignId('questionId')->constrained('questions')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['responseSessionId', 'questionId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_answers');
    }
};
