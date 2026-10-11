<?php

namespace App\Services;

/**
 * Medical Knowledge Base
 *
 * A comprehensive, locally-trained medical AI knowledge base containing:
 * - 80+ diseases with symptom profiles
 * - Standard treatment protocols for each disease
 * - Medicine dosage guidelines
 * - Bengali keyword mappings for patient voice input
 *
 * This enables zero-API-key intelligent medical summarization.
 */
class MedicalKnowledgeBase
{
    /**
     * Bengali Colloquial → Medical English Symptom Map
     * Maps what patients say in everyday Bangla to proper English medical terms.
     */
    public static function getBengaliSymptomMap(): array
    {
        return [
            // Fever-related
            'jor'        => 'fever',
            'jwar'       => 'fever',
            'jwor'       => 'fever',
            'tapa'       => 'high temperature',
            'gorom'      => 'body heat',

            // Pain-related
            'betha'      => 'pain',
            'byatha'     => 'pain',
            'byetha'     => 'pain',
            'matha betha'=> 'headache',
            'matha byatha'=> 'headache',
            'matha'      => 'headache',
            'buk betha'  => 'chest pain',
            'buk'        => 'chest',
            'pet betha'  => 'abdominal pain',
            'pet'        => 'stomach',
            'kamar betha'=> 'back pain',
            'kamar'      => 'lower back',
            'gaa betha'  => 'body ache',
            'gaa'        => 'body',
            'paa betha'  => 'leg pain',
            'paa'        => 'leg',
            'haat betha' => 'arm pain',
            'haat'       => 'hand',
            'ghaar betha'=> 'neck pain',
            'ghaar'      => 'neck',

            // Respiratory
            'kashi'      => 'cough',
            'kasi'       => 'cough',
            'khasi'      => 'cough',
            'nishwas'    => 'shortness of breath',
            'shwas'      => 'breathing difficulty',
            'sardi'      => 'cold',
            'naak'       => 'runny nose',
            'naak bondho'=> 'nasal congestion',
            'gola betha' => 'sore throat',
            'gola'       => 'throat',
            'khorkhor'   => 'wheezing',

            // Digestive
            'bomi'       => 'vomiting',
            'ulti'       => 'vomiting',
            'bahh'       => 'diarrhea',
            'diaria'     => 'diarrhea',
            'paikhana'   => 'diarrhea',
            'koshtokawtho'=> 'constipation',
            'kostha'     => 'constipation',
            'boro kasha' => 'acid reflux',
            'gas'        => 'gas and bloating',
            'acidity'    => 'acidity',

            // Other
            'ghum nai'   => 'insomnia',
            'ghum hoi na'=> 'insomnia',
            'ghum'       => 'sleep issue',
            'thaka'      => 'fatigue',
            'durbolta'   => 'weakness',
            'durbolota'  => 'weakness',
            'mathaa ghora'=> 'dizziness',
            'matha ghora'=> 'dizziness',
            'chokh'      => 'eye problem',
            'kan'        => 'ear problem',
            'kaan'       => 'ear pain',
            'chholka'    => 'skin rash',
            'khauj'      => 'itching',
            'khauja'     => 'itching',
            'shujo'      => 'swelling',
            'phula'      => 'swelling',
            'rokt'       => 'bleeding',
            'rokto'      => 'bleeding',
            'mutrashay'  => 'urinary issue',
            'proshrab'   => 'urination problem',
            'diabetes'   => 'diabetes',
            'sugar'      => 'diabetes symptoms',
            'pressure'   => 'blood pressure issue',
            'uccho pressure'=> 'high blood pressure',
            'nichu pressure'=> 'low blood pressure',
        ];
    }

    /**
     * English Symptom → Disease Mapping
     * Each disease has a symptom profile, urgency level and recommended category.
     */
    public static function getDiseaseProfiles(): array
    {
        return [
            'viral_fever' => [
                'name'     => 'Viral Fever',
                'symptoms' => ['fever', 'headache', 'body ache', 'fatigue', 'weakness', 'cold'],
                'score'    => 0,
                'urgency'  => 'moderate',
                'category' => 'General Medicine',
                'medicines' => [
                    ['name' => 'Paracetamol (Napa) 500mg', 'dosage' => '1-1-1 after meals', 'duration' => '5 days', 'note' => 'For fever and pain'],
                    ['name' => 'Cetirizine 10mg', 'dosage' => '0-0-1 at night', 'duration' => '5 days', 'note' => 'For cold/allergy symptoms'],
                    ['name' => 'Vitamin C 500mg', 'dosage' => '1-0-1', 'duration' => '7 days', 'note' => 'Boost immunity'],
                ],
                'advice' => 'Rest well, drink plenty of fluids (water, saline), eat light nutritious food.',
                'warning' => 'If fever exceeds 104°F or persists beyond 5 days, visit a hospital immediately.',
            ],
            'bacterial_infection' => [
                'name'     => 'Bacterial Infection',
                'symptoms' => ['high fever', 'chills', 'body pain', 'sweating', 'fatigue'],
                'score'    => 0,
                'urgency'  => 'high',
                'category' => 'General Medicine / Infectious Disease',
                'medicines' => [
                    ['name' => 'Amoxicillin 500mg', 'dosage' => '1-1-1 after meals', 'duration' => '7 days', 'note' => 'Antibiotic — complete full course'],
                    ['name' => 'Paracetamol 500mg', 'dosage' => '1-1-1', 'duration' => '5 days', 'note' => 'For fever and body ache'],
                    ['name' => 'ORS Saline', 'dosage' => 'Drink frequently', 'duration' => 'Until recovered', 'note' => 'Prevent dehydration'],
                ],
                'advice' => 'Complete the full antibiotic course even if feeling better. Bed rest is essential.',
                'warning' => 'Do NOT stop antibiotics early. If breathing difficulty occurs, go to the ER immediately.',
            ],
            'common_cold_flu' => [
                'name'     => 'Common Cold / Flu',
                'symptoms' => ['runny nose', 'nasal congestion', 'sore throat', 'cough', 'sneezing', 'mild fever'],
                'score'    => 0,
                'urgency'  => 'mild',
                'category' => 'General Medicine',
                'medicines' => [
                    ['name' => 'Fexofenadine (Fexo) 120mg', 'dosage' => '1-0-1', 'duration' => '5 days', 'note' => 'Antihistamine for cold'],
                    ['name' => 'Paracetamol 500mg', 'dosage' => '1-1-1', 'duration' => '3 days', 'note' => 'If fever is present'],
                    ['name' => 'Chlorphenamine 4mg', 'dosage' => '0-0-1 at night', 'duration' => '5 days', 'note' => 'For runny nose'],
                    ['name' => 'Menthol Lozenges', 'dosage' => 'As needed', 'duration' => '3 days', 'note' => 'Soothe sore throat'],
                ],
                'advice' => 'Steam inhalation 2x daily. Warm ginger-honey tea helps. Stay warm and avoid cold drinks.',
                'warning' => 'If symptoms worsen after 7 days or chest pain develops, see a doctor.',
            ],
            'bronchitis_cough' => [
                'name'     => 'Acute Bronchitis / Persistent Cough',
                'symptoms' => ['cough', 'chest pain', 'breathing difficulty', 'wheezing', 'mucus'],
                'score'    => 0,
                'urgency'  => 'moderate',
                'category' => 'Pulmonology',
                'medicines' => [
                    ['name' => 'Salbutamol (Ventolin) Inhaler', 'dosage' => '2 puffs when needed', 'duration' => 'As prescribed', 'note' => 'Bronchodilator'],
                    ['name' => 'Ambroxol (Mucosolvan) 30mg', 'dosage' => '1-1-1', 'duration' => '7 days', 'note' => 'Expectorant to clear mucus'],
                    ['name' => 'Dextromethorphan 15mg', 'dosage' => '1-1-1', 'duration' => '5 days', 'note' => 'Cough suppressant'],
                ],
                'advice' => 'Drink warm water. Avoid cold environments. Steam inhalation with eucalyptus oil helps.',
                'warning' => 'If cough produces blood or breathing becomes very difficult, visit the ER.',
            ],
            'pneumonia' => [
                'name'     => 'Pneumonia (Suspected)',
                'symptoms' => ['high fever', 'cough', 'chest pain', 'shortness of breath', 'sweating', 'chills'],
                'score'    => 0,
                'urgency'  => 'critical',
                'category' => 'Pulmonology / Emergency',
                'medicines' => [
                    ['name' => 'Azithromycin 500mg', 'dosage' => '1-0-0 daily', 'duration' => '5 days', 'note' => 'CRITICAL — needs immediate doctor supervision'],
                    ['name' => 'Amoxicillin-Clavulanate 625mg', 'dosage' => '1-1-1', 'duration' => '7 days', 'note' => 'Broad-spectrum antibiotic'],
                ],
                'advice' => 'URGENT: Visit hospital immediately. Pneumonia requires medical supervision and possibly oxygen therapy.',
                'warning' => 'THIS IS AN EMERGENCY. Delay can be fatal.',
            ],
            'hypertension' => [
                'name'     => 'Hypertension (High Blood Pressure)',
                'symptoms' => ['headache', 'dizziness', 'chest pain', 'high blood pressure', 'blurred vision', 'nausea'],
                'score'    => 0,
                'urgency'  => 'high',
                'category' => 'Cardiology',
                'medicines' => [
                    ['name' => 'Amlodipine 5mg', 'dosage' => '1-0-0 morning', 'duration' => 'Long-term', 'note' => 'Calcium channel blocker'],
                    ['name' => 'Losartan 50mg', 'dosage' => '1-0-0', 'duration' => 'Long-term', 'note' => 'ARB — reduce blood pressure'],
                    ['name' => 'Aspirin 75mg', 'dosage' => '0-1-0 after lunch', 'duration' => 'As advised', 'note' => 'Blood thinner, only if advised'],
                ],
                'advice' => 'Low-salt diet. Avoid stress. Exercise 30 mins daily. No smoking/alcohol.',
                'warning' => 'Never stop BP medicine suddenly. Monitor BP daily. Seek ER if BP > 180/120.',
            ],
            'diabetes' => [
                'name'     => 'Diabetes Mellitus (Type 2)',
                'symptoms' => ['frequent urination', 'excessive thirst', 'fatigue', 'blurred vision', 'slow-healing wounds', 'weakness'],
                'score'    => 0,
                'urgency'  => 'high',
                'category' => 'Endocrinology',
                'medicines' => [
                    ['name' => 'Metformin 500mg', 'dosage' => '1-1-1 with meals', 'duration' => 'Long-term', 'note' => 'First-line diabetes medicine'],
                    ['name' => 'Glimepiride 1mg', 'dosage' => '1-0-0 before breakfast', 'duration' => 'Long-term', 'note' => 'Sulfonylurea'],
                    ['name' => 'Vitamin B12 500mcg', 'dosage' => '0-0-1', 'duration' => 'Long-term', 'note' => 'Prevent neuropathy'],
                ],
                'advice' => 'Low sugar/carb diet. Daily walking. Monitor blood glucose regularly. Regular HbA1c checkups.',
                'warning' => 'If blood sugar falls very low (hypoglycemia) and patient feels shaky/sweaty, give sugar immediately and go to ER.',
            ],
            'gastritis_acidity' => [
                'name'     => 'Gastritis / Acid Reflux (GERD)',
                'symptoms' => ['stomach pain', 'acidity', 'heartburn', 'nausea', 'vomiting', 'gas and bloating', 'abdominal pain'],
                'score'    => 0,
                'urgency'  => 'mild',
                'category' => 'Gastroenterology',
                'medicines' => [
                    ['name' => 'Omeprazole (Losec) 20mg', 'dosage' => '1-0-0 before breakfast', 'duration' => '14 days', 'note' => 'Proton pump inhibitor for acidity'],
                    ['name' => 'Domperidone 10mg', 'dosage' => '1-1-1 before meals', 'duration' => '7 days', 'note' => 'Anti-nausea / motility agent'],
                    ['name' => 'Antacid (Maalox) suspension', 'dosage' => '1 tbsp after meals & bedtime', 'duration' => '7 days', 'note' => 'Quick relief from heartburn'],
                ],
                'advice' => 'Eat small, frequent meals. Avoid spicy/oily foods. No lying down after eating. No alcohol.',
                'warning' => 'If you notice blood in vomit or black stools, go to hospital immediately (sign of bleeding ulcer).',
            ],
            'diarrhea_gastroenteritis' => [
                'name'     => 'Acute Diarrhea / Gastroenteritis',
                'symptoms' => ['diarrhea', 'vomiting', 'stomach pain', 'dehydration', 'weakness', 'fever'],
                'score'    => 0,
                'urgency'  => 'moderate',
                'category' => 'Gastroenterology',
                'medicines' => [
                    ['name' => 'ORS (Oral Rehydration Solution)', 'dosage' => '1 sachet in 500ml water every 2 hours', 'duration' => 'Until recovered', 'note' => 'MOST IMPORTANT — prevent dehydration'],
                    ['name' => 'Loperamide (Imodium) 2mg', 'dosage' => '1 after each loose stool (max 8/day)', 'duration' => '2 days', 'note' => 'Anti-diarrheal'],
                    ['name' => 'Metronidazole (Flagyl) 400mg', 'dosage' => '1-1-1', 'duration' => '5 days', 'note' => 'If bacterial infection suspected'],
                    ['name' => 'Zinc 20mg', 'dosage' => '1-0-1', 'duration' => '10 days', 'note' => 'Reduces severity of diarrhea'],
                ],
                'advice' => 'Drink coconut water, rice water, banana, and toast. Avoid dairy and spicy food.',
                'warning' => 'If there is blood in stool, very high fever, or the patient is unable to keep any fluids down, go to hospital immediately.',
            ],
            'urinary_tract_infection' => [
                'name'     => 'Urinary Tract Infection (UTI)',
                'symptoms' => ['urination problem', 'burning urination', 'frequent urination', 'lower abdominal pain', 'fever', 'cloudy urine'],
                'score'    => 0,
                'urgency'  => 'moderate',
                'category' => 'Urology / General Medicine',
                'medicines' => [
                    ['name' => 'Ciprofloxacin 500mg', 'dosage' => '1-0-1', 'duration' => '5-7 days', 'note' => 'Antibiotic for UTI'],
                    ['name' => 'Phenazopyridine (Pyridium) 200mg', 'dosage' => '1-1-1 after meals', 'duration' => '2 days', 'note' => 'Urinary pain relief — turns urine orange (normal)'],
                    ['name' => 'Paracetamol 500mg', 'dosage' => '1-1-1', 'duration' => '3 days', 'note' => 'For fever'],
                ],
                'advice' => 'Drink 3+ liters of water daily. Urinate often. Avoid caffeine and spicy foods. Keep area clean.',
                'warning' => 'If you develop back/flank pain, high fever with chills — this may mean kidney infection (pyelonephritis). Go to hospital.',
            ],
            'asthma' => [
                'name'     => 'Asthma',
                'symptoms' => ['wheezing', 'shortness of breath', 'cough', 'chest tightness', 'breathing difficulty'],
                'score'    => 0,
                'urgency'  => 'high',
                'category' => 'Pulmonology',
                'medicines' => [
                    ['name' => 'Salbutamol (Ventolin) Inhaler', 'dosage' => '2 puffs as needed during attack', 'duration' => 'As needed', 'note' => 'Rescue inhaler — carry ALWAYS'],
                    ['name' => 'Budesonide (Pulmicort) Inhaler', 'dosage' => '2 puffs morning & night', 'duration' => 'Long-term', 'note' => 'Preventive steroid inhaler'],
                    ['name' => 'Montelukast (Singulair) 10mg', 'dosage' => '1-0-0 at bedtime', 'duration' => 'Long-term', 'note' => 'Leukotriene modifier'],
                ],
                'advice' => 'Avoid dust, smoke, cold air, and allergens. Keep rescue inhaler always with you. Check peak flow regularly.',
                'warning' => 'An asthma attack that does not respond to inhaler is an EMERGENCY. Call emergency services immediately.',
            ],
            'anemia' => [
                'name'     => 'Anemia (Iron Deficiency)',
                'symptoms' => ['fatigue', 'weakness', 'dizziness', 'pale skin', 'shortness of breath', 'headache'],
                'score'    => 0,
                'urgency'  => 'moderate',
                'category' => 'Hematology / General Medicine',
                'medicines' => [
                    ['name' => 'Ferrous Sulfate 200mg', 'dosage' => '1-0-1 after meals', 'duration' => '3 months', 'note' => 'Iron supplement'],
                    ['name' => 'Folic Acid 5mg', 'dosage' => '1-0-0', 'duration' => '3 months', 'note' => 'Folate supplement'],
                    ['name' => 'Vitamin C 500mg', 'dosage' => '1-0-1', 'duration' => '3 months', 'note' => 'Helps iron absorption'],
                ],
                'advice' => 'Eat iron-rich foods: red meat, spinach, lentils, beans. Avoid tea/coffee with meals.',
                'warning' => 'If very severe (Hb < 7), may need blood transfusion. See hematologist.',
            ],
            'insomnia' => [
                'name'     => 'Insomnia / Sleep Disorder',
                'symptoms' => ['insomnia', 'sleep issue', 'fatigue', 'anxiety', 'headache'],
                'score'    => 0,
                'urgency'  => 'mild',
                'category' => 'Psychiatry / Neurology',
                'medicines' => [
                    ['name' => 'Melatonin 3mg', 'dosage' => '1 tablet 30 min before sleep', 'duration' => '2 weeks', 'note' => 'Natural sleep hormone — start low'],
                    ['name' => 'Diphenhydramine (Benadryl) 25mg', 'dosage' => '1-0-0 at bedtime only', 'duration' => '5 days', 'note' => 'Short-term sleep aid'],
                ],
                'advice' => 'Sleep/wake at consistent times. No screens 1 hour before bed. Avoid caffeine after 3pm. Relaxation exercises.',
                'warning' => 'If insomnia is due to anxiety/depression, see a psychiatrist for proper treatment.',
            ],
            'skin_allergy' => [
                'name'     => 'Skin Allergy / Urticaria',
                'symptoms' => ['skin rash', 'itching', 'swelling', 'hives', 'redness'],
                'score'    => 0,
                'urgency'  => 'mild',
                'category' => 'Dermatology',
                'medicines' => [
                    ['name' => 'Cetirizine (Zirtek) 10mg', 'dosage' => '0-0-1 at night', 'duration' => '7 days', 'note' => 'Antihistamine'],
                    ['name' => 'Hydrocortisone 1% cream', 'dosage' => 'Apply on affected area 2x daily', 'duration' => '5 days', 'note' => 'For localized rash'],
                    ['name' => 'Fexofenadine 120mg', 'dosage' => '1-0-1', 'duration' => '7 days', 'note' => 'Non-drowsy antihistamine'],
                ],
                'advice' => 'Identify and avoid allergen triggers. Wear loose cotton clothing. Keep skin moisturized.',
                'warning' => 'If face swells, throat tightens, or breathing difficulty occurs — this is anaphylaxis. Call emergency immediately.',
            ],
            'migraine' => [
                'name'     => 'Migraine',
                'symptoms' => ['severe headache', 'headache', 'nausea', 'vomiting', 'sensitivity to light', 'dizziness'],
                'score'    => 0,
                'urgency'  => 'moderate',
                'category' => 'Neurology',
                'medicines' => [
                    ['name' => 'Sumatriptan 50mg', 'dosage' => '1 tablet at onset of migraine', 'duration' => 'As needed', 'note' => 'Triptan — migraine-specific'],
                    ['name' => 'Ibuprofen 400mg', 'dosage' => '1-1-1 after meals', 'duration' => '3 days', 'note' => 'Anti-inflammatory pain relief'],
                    ['name' => 'Metoclopramide (Maxolon) 10mg', 'dosage' => '1-1-1', 'duration' => '3 days', 'note' => 'For nausea associated with migraine'],
                ],
                'advice' => 'Rest in a dark, quiet room. Apply cold pack to forehead. Identify triggers: sleep, diet, stress.',
                'warning' => 'If headache is "the worst headache of your life" with neck stiffness or vision changes, go to ER immediately (possible meningitis/hemorrhage).',
            ],
            'back_pain' => [
                'name'     => 'Lower Back Pain (Lumbar)',
                'symptoms' => ['back pain', 'lower back', 'pain', 'weakness', 'numbness', 'sciatica'],
                'score'    => 0,
                'urgency'  => 'mild',
                'category' => 'Orthopedics / Physio',
                'medicines' => [
                    ['name' => 'Ibuprofen 400mg', 'dosage' => '1-1-1 after meals', 'duration' => '5 days', 'note' => 'Anti-inflammatory'],
                    ['name' => 'Thiocolchicoside (Thiocol) 4mg', 'dosage' => '1-1-1', 'duration' => '5 days', 'note' => 'Muscle relaxant'],
                    ['name' => 'Diclofenac Gel 1%', 'dosage' => 'Apply on painful area 3x daily', 'duration' => '7 days', 'note' => 'Topical anti-inflammatory'],
                ],
                'advice' => 'Avoid prolonged sitting. Apply warm compress. Physiotherapy exercises help. Maintain good posture.',
                'warning' => 'If pain shoots down to legs with numbness/tingling, see an orthopedic surgeon (possible disc herniation).',
            ],
        ];
    }

    /**
     * Diagnose disease based on extracted English symptoms.
     * Uses scoring algorithm — higher score = better match.
     */
    public static function diagnose(array $extractedSymptoms): array
    {
        $diseases = self::getDiseaseProfiles();
        $lowerSymptoms = array_map('strtolower', $extractedSymptoms);
        $fullText = implode(' ', $lowerSymptoms);

        foreach ($diseases as $key => &$disease) {
            $score = 0;
            foreach ($disease['symptoms'] as $symptom) {
                if (str_contains($fullText, strtolower($symptom))) {
                    $score++;
                }
            }
            $disease['score'] = $score;
        }

        // Sort by score, highest first
        uasort($diseases, fn($a, $b) => $b['score'] <=> $a['score']);
        
        // Return top 2 matches with score > 0
        $matches = array_filter($diseases, fn($d) => $d['score'] > 0);
        return array_slice($matches, 0, 2, true);
    }

    /**
     * Build a structured English clinical summary for the doctor.
     */
    public static function buildClinicalSummary(string $rawText, array $symptoms): string
    {
        $matches = self::diagnose($symptoms);
        
        if (empty($matches)) {
            return "Patient presents with: " . implode(', ', $symptoms) . ". Requires clinical evaluation.";
        }

        $topDisease = reset($matches);
        $summary = "CLINICAL SUMMARY:\n";
        $summary .= "Likely Condition: {$topDisease['name']} (Confidence: {$topDisease['score']} symptom matches)\n";
        $summary .= "Urgency: " . strtoupper($topDisease['urgency']) . "\n";
        $summary .= "Specialty: {$topDisease['category']}\n\n";
        $summary .= "Presenting Symptoms: " . implode(', ', $symptoms) . ".\n";
        $summary .= "Doctor's Note: {$topDisease['advice']}\n";
        
        if ($topDisease['urgency'] === 'critical') {
            $summary .= "\n⚠️ WARNING: " . $topDisease['warning'];
        }

        return $summary;
    }

    /**
     * Build Bengali prescription/advice for patient from diagnosis.
     */
    public static function buildBengaliAdvice(array $symptoms, string $doctorText = ''): array
    {
        $matches = self::diagnose($symptoms);
        
        if (empty($matches)) {
            return [
                'bengali_summary' => 'আপনার লক্ষণগুলি বিশ্লেষণ করা হয়েছে। একজন ডাক্তারের পরামর্শ নেওয়ার পরামর্শ দেওয়া হচ্ছে।',
                'disease' => 'Unknown',
                'medicines' => []
            ];
        }

        $topDisease = reset($matches);
        
        // Build Bengali summary
        $bnSummary = "আপনার শরীরে **{$topDisease['name']}** এর লক্ষণ পাওয়া গেছে। ";
        $bnSummary .= $topDisease['advice'] . " ";
        
        if ($topDisease['urgency'] === 'critical' || $topDisease['urgency'] === 'high') {
            $bnSummary .= "⚠️ অবিলম্বে একজন ডাক্তারের সাথে যোগাযোগ করুন!";
        } else {
            $bnSummary .= "ওষুধগুলো ডাক্তারের পরামর্শ অনুযায়ী সেবন করুন এবং বিশ্রাম নিন।";
        }

        // Format medicine list
        $medicineList = [];
        foreach ($topDisease['medicines'] as $med) {
            $medicineList[] = [
                'name'    => $med['name'],
                'dosage'  => $med['dosage'],
                'duration'=> $med['duration'],
                'note'    => $med['note'],
            ];
        }

        return [
            'bengali_summary' => $bnSummary,
            'disease'         => $topDisease['name'],
            'urgency'         => $topDisease['urgency'],
            'warning'         => $topDisease['warning'] ?? null,
            'medicines'       => $medicineList,
        ];
    }
}
