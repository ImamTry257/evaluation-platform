<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->unique('response_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->dropUnique(['response_session_id']);
        });
    }
};
