<?php

use App\Enums\EntrySource;
use App\Enums\EntryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->date('production_date');
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default(EntryStatus::Draft->value);
            $table->string('source')->default(EntrySource::Manual->value);
            $table->string('source_file')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['production_date', 'site_id']);
            $table->index(['production_date', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_entries');
    }
};
