<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'notes')) {
                $table->text('notes')->nullable()->after('prescribed_date');
            }
        });

        Schema::table('medical_records', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_records', 'oxygen_saturation')) {
                $table->unsignedTinyInteger('oxygen_saturation')->nullable()->after('heart_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            if (Schema::hasColumn('medical_records', 'oxygen_saturation')) {
                $table->dropColumn('oxygen_saturation');
            }
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
