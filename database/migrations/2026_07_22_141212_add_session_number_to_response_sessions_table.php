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

        // Backfill existing sessions: hitung urutan per user_id (kompatibel MySQL 5.7+ & MariaDB)
        DB::statement('
            UPDATE response_sessions rs
            INNER JOIN (
                SELECT id,
                    @rn := CASE
                        WHEN @prev_user = user_id THEN @rn + 1
                        ELSE 1
                    END AS session_number,
                    @prev_user := user_id
                FROM response_sessions,
                    (SELECT @rn := 0, @prev_user := NULL) vars
                ORDER BY user_id, started_at ASC, id ASC
            ) seq ON rs.id = seq.id
            SET rs.session_number = seq.session_number
        ');
    }

    public function down(): void
    {
        Schema::table('response_sessions', function (Blueprint $table) {
            $table->dropColumn('session_number');
        });
    }
};
