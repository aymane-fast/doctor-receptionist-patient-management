<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->json('specialty_data')->nullable();
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->json('specialty_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn('specialty_data');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('specialty_data');
        });
    }
};
