<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_components', function (Blueprint $table) {
            if (Schema::hasColumn('sub_components', 'componentId')) {
                $table->renameColumn('componentId', 'component_id');
            }
            if (Schema::hasColumn('sub_components', 'orderNumber')) {
                $table->renameColumn('orderNumber', 'order_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sub_components', function (Blueprint $table) {
            if (Schema::hasColumn('sub_components', 'component_id')) {
                $table->renameColumn('component_id', 'componentId');
            }
            if (Schema::hasColumn('sub_components', 'order_number')) {
                $table->renameColumn('order_number', 'orderNumber');
            }
        });
    }
};
