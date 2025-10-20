<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrescriptionItem;
use App\Models\Prescription;
use App\Models\Order;

class PrescriptionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prescriptions = Prescription::all();

        if ($prescriptions->isEmpty()) {
            $this->command->warn('No prescriptions found. Please run PrescriptionSeeder first.');
            return;
        }

        $itemsCreated = 0;

        // Additional medication items that can be added to prescriptions
        $additionalMedications = [
            ['name' => 'Aspirin (Low Dose)', 'dosage' => '81mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'For cardiovascular protection. Take with food. Monitor for bleeding.'],
            ['name' => 'Calcium Carbonate', 'dosage' => '500mg', 'frequency' => 'Twice daily', 'duration' => 90, 'instructions' => 'Calcium supplement. Take with meals for better absorption.'],
            ['name' => 'Multivitamin', 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'General nutritional supplement. Take with breakfast.'],
            ['name' => 'Probiotics', 'dosage' => '1 capsule', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Digestive health support. Take on empty stomach or with light meal.'],
            ['name' => 'Acetaminophen (Extra Strength)', 'dosage' => '650mg', 'frequency' => 'As needed', 'duration' => 14, 'instructions' => 'For pain relief. Maximum 4 grams per day. Do not exceed recommended dose.'],
            ['name' => 'Diphenhydramine', 'dosage' => '25mg', 'frequency' => 'As needed for sleep', 'duration' => 7, 'instructions' => 'Sleep aid. May cause drowsiness. Do not drive after taking.'],
            ['name' => 'Famotidine', 'dosage' => '20mg', 'frequency' => 'Twice daily', 'duration' => 30, 'instructions' => 'Acid reducer. Take before meals. Can be taken with or without food.'],
            ['name' => 'Simethicone', 'dosage' => '40mg', 'frequency' => 'As needed', 'duration' => 14, 'instructions' => 'For gas relief. Take after meals and at bedtime as needed.'],
            ['name' => 'Magnesium Oxide', 'dosage' => '400mg', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Magnesium supplement. May cause loose stools. Take with food.'],
            ['name' => 'Zinc Sulfate', 'dosage' => '50mg', 'frequency' => 'Once daily', 'duration' => 14, 'instructions' => 'Immune support. Take on empty stomach. May cause nausea if taken without food.'],
        ];

        // Create additional prescription items for some prescriptions (about 30% of them)
        $prescriptionsWithItems = $prescriptions->random(min(30, $prescriptions->count()));

        foreach ($prescriptionsWithItems as $prescription) {
            // Add 1-3 additional items per prescription
            $itemCount = rand(1, 3);
            $selectedMedications = collect($additionalMedications)->random($itemCount);

            foreach ($selectedMedications as $medication) {
                $prescriptionItem = [
                    'prescription_id' => $prescription->id,
                    'medication_name' => $medication['name'],
                    'dosage' => $medication['dosage'],
                    'frequency' => $medication['frequency'],
                    'duration_days' => $medication['duration'],
                    'instructions' => $medication['instructions'],
                ];

                PrescriptionItem::create($prescriptionItem);
                $itemsCreated++;
            }
        }

        $this->command->info("Successfully created {$itemsCreated} additional prescription items for comprehensive treatment plans!");
    }
}