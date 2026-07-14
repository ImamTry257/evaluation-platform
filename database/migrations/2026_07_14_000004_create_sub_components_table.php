<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('componentId')->constrained('components')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('orderNumber')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_components');
    }
};
