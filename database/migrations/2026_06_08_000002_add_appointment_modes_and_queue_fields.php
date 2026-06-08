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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'appointment_mode')) {
                $table->enum('appointment_mode', ['scheduled', 'queue'])->default('scheduled')->after('role');
            }

            if (!Schema::hasColumn('users', 'queue_daily_limit')) {
                $table->unsignedInteger('queue_daily_limit')->nullable()->after('appointment_mode');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'appointment_mode')) {
                $table->enum('appointment_mode', ['scheduled', 'queue'])->default('scheduled')->after('doctor_id');
            }

            if (!Schema::hasColumn('appointments', 'queue_number')) {
                $table->unsignedInteger('queue_number')->nullable()->after('appointment_time');
            }

            if (!Schema::hasColumn('appointments', 'queue_position')) {
                $table->unsignedInteger('queue_position')->nullable()->after('queue_number');
            }

            if (!Schema::hasColumn('appointments', 'estimated_time')) {
                $table->time('estimated_time')->nullable()->after('queue_position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['estimated_time', 'queue_position', 'queue_number', 'appointment_mode'] as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['queue_daily_limit', 'appointment_mode'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
