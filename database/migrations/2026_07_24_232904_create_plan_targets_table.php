<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pit_id')->constrained()->cascadeOnDelete();
            $table->string('metric');
            $table->string('owner');
            $table->decimal('target_value', 14, 2);
            $table->timestamps();

            $table->index('monthly_plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_targets');
    }
};
