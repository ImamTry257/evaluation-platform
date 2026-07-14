<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_result_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluationResultId')->constrained('evaluation_results')->cascadeOnDelete();
            $table->foreignId('indicatorId')->constrained('indicators')->cascadeOnDelete();
            $table->decimal('score', 10, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('category', 1);
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_result_details');
    }
};
