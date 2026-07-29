<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hourly_production_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('equipment_id');
            $table->string('unit_code')->nullable();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_type');
            $table->unsignedTinyInteger('hour_slot');
            $table->decimal('tonnage', 14, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('loader_info')->nullable();
            $table->timestamps();

            $table->unique(['daily_entry_id', 'equipment_id', 'material_type', 'hour_slot'], 'hourly_unique_slot');
            $table->index(['daily_entry_id', 'material_type']);
            $table->index(['equipment_id', 'hour_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hourly_production_records');
    }
};
