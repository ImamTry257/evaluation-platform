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
        Schema::table('users', function (Blueprint $table) {
            $table->string('type', 36)->after('last_login_at');
            $table->integer('city_code')->after('type');
            $table->string('city_name', 255)->after('city_code');
            $table->text('address')->after('city_name');
            $table->text('additional_info')->after('address');
            $table->integer('update_by')->after('additional_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('city_code');
            $table->dropColumn('city_name');
            $table->dropColumn('address');
            $table->dropColumn('additional_info');
            $table->dropColumn('update_by');
        });
    }
};
