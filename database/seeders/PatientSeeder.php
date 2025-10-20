<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = [
            'James', 'Robert', 'John', 'Michael', 'David', 'William', 'Richard', 'Joseph', 'Thomas', 'Christopher',
            'Charles', 'Daniel', 'Matthew', 'Anthony', 'Mark', 'Donald', 'Steven', 'Paul', 'Andrew', 'Joshua',
            'Kenneth', 'Kevin', 'Brian', 'George', 'Timothy', 'Ronald', 'Jason', 'Edward', 'Jeffrey', 'Ryan',
            'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen',
            'Nancy', 'Lisa', 'Betty', 'Helen', 'Sandra', 'Donna', 'Carol', 'Ruth', 'Sharon', 'Michelle',
            'Laura', 'Kimberly', 'Deborah', 'Dorothy', 'Maria', 'Ashley', 'Emma', 'Olivia', 'Sophia', 'Ava'
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'
        ];

        $addresses = [
            '123 Main St, Springfield', '456 Oak Ave, Riverside', '789 Pine Rd, Lakeside', '321 Elm St, Brookfield',
            '654 Maple Ave, Fairview', '987 Cedar Ln, Hillside', '147 Birch Dr, Woodland', '258 Spruce Way, Valley View',
            '369 Willow St, Meadowbrook', '741 Aspen Ct, Forest Hill', '852 Poplar Ave, Garden City', '963 Sycamore Rd, Greenwood'
        ];

        $allergies = [null, 'Peanuts', 'Shellfish', 'Dairy', 'Eggs', 'Penicillin', 'Latex', 'Dust mites'];
        $chronicConditions = [null, 'Hypertension', 'Diabetes Type II', 'Asthma', 'COPD', 'Heart disease', 'Arthritis'];
        $genders = ['male', 'female'];

        // Generate 150 patients
        for ($i = 1; $i <= 150; $i++) {
            $gender = $genders[array_rand($genders)];
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            $birthDate = now()->subYears(rand(18, 85))->subDays(rand(0, 365));
            $phone = '+1555' . str_pad($i, 6, '0', STR_PAD_LEFT);
            $email = rand(1, 10) <= 7 ? strtolower($firstName . '.' . $lastName . $i . '@example.com') : null;
            $idCardNumber = 'ID' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            $selectedAllergies = rand(1, 10) <= 3 ? $allergies[array_rand($allergies)] : null;
            $selectedChronicConditions = rand(1, 10) <= 3 ? $chronicConditions[array_rand($chronicConditions)] : null;
            
            $emergencyFirstName = $firstNames[array_rand($firstNames)];
            $emergencyLastName = rand(1, 2) === 1 ? $lastName : $lastNames[array_rand($lastNames)];
            $emergencyPhone = '+1555' . str_pad(($i + 50000), 6, '0', STR_PAD_LEFT);

            \App\Models\Patient::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $birthDate->format('Y-m-d'),
                'gender' => $gender,
                'phone' => $phone,
                'email' => $email,
                'address' => $addresses[array_rand($addresses)],
                'id_card_number' => $idCardNumber,
                'allergies' => $selectedAllergies,
                'chronic_conditions' => $selectedChronicConditions,
                'emergency_contact_name' => $emergencyFirstName . ' ' . $emergencyLastName,
                'emergency_contact_phone' => $emergencyPhone,
            ]);
        }

        $this->command->info('150 patients seeded successfully!');
    }
}
