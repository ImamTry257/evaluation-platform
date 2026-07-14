<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responseSessionId')->constrained('response_sessions')->cascadeOnDelete();
            $table->decimal('overallScore', 10, 2);
            $table->decimal('overallPercentage', 5, 2);
            $table->string('overallCategory', 1);
            $table->text('conclusion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_results');
    }
};
