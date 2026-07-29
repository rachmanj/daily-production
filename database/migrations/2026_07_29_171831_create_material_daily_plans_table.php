<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_daily_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('material_type');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('daily_plan_tonnage', 14, 2)->default(0);
            $table->decimal('monthly_plan_tonnage', 14, 2)->default(0);
            $table->decimal('operating_hours_per_day', 5, 2)->default(20);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['site_id', 'material_type', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_daily_plans');
    }
};
