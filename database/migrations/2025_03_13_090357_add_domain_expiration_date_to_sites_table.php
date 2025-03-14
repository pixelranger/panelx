<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('sites', function (Blueprint $table) {
            $table->date('domain_expiration_date')->nullable()->after('updated_at');
        });
    }

    public function down(): void {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('domain_expiration_date');
        });
    }
};
