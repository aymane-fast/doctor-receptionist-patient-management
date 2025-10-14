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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('clinic_name')->nullable()->after('value');
            $table->text('clinic_address')->nullable()->after('clinic_name');
            $table->string('clinic_phone')->nullable()->after('clinic_address');
            $table->string('clinic_email')->nullable()->after('clinic_phone');
            $table->string('clinic_website')->nullable()->after('clinic_email');
            $table->string('clinic_logo')->nullable()->after('clinic_website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['clinic_name', 'clinic_address', 'clinic_phone', 'clinic_email', 'clinic_website', 'clinic_logo']);
        });
    }
};
