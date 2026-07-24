<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('equipment_id');
            $table->string('unit_code')->nullable();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fuel_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('liters', 14, 2);
            $table->decimal('working_hours', 8, 2)->nullable();
            $table->string('usage_category')->nullable();
            $table->timestamps();

            $table->index('daily_entry_id');
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_records');
    }
};
