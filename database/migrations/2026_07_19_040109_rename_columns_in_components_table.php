<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            if (Schema::hasColumn('components', 'questionnaireId')) {
                $table->renameColumn('questionnaireId', 'questionnaire_id');
            }
            if (Schema::hasColumn('components', 'orderNumber')) {
                $table->renameColumn('orderNumber', 'order_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            if (Schema::hasColumn('components', 'questionnaire_id')) {
                $table->renameColumn('questionnaire_id', 'questionnaireId');
            }
            if (Schema::hasColumn('components', 'order_number')) {
                $table->renameColumn('order_number', 'orderNumber');
            }
        });
    }
};
