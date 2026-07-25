<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('scoring_level', 'is_active')) {
            Schema::table('scoring_level', function (Blueprint $table) {
                $table->integer('is_active')->default(1);
            });
        }
    }

    public function down(): void
    {
        Schema::table('scoring_level', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
