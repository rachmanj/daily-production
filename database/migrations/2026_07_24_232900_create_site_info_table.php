<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('weather')->nullable();
            $table->decimal('rain_hours', 5, 2)->default(0);
            $table->decimal('slippery_hours', 5, 2)->default(0);
            $table->integer('manpower_plan')->nullable();
            $table->integer('manpower_actual')->nullable();
            $table->text('safety_notes')->nullable();
            $table->decimal('fuel_stock_liters', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_info');
    }
};
