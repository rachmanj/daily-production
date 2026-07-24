<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('equipment_id');
            $table->string('unit_code')->nullable();
            $table->foreignId('pit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->decimal('prod_ob_bcm', 14, 2)->default(0);
            $table->decimal('prod_coal_ton', 14, 2)->default(0);
            $table->string('operator_name')->nullable();
            $table->timestamps();

            $table->index('daily_entry_id');
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_deployments');
    }
};
