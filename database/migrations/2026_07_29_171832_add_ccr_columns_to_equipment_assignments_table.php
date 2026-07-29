<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_assignments', function (Blueprint $table) {
            $table->string('material_type')->nullable()->after('pit_id');
            $table->string('equipment_role')->nullable()->after('material_type');
            $table->unsignedSmallInteger('display_order')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_assignments', function (Blueprint $table) {
            $table->dropColumn(['material_type', 'equipment_role', 'display_order']);
        });
    }
};
