<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Arrival tracking: the receptionist checks patients in as they arrive,
        // and "call next patient" prefers patients who are actually waiting.
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('status');
        });

        // Statuses now include no_show; a plain string column keeps the set
        // app-managed and portable across MySQL/SQLite.
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status', 20)->default('scheduled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('appointments')->where('status', 'no_show')->update(['status' => 'cancelled']);

        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])
                ->default('scheduled')
                ->change();
            $table->dropColumn('checked_in_at');
        });
    }
};
