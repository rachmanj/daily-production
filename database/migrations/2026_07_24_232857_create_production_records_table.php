<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->decimal('ob_removal_bcm', 14, 2)->default(0);
            $table->decimal('coal_getting_ton', 14, 2)->default(0);
            $table->decimal('coal_hauling_ton', 14, 2)->default(0);
            $table->string('activity')->nullable();
            $table->integer('truck_count')->default(0);
            $table->timestamps();

            $table->index('daily_entry_id');
            $table->index(['pit_id', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_records');
    }
};
