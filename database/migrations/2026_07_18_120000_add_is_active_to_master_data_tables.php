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
        Schema::table('components', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('orderNumber');
        });

        Schema::table('sub_components', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('orderNumber');
        });

        Schema::table('indicators', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('orderNumber');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('orderNumber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('sub_components', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
