<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->string('unit_code');
            $table->string('description')->nullable();
            $table->string('plant_type_name')->nullable();
            $table->string('project_code');
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pit_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active_for_tracking')->default(true);
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['equipment_id', 'site_id']);
            $table->index(['site_id', 'is_active_for_tracking']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_assignments');
    }
};
