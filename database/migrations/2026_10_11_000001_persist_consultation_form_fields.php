<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('visit_type', 30)->nullable();
            $table->text('history_present_illness')->nullable();
            $table->text('physical_examination')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn(['visit_type', 'history_present_illness', 'physical_examination']);
        });
    }
};
