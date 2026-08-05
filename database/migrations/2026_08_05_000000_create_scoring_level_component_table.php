<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scoring_level_component', function (Blueprint $table) {
            // --- Primary key ---
            $table->id();

            // --- Foreign keys (relasi) ---
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();

            // --- Predikat ---
            $table->string('score_title')->nullable();       // predikat/label nilai

            // --- Range nilai ---
            $table->decimal('start_from', 10, 2)->default(0); // batas bawah range
            $table->decimal('end_at', 10, 2)->nullable();     // batas atas range

            // --- Status ---
            $table->boolean('is_active')->default(true);

            // --- Timestamp ---
            $table->timestamps();

            // --- Siapa yang melakukan aksi ---
            $table->string('action_by')->nullable();

            // --- Soft delete (timestamps + siapa yang delete) ---
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            // --- Index tambahan ---
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_level_component');
    }
};
