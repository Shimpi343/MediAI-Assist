<?php
header('Content-Type: application/json');
error_reporting(0);

// Disease prediction knowledge base with confidence scoring
$disease_database = array(
    array(
        'name' => 'COVID-19',
        'symptoms' => ['Fever', 'Cough', 'Shortness of Breath', 'Loss of Taste', 'Loss of Smell'],
        'severity' => 'High',
        'confidence' => 0,
        'precautions' => 'Isolate yourself, wear mask, get vaccinated, practice hand hygiene',
        'treatment' => 'Antiviral medications, oxygen therapy if needed, rest'
    ),
    array(
        'name' => 'Pneumonia',
        'symptoms' => ['Fever', 'Cough', 'Shortness of Breath', 'Chest Pain'],
        'severity' => 'High',
        'confidence' => 0,
        'precautions' => 'Get pneumonia vaccine, avoid smoke, stay hydrated',
        'treatment' => 'Antibiotics, oxygen support, fluids'
    ),
    array(
        'name' => 'Migraine',
        'symptoms' => ['Headache', 'Fatigue', 'Dizziness', 'Nausea', 'Vomiting'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Avoid triggers, manage stress, maintain hydration',
        'treatment' => 'Pain relief medication, rest in dark room, compress therapy'
    ),
    array(
        'name' => 'Common Cold',
        'symptoms' => ['Runny Nose', 'Sore Throat', 'Cough', 'Sneezing', 'Fatigue'],
        'severity' => 'Low',
        'confidence' => 0,
        'precautions' => 'Wash hands frequently, avoid close contact, use tissues',
        'treatment' => 'Rest, fluids, vitamin C, over-the-counter medication'
    ),
    array(
        'name' => 'Flu',
        'symptoms' => ['Fever', 'Body Ache', 'Fatigue', 'Cough', 'Sore Throat'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Get flu vaccine, avoid close contact, sanitize regularly',
        'treatment' => 'Antiviral drugs, rest, fluids, fever management'
    ),
    array(
        'name' => 'Food Poisoning',
        'symptoms' => ['Nausea', 'Vomiting', 'Diarrhea', 'Stomach Pain', 'Fever'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Eat fresh food, maintain hygiene, cook properly',
        'treatment' => 'Electrolyte replacement, light diet, anti-diarrheal medication'
    ),
    array(
        'name' => 'Allergic Reaction',
        'symptoms' => ['Skin Rash', 'Swelling', 'Itching', 'Hives'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Identify and avoid allergens, carry antihistamines',
        'treatment' => 'Antihistamines, corticosteroids, moisturizers'
    ),
    array(
        'name' => 'Asthma',
        'symptoms' => ['Shortness of Breath', 'Wheezing', 'Cough', 'Chest Tightness'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Avoid triggers, keep inhaler handy, indoor air quality',
        'treatment' => 'Inhalers (rescue & maintenance), nebulizers, avoidance'
    ),
    array(
        'name' => 'Diabetes',
        'symptoms' => ['Frequent Urination', 'Increased Thirst', 'Fatigue', 'Blurred Vision', 'Weight Loss'],
        'severity' => 'High',
        'confidence' => 0,
        'precautions' => 'Regular check-ups, monitor blood sugar, balanced diet, exercise',
        'treatment' => 'Insulin/oral meds, dietary management, lifestyle changes'
    ),
    array(
        'name' => 'Hypertension',
        'symptoms' => ['Headache', 'Dizziness', 'Fatigue', 'Chest Pain', 'Shortness of Breath'],
        'severity' => 'High',
        'confidence' => 0,
        'precautions' => 'Low sodium diet, regular exercise, stress management',
        'treatment' => 'ACE inhibitors, beta-blockers, lifestyle modifications'
    ),
    array(
        'name' => 'Arthritis',
        'symptoms' => ['Joint Pain', 'Swelling', 'Stiffness', 'Fatigue'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Physical therapy, weight management, gentle exercise',
        'treatment' => 'NSAIDs, corticosteroids, physical therapy'
    ),
    array(
        'name' => 'Strep Throat',
        'symptoms' => ['Sore Throat', 'Fever', 'Swollen Lymph Nodes', 'Difficulty Swallowing'],
        'severity' => 'Medium',
        'confidence' => 0,
        'precautions' => 'Avoid close contact, sanitize hands, no sharing utensils',
        'treatment' => 'Antibiotics, throat lozenges, warm fluids'
    ),
    array(
        'name' => 'Conjunctivitis',
        'symptoms' => ['Eye Redness', 'Itching', 'Discharge', 'Watery Eyes'],
        'severity' => 'Low',
        'confidence' => 0,
        'precautions' => 'Don\'t touch eyes, wash hands, use clean towels',
        'treatment' => 'Eye drops, warm compress, avoid contact lenses'
    ),
);

// Function to calculate confidence score
function calculateConfidence($userSymptoms, $diseaseSymptoms) {
    $matchCount = 0;
    foreach ($userSymptoms as $symptom) {
        foreach ($diseaseSymptoms as $dbSymptom) {
            if (stripos($symptom, $dbSymptom) !== false || stripos($dbSymptom, $symptom) !== false) {
                $matchCount++;
            }
        }
    }
    return ($matchCount / count($diseaseSymptoms)) * 100;
}

// Get prediction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $userSymptoms = isset($data['symptoms']) ? $data['symptoms'] : [];
    
    if (empty($userSymptoms)) {
        echo json_encode(['error' => 'No symptoms provided']);
        exit;
    }
    
    // Calculate confidence for each disease
    foreach ($disease_database as &$disease) {
        $disease['confidence'] = calculateConfidence($userSymptoms, $disease['symptoms']);
    }
    
    // Sort by confidence
    usort($disease_database, function($a, $b) {
        return $b['confidence'] - $a['confidence'];
    });
    
    // Get top 3
    $topDiseases = array_slice($disease_database, 0, 3);
    
    // Filter valid predictions (confidence > 20%)
    $validPredictions = array_filter($topDiseases, function($disease) {
        return $disease['confidence'] > 20;
    });
    
    // Prepare response
    $response = [
        'primary_disease' => $validPredictions ? array_values($validPredictions)[0] : [
            'name' => 'General Health Check-up Recommended',
            'severity' => 'Low',
            'confidence' => 0,
            'treatment' => 'Consult a doctor for thorough examination'
        ],
        'top_3_diseases' => array_slice(array_values($validPredictions), 0, 3),
        'all_results' => $topDiseases,
        'input_symptoms' => $userSymptoms
    ];
    
    echo json_encode($response);
}
?>
