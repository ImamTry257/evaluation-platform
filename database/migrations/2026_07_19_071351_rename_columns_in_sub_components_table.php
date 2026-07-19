<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_components', function (Blueprint $table) {
            $table->renameColumn('componentId', 'component_id');
            $table->renameColumn('orderNumber', 'order_number');
        });
    }

    public function down(): void
    {
        Schema::table('sub_components', function (Blueprint $table) {
            $table->renameColumn('component_id', 'componentId');
            $table->renameColumn('order_number', 'orderNumber');
        });
    }
};
