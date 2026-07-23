<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('response_sessions', function (Blueprint $table) {
            $table->unsignedInteger('session_number')->nullable()->after('remaining_seconds');
        });

        // Backfill existing sessions: hitung urutan per user_id berdasarkan started_at
        DB::statement('
            UPDATE response_sessions rs
            JOIN (
                SELECT
                    id,
                    ROW_NUMBER() OVER (
                        PARTITION BY user_id
                        ORDER BY started_at ASC, id ASC
                    ) AS rn
                FROM response_sessions
            ) seq ON rs.id = seq.id
            SET rs.session_number = seq.rn
        ');
    }

    public function down(): void
    {
        Schema::table('response_sessions', function (Blueprint $table) {
            $table->dropColumn('session_number');
        });
    }
};
