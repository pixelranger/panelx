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
        Schema::create('sites_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->date('date');
            $table->integer('unique_visitors')->default(0);
            $table->integer('page_views')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['site_id', 'date']); // Гарантия уникальности записей по сайту и дате
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites_metrics');
    }
};
