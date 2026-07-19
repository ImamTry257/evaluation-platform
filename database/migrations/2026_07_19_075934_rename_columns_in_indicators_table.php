<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->renameColumn('subComponentId', 'sub_component_id');
            $table->renameColumn('orderNumber', 'order_number');
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->renameColumn('sub_component_id', 'subComponentId');
            $table->renameColumn('order_number', 'orderNumber');
        });
    }
};
