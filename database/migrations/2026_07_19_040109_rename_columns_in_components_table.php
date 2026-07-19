<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->renameColumn('questionnaireId', 'questionnaire_id');
            $table->renameColumn('orderNumber', 'order_number');
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->renameColumn('questionnaire_id', 'questionnaireId');
            $table->renameColumn('order_number', 'orderNumber');
        });
    }
};
