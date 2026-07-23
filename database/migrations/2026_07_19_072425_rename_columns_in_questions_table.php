<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'indicatorId')) {
                $table->renameColumn('indicatorId', 'indicator_id');
            }
            if (Schema::hasColumn('questions', 'questionText')) {
                $table->renameColumn('questionText', 'question_text');
            }
            if (Schema::hasColumn('questions', 'orderNumber')) {
                $table->renameColumn('orderNumber', 'order_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'indicator_id')) {
                $table->renameColumn('indicator_id', 'indicatorId');
            }
            if (Schema::hasColumn('questions', 'question_text')) {
                $table->renameColumn('question_text', 'questionText');
            }
            if (Schema::hasColumn('questions', 'order_number')) {
                $table->renameColumn('order_number', 'orderNumber');
            }
        });
    }
};
