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
        if (!Schema::hasColumn('components', 'is_active')) {
            Schema::table('components', function (Blueprint $table) {
                $table->integer('is_active')->default(1)->after('order_number');
            });
        }

        if (!Schema::hasColumn('sub_components', 'is_active')) {
            Schema::table('sub_components', function (Blueprint $table) {
                $table->integer('is_active')->default(1)->after('order_number');
            });
        }

        if (!Schema::hasColumn('indicators', 'is_active')) {
            Schema::table('indicators', function (Blueprint $table) {
                $table->integer('is_active')->default(1)->after('order_number');
            });
        }

        if (!Schema::hasColumn('questions', 'is_active')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->integer('is_active')->default(1)->after('order_number');
            });
        }
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
