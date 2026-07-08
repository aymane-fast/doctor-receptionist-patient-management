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
        // The patient create form only requires name/phone/ID card, but these
        // columns were NOT NULL, so submitting the form without them crashed.
        Schema::table('patients', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
            $table->enum('gender', ['male', 'female'])->nullable()->change();
        });

        // Legacy single-medication columns; prescriptions now store medications
        // in prescription_items, so new rows no longer fill these.
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('medication_name')->nullable()->change();
            $table->string('dosage')->nullable()->change();
            $table->string('frequency')->nullable()->change();
            $table->integer('duration_days')->nullable()->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'is_current')) {
                $table->dropColumn('is_current');
            }

            $table->index(['doctor_id', 'appointment_date'], 'appointments_doctor_date_index');
            $table->index(['appointment_date', 'status'], 'appointments_date_status_index');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->index(['doctor_id', 'visit_date'], 'medical_records_doctor_visit_index');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index(['doctor_id', 'prescribed_date'], 'prescriptions_doctor_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex('prescriptions_doctor_date_index');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex('medical_records_doctor_visit_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_doctor_date_index');
            $table->dropIndex('appointments_date_status_index');
            $table->boolean('is_current')->default(false)->after('appointment_time');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('medication_name')->nullable(false)->change();
            $table->string('dosage')->nullable(false)->change();
            $table->string('frequency')->nullable(false)->change();
            $table->integer('duration_days')->nullable(false)->change();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->date('birth_date')->nullable(false)->change();
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();
        });
    }
};
