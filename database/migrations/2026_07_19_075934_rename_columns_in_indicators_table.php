<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            if (Schema::hasColumn('indicators', 'subComponentId')) {
                $table->renameColumn('subComponentId', 'sub_component_id');
            }
            if (Schema::hasColumn('indicators', 'orderNumber')) {
                $table->renameColumn('orderNumber', 'order_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            if (Schema::hasColumn('indicators', 'sub_component_id')) {
                $table->renameColumn('sub_component_id', 'subComponentId');
            }
            if (Schema::hasColumn('indicators', 'order_number')) {
                $table->renameColumn('order_number', 'orderNumber');
            }
        });
    }
};
