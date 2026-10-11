<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('scheduling_override')->default(false);
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('override_reason')->nullable();
        });
        // Historical temperatures had conflicting UI labels: never guess.
        Schema::table('medical_records', fn (Blueprint $table) => $table->string('temperature_unit', 1)->nullable());
        Schema::create('medical_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('title');
            $table->string('path')->unique();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
            $table->index(['patient_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_documents');
        Schema::table('medical_records', fn (Blueprint $table) => $table->dropColumn('temperature_unit'));
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['override_by']);
            $table->dropColumn(['scheduling_override', 'override_by', 'override_reason']);
        });
    }
};
