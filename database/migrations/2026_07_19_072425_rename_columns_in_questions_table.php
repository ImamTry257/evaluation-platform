<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('indicatorId', 'indicator_id');
            $table->renameColumn('questionText', 'question_text');
            $table->renameColumn('orderNumber', 'order_number');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('indicator_id', 'indicatorId');
            $table->renameColumn('question_text', 'questionText');
            $table->renameColumn('order_number', 'orderNumber');
        });
    }
};
