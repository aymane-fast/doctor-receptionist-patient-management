<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('patients', 'is_pediatric')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->boolean('is_pediatric')->default(false);
            });
        }

        Schema::table('patients', function (Blueprint $table) {
            $table->string('id_card_number')->nullable()->change();
        });
        if (! Schema::hasTable('patient_guardians')) {
            Schema::create('patient_guardians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('relationship', 100);
                $table->string('phone');
                $table->string('email')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        // Retain legacy notes while making existing pediatric contacts usable.
        if (DB::table('settings')->where('key', 'clinic_specialty')->value('value') === 'pediatrics') {
            DB::table('patients')->update(['is_pediatric' => true]);
        }
        DB::table('patients')->orderBy('id')->chunkById(200, function ($patients) {
            foreach ($patients as $patient) {
                $data = json_decode($patient->specialty_data ?? '{}', true) ?: [];
                if (empty($data['guardian_name'])) {
                    continue;
                }
                DB::table('patients')->where('id', $patient->id)->update(['is_pediatric' => true]);
                if (DB::table('patient_guardians')->where('patient_id', $patient->id)->exists()) {
                    continue;
                }
                DB::table('patient_guardians')->insert([
                    'patient_id' => $patient->id, 'name' => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'] ?? 'Guardian',
                    'phone' => $patient->phone, 'email' => $patient->email,
                    'is_primary' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_guardians');
        Schema::table('patients', fn (Blueprint $table) => $table->dropColumn('is_pediatric'));
        // Keep IDs nullable: rollback must not invent IDs for existing children.
    }
};
