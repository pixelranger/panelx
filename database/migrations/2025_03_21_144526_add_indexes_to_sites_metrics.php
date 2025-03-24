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
        Schema::table('sites_metrics', function (Blueprint $table) {
            $table->integer('ix')->default(0)->after('total_revenue');
            $table->integer('yandex_indexed')->default(0)->after('ix');
            $table->integer('google_indexed')->default(0)->after('yandex_indexed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites_metrics', function (Blueprint $table) {
            $table->dropColumn(['ix', 'yandex_indexed', 'google_indexed']);
        });
    }
};
