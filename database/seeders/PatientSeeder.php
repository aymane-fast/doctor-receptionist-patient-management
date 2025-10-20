<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
            [
                'first_name' => 'Alice',
                'last_name' => 'Nguyen',
                'birth_date' => '1988-04-12',
                'gender' => 'female',
                'phone' => '+1555000001',
                'email' => 'alice.nguyen@example.com',
                'address' => '101 Main St, Springfield',
                'id_card_number' => 'G001',
                'allergies' => 'Peanuts',
                'chronic_conditions' => 'Asthma',
                'emergency_contact_name' => 'Tom Nguyen',
                'emergency_contact_phone' => '+1555010001',
            ],
            [
                'first_name' => 'Brian',
                'last_name' => 'O’Connor',
                'birth_date' => '1979-11-23',
                'gender' => 'male',
                'phone' => '+1555000002',
                'email' => 'brian.oconnor@example.com',
                'address' => '202 Oak Ave, Riverdale',
                'id_card_number' => 'G002',
                'allergies' => null,
                'chronic_conditions' => 'Hypertension',
                'emergency_contact_name' => 'Mia O’Connor',
                'emergency_contact_phone' => '+1555010002',
            ],
            [
                'first_name' => 'Carla',
                'last_name' => 'Fernandez',
                'birth_date' => '1993-06-05',
                'gender' => 'female',
                'phone' => '+1555000003',
                'email' => 'carla.fernandez@example.com',
                'address' => '303 Pine Rd, Lakeside',
                'id_card_number' => 'G003',
                'allergies' => 'Penicillin',
                'chronic_conditions' => null,
                'emergency_contact_name' => 'Diego Fernandez',
                'emergency_contact_phone' => '+1555010003',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Kim',
                'birth_date' => '2001-02-18',
                'gender' => 'male',
                'phone' => '+1555000004',
                'email' => null,
                'address' => '404 Cedar Ln, Brookfield',
                'id_card_number' => 'G004',
                'allergies' => null,
                'chronic_conditions' => null,
                'emergency_contact_name' => 'Jin Kim',
                'emergency_contact_phone' => '+1555010004',
            ],
            [
                'first_name' => 'Eman',
                'last_name' => 'Hassan',
                'birth_date' => '1985-09-30',
                'gender' => 'male',
                'phone' => '+1555000005',
                'email' => 'eman.hassan@example.com',
                'address' => '505 Maple St, Fairview',
                'id_card_number' => 'G005',
                'allergies' => 'Shellfish',
                'chronic_conditions' => 'Diabetes Type II',
                'emergency_contact_name' => 'Omar Hassan',
                'emergency_contact_phone' => '+1555010005',
            ],
        ];

        foreach ($patients as $data) {
            Patient::create($data);
        }
    }
}


