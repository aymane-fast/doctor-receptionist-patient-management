<?php

return [
    'general_practice' => [
        'label' => 'General Practice',
        'description' => 'Everyday primary care, prevention, and follow-up visits.',
        'icon' => 'fa-stethoscope',
        'defaults' => ['appointment_duration' => 20, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Routine consultation', 'Preventive check-up', 'Medication review', 'Follow-up visit', 'Lab results review',
        ],
        'patient_fields' => [
            ['key' => 'insurance_provider', 'label' => 'Insurance provider', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ['key' => 'preferred_pharmacy', 'label' => 'Preferred pharmacy', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
        ],
        'record_fields' => [
            ['key' => 'lifestyle_notes', 'label' => 'Lifestyle notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
            ['key' => 'follow_up_plan', 'label' => 'Follow-up plan', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [18, 82],
            'allergies' => ['Penicillin', 'Pollen', 'Peanuts'],
            'conditions' => ['Hypertension', 'Type 2 Diabetes', 'Asthma'],
            'patient_data' => [
                'insurance_provider' => ['Atlas Health', 'Mutuelle Care', ''],
                'preferred_pharmacy' => ['Central Pharmacy', 'Neighborhood Pharmacy', ''],
            ],
            'symptoms' => ['Headache and fatigue', 'Cough and congestion', 'Abdominal discomfort'],
            'diagnoses' => ['Viral infection', 'Hypertension follow-up', 'Seasonal allergies'],
            'treatments' => ['Supportive care and monitoring', 'Lifestyle adjustments', 'Medication review'],
            'medications' => [
                ['name' => 'Acetaminophen', 'dosage' => '500 mg', 'frequency' => 'Twice daily', 'duration' => 5, 'instructions' => 'Take with water when needed for pain or fever.'],
                ['name' => 'Vitamin D3', 'dosage' => '1000 IU', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Take with the main meal.'],
            ],
        ],
    ],

    'cardiology' => [
        'label' => 'Cardiology',
        'description' => 'Heart health, cardiovascular risk, and rhythm follow-up.',
        'icon' => 'fa-heart-pulse',
        'defaults' => ['appointment_duration' => 30, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Cardiology consultation', 'Blood pressure review', 'ECG review', 'Palpitations assessment', 'Cardiac follow-up',
        ],
        'patient_fields' => [
            ['key' => 'cardiac_history', 'label' => 'Cardiac history', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
            ['key' => 'family_cardiac_history', 'label' => 'Family cardiac history', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'record_fields' => [
            ['key' => 'ecg_summary', 'label' => 'ECG summary', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
            ['key' => 'cardiac_risk_assessment', 'label' => 'Cardiac risk assessment', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [30, 85],
            'allergies' => ['Penicillin', 'Aspirin', 'Latex'],
            'conditions' => ['Hypertension', 'Hyperlipidemia', 'Atrial fibrillation'],
            'patient_data' => [
                'cardiac_history' => ['Previous hypertension diagnosis', 'No known cardiac history', 'Prior arrhythmia assessment'],
                'family_cardiac_history' => ['Family history of hypertension', 'No relevant family history', 'Family history of coronary disease'],
            ],
            'symptoms' => ['Palpitations on exertion', 'Blood pressure follow-up', 'Occasional chest discomfort'],
            'diagnoses' => ['Controlled hypertension', 'Cardiovascular risk review', 'Benign palpitations'],
            'treatments' => ['Home blood pressure monitoring', 'Cardiac lifestyle counselling', 'ECG follow-up'],
            'medications' => [
                ['name' => 'Amlodipine', 'dosage' => '5 mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Take at the same time each day and monitor blood pressure.'],
                ['name' => 'Atorvastatin', 'dosage' => '10 mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Take in the evening as prescribed.'],
            ],
        ],
    ],

    'pediatrics' => [
        'label' => 'Pediatrics',
        'description' => 'Child health, growth, development, and family-centred care.',
        'icon' => 'fa-child-reaching',
        'capabilities' => ['guardians' => true],
        'defaults' => ['appointment_duration' => 20, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Child wellness visit', 'Growth and development review', 'Vaccination visit', 'Fever or cough assessment', 'Pediatric follow-up',
        ],
        'patient_fields' => [
            ['key' => 'guardian_name', 'label' => 'Parent or guardian name', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ['key' => 'guardian_relationship', 'label' => 'Relationship to child', 'type' => 'select', 'options' => ['Parent', 'Legal guardian', 'Other'], 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'immunization_status', 'label' => 'Immunization status', 'type' => 'select', 'options' => ['Up to date', 'Needs review', 'Unknown'], 'rules' => ['nullable', 'string', 'max:100']],
        ],
        'record_fields' => [
            ['key' => 'development_notes', 'label' => 'Development notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
            ['key' => 'feeding_notes', 'label' => 'Feeding and nutrition notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [0, 17],
            'allergies' => ['Peanuts', 'Penicillin', 'Eggs'],
            'conditions' => ['Asthma', 'Seasonal allergies', 'Eczema'],
            'patient_data' => [
                'guardian_name' => ['Maya Haddad', 'Karim Bennani', 'Sara Martin'],
                'guardian_relationship' => ['Parent', 'Legal guardian'],
                'immunization_status' => ['Up to date', 'Needs review'],
            ],
            'symptoms' => ['Fever and cough', 'Wellness visit', 'Mild rash'],
            'diagnoses' => ['Viral upper respiratory infection', 'Normal development review', 'Atopic dermatitis'],
            'treatments' => ['Hydration and symptom monitoring', 'Routine wellness guidance', 'Skin-care plan'],
            'medications' => [
                ['name' => 'Paracetamol oral suspension', 'dosage' => 'As prescribed', 'frequency' => 'Every 6 hours if needed', 'duration' => 3, 'instructions' => 'Use the dose recommended for the child\'s current weight.'],
                ['name' => 'Saline nasal spray', 'dosage' => '1 spray', 'frequency' => 'Three times daily', 'duration' => 5, 'instructions' => 'Use gently to relieve nasal congestion.'],
            ],
        ],
    ],

    'dentistry' => [
        'label' => 'Dentistry',
        'description' => 'Preventive dental care, oral health, and procedures.',
        'icon' => 'fa-tooth',
        'defaults' => ['appointment_duration' => 30, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Dental check-up', 'Tooth pain assessment', 'Dental cleaning', 'Restoration follow-up', 'Oral hygiene review',
        ],
        'patient_fields' => [
            ['key' => 'last_dental_visit', 'label' => 'Last dental visit', 'type' => 'date', 'rules' => ['nullable', 'date']],
            ['key' => 'oral_hygiene_habits', 'label' => 'Oral hygiene habits', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
        ],
        'record_fields' => [
            ['key' => 'tooth_reference', 'label' => 'Tooth or area reference', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ['key' => 'procedure_performed', 'label' => 'Procedure performed', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
            ['key' => 'xray_summary', 'label' => 'Dental X-ray summary', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [6, 82],
            'allergies' => ['Penicillin', 'Latex', 'None known'],
            'conditions' => ['Dental anxiety', 'Bruxism', 'Gingivitis'],
            'patient_data' => [
                'last_dental_visit' => ['2026-01-14', '2025-11-03', ''],
                'oral_hygiene_habits' => ['Brushes twice daily', 'Uses an electric toothbrush', ''],
            ],
            'symptoms' => ['Tooth sensitivity', 'Routine cleaning visit', 'Mild gum bleeding'],
            'diagnoses' => ['Dental caries review', 'Gingivitis', 'Routine oral health assessment'],
            'treatments' => ['Professional cleaning and hygiene advice', 'Restoration planning', 'Fluoride application'],
            'medications' => [
                ['name' => 'Ibuprofen', 'dosage' => '400 mg', 'frequency' => 'Every 8 hours if needed', 'duration' => 3, 'instructions' => 'Take with food for post-procedure discomfort.'],
                ['name' => 'Chlorhexidine mouthwash', 'dosage' => '15 ml', 'frequency' => 'Twice daily', 'duration' => 7, 'instructions' => 'Rinse and spit. Do not swallow.'],
            ],
        ],
    ],

    'dermatology' => [
        'label' => 'Dermatology',
        'description' => 'Skin, hair, and nail conditions with targeted follow-up.',
        'icon' => 'fa-hand-dots',
        'defaults' => ['appointment_duration' => 20, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Skin consultation', 'Rash assessment', 'Acne follow-up', 'Mole review', 'Dermatology follow-up',
        ],
        'patient_fields' => [
            ['key' => 'skin_type', 'label' => 'Skin type', 'type' => 'select', 'options' => ['Dry', 'Normal', 'Oily', 'Combination', 'Not recorded'], 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'sun_exposure_notes', 'label' => 'Sun exposure notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
        ],
        'record_fields' => [
            ['key' => 'affected_area', 'label' => 'Affected area', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ['key' => 'lesion_description', 'label' => 'Lesion or rash description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [12, 80],
            'allergies' => ['Latex', 'Fragrance', 'Penicillin'],
            'conditions' => ['Eczema', 'Acne', 'Psoriasis'],
            'patient_data' => ['skin_type' => ['Dry', 'Combination', 'Oily'], 'sun_exposure_notes' => ['Uses sunscreen daily', 'Outdoor work', '']],
            'symptoms' => ['Itchy rash on forearm', 'Acne flare-up', 'Changing pigmented lesion'],
            'diagnoses' => ['Atopic dermatitis', 'Acne vulgaris', 'Benign nevus review'],
            'treatments' => ['Moisturising skin-care plan', 'Topical treatment plan', 'Sun protection counselling'],
            'medications' => [
                ['name' => 'Hydrocortisone cream', 'dosage' => '1%', 'frequency' => 'Twice daily', 'duration' => 7, 'instructions' => 'Apply a thin layer to the affected skin only.'],
                ['name' => 'Benzoyl peroxide gel', 'dosage' => '2.5%', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Apply sparingly and avoid eye contact.'],
            ],
        ],
    ],

    'orthopedics' => [
        'label' => 'Orthopedics',
        'description' => 'Bones, joints, mobility, injury assessment, and recovery.',
        'icon' => 'fa-bone',
        'defaults' => ['appointment_duration' => 30, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Joint pain assessment', 'Sports injury review', 'Fracture follow-up', 'Mobility consultation', 'Orthopedic follow-up',
        ],
        'patient_fields' => [
            ['key' => 'dominant_side', 'label' => 'Dominant side', 'type' => 'select', 'options' => ['Right', 'Left', 'Ambidextrous', 'Not recorded'], 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'previous_injury_history', 'label' => 'Previous injury history', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'record_fields' => [
            ['key' => 'affected_joint_or_area', 'label' => 'Affected joint or area', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ['key' => 'mobility_assessment', 'label' => 'Mobility assessment', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [16, 85],
            'allergies' => ['Latex', 'NSAIDs', 'Penicillin'],
            'conditions' => ['Osteoarthritis', 'Chronic back pain', 'Previous fracture'],
            'patient_data' => ['dominant_side' => ['Right', 'Left'], 'previous_injury_history' => ['Previous ankle sprain', 'No previous injury reported', '']],
            'symptoms' => ['Knee pain after activity', 'Lower back discomfort', 'Shoulder stiffness'],
            'diagnoses' => ['Knee osteoarthritis', 'Lumbar strain', 'Rotator cuff tendinopathy'],
            'treatments' => ['Activity modification and physiotherapy referral', 'Pain management plan', 'Mobility exercise plan'],
            'medications' => [
                ['name' => 'Naproxen', 'dosage' => '250 mg', 'frequency' => 'Twice daily', 'duration' => 5, 'instructions' => 'Take with food for short-term pain relief.'],
                ['name' => 'Topical diclofenac gel', 'dosage' => 'As prescribed', 'frequency' => 'Three times daily', 'duration' => 7, 'instructions' => 'Apply to intact skin over the painful area.'],
            ],
        ],
    ],

    'gynecology' => [
        'label' => 'Gynecology',
        'description' => 'Women\'s health, reproductive care, and preventive follow-up.',
        'icon' => 'fa-venus',
        'defaults' => ['appointment_duration' => 30, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Gynecology consultation', 'Preventive screening', 'Cycle concern', 'Pregnancy follow-up', 'Women\'s health follow-up',
        ],
        'patient_fields' => [
            ['key' => 'last_menstrual_period', 'label' => 'Last menstrual period', 'type' => 'date', 'rules' => ['nullable', 'date']],
            ['key' => 'obstetric_history', 'label' => 'Obstetric history', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'record_fields' => [
            ['key' => 'gestational_age', 'label' => 'Gestational age', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'screening_or_counselling_notes', 'label' => 'Screening or counselling notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
        ],
        'seed' => [
            'age_range' => [16, 65],
            'allergies' => ['Latex', 'Penicillin', 'None known'],
            'conditions' => ['Dysmenorrhea', 'Endometriosis', 'Iron deficiency'],
            'patient_data' => ['last_menstrual_period' => ['2026-08-01', '2026-07-24', ''], 'obstetric_history' => ['No prior pregnancies recorded', 'Routine history reviewed', '']],
            'symptoms' => ['Routine preventive visit', 'Cycle discomfort', 'Pregnancy follow-up'],
            'diagnoses' => ['Preventive screening review', 'Primary dysmenorrhea', 'Routine antenatal follow-up'],
            'treatments' => ['Health counselling and follow-up', 'Symptom management plan', 'Routine monitoring'],
            'medications' => [
                ['name' => 'Folic acid', 'dosage' => '400 mcg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Take daily as advised by the clinician.'],
                ['name' => 'Iron supplement', 'dosage' => 'As prescribed', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Take as directed; discuss side effects at follow-up.'],
            ],
        ],
    ],

    'ophthalmology' => [
        'label' => 'Ophthalmology',
        'description' => 'Eye health, vision assessment, and optical follow-up.',
        'icon' => 'fa-eye',
        'defaults' => ['appointment_duration' => 20, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Eye examination', 'Vision change assessment', 'Glasses prescription review', 'Red eye consultation', 'Ophthalmology follow-up',
        ],
        'patient_fields' => [
            ['key' => 'uses_corrective_lenses', 'label' => 'Uses corrective lenses', 'type' => 'select', 'options' => ['Yes', 'No', 'Not recorded'], 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'last_eye_exam', 'label' => 'Last eye examination', 'type' => 'date', 'rules' => ['nullable', 'date']],
        ],
        'record_fields' => [
            ['key' => 'visual_acuity', 'label' => 'Visual acuity', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ['key' => 'intraocular_pressure', 'label' => 'Intraocular pressure', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
        ],
        'seed' => [
            'age_range' => [8, 85],
            'allergies' => ['Latex', 'Eye-drop preservative', 'None known'],
            'conditions' => ['Myopia', 'Dry eye syndrome', 'Glaucoma monitoring'],
            'patient_data' => ['uses_corrective_lenses' => ['Yes', 'No'], 'last_eye_exam' => ['2025-12-02', '2026-03-11', '']],
            'symptoms' => ['Blurred distance vision', 'Dry eyes', 'Routine eye check'],
            'diagnoses' => ['Myopia review', 'Dry eye syndrome', 'Normal eye health review'],
            'treatments' => ['Updated optical prescription', 'Lubricating eye-drop plan', 'Annual monitoring'],
            'medications' => [
                ['name' => 'Artificial tears', 'dosage' => '1 drop', 'frequency' => 'Four times daily', 'duration' => 14, 'instructions' => 'Instil into the affected eye as directed.'],
                ['name' => 'Lubricating eye gel', 'dosage' => '1 application', 'frequency' => 'At bedtime', 'duration' => 14, 'instructions' => 'Use before sleep; vision may blur briefly.'],
            ],
        ],
    ],

    'psychiatry' => [
        'label' => 'Psychiatry',
        'description' => 'Mental health assessments, therapy follow-up, and care planning.',
        'icon' => 'fa-brain',
        'defaults' => ['appointment_duration' => 45, 'appointment_mode' => 'scheduled'],
        'appointment_reasons' => [
            'Mental health consultation', 'Therapy follow-up', 'Medication review', 'Mood assessment', 'Psychiatric follow-up',
        ],
        'patient_fields' => [
            ['key' => 'preferred_contact_method', 'label' => 'Preferred contact method', 'type' => 'select', 'options' => ['Phone', 'Email', 'In person', 'Not recorded'], 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'support_person', 'label' => 'Support person (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
        ],
        'record_fields' => [
            ['key' => 'mental_status_summary', 'label' => 'Mental status summary', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:3000']],
            ['key' => 'risk_assessment', 'label' => 'Risk assessment', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:3000']],
        ],
        'seed' => [
            'age_range' => [16, 80],
            'allergies' => ['Penicillin', 'None known', 'Latex'],
            'conditions' => ['Anxiety', 'Depression', 'Insomnia'],
            'patient_data' => ['preferred_contact_method' => ['Phone', 'Email', 'In person'], 'support_person' => ['Family member', 'Trusted friend', '']],
            'symptoms' => ['Persistent anxiety', 'Low mood and sleep changes', 'Medication follow-up'],
            'diagnoses' => ['Generalized anxiety disorder review', 'Depressive symptoms review', 'Insomnia'],
            'treatments' => ['Therapy follow-up plan', 'Sleep hygiene counselling', 'Shared care plan'],
            'medications' => [
                ['name' => 'Sertraline', 'dosage' => '50 mg', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Take exactly as prescribed and discuss changes at follow-up.'],
                ['name' => 'Melatonin', 'dosage' => '2 mg', 'frequency' => 'At bedtime', 'duration' => 14, 'instructions' => 'Use as directed alongside sleep hygiene measures.'],
            ],
        ],
    ],
];
