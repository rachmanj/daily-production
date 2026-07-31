<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_production_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('excavator_id')->nullable();
            $table->string('excavator_code')->nullable();
            $table->unsignedBigInteger('hauler_id')->nullable();
            $table->string('hauler_code')->nullable();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->string('material_type');
            $table->unsignedTinyInteger('hour_slot');
            $table->decimal('truck_capacity_bcm', 8, 2)->default(0);
            $table->decimal('volume_bcm', 10, 2)->default(0);
            $table->decimal('load_percent', 5, 2)->default(100);
            $table->decimal('trip_count', 5, 2)->default(1);
            $table->string('excavator_type')->nullable();
            $table->string('hauler_type')->nullable();
            $table->timestamps();

            $table->index(['daily_entry_id', 'excavator_id'], 'trip_daily_excavator_idx');
            $table->index(['daily_entry_id', 'hauler_id'], 'trip_daily_hauler_idx');
            $table->index(['daily_entry_id', 'material_type', 'hour_slot'], 'trip_daily_mat_hour_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_production_records');
    }
};
