<?php
session_start();

// 🔥 SHOW ERRORS (important for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$user_id = (int)$_SESSION['user_id'];

// DB Connection
$conn = new mysqli("localhost", "root", "040301@Shilki1", "doctor_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default values
$name = $age = $gender = $symptoms = $description = "";
$diagnosis = "No diagnosis yet";
$confidence = 0.0;
$severity = "Low";
$precautions = "";
$treatment = "";
$doctor_advice = "";
$top_3 = "[]";
$prediction = [];
$consultation_id = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $age = (int)$_POST['age'];
    $gender = ucfirst(strtolower($_POST['gender']));
    $symptoms = $_POST['symptoms'];
    $description = $_POST['description'] ?? '';

    $symptomArray = array_map('trim', explode(',', $symptoms));

    // ✅ FIXED API URL
    $ch = curl_init('http://localhost/doctor_consultation-main/api_predict_disease.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['symptoms' => $symptomArray]));

    $response = curl_exec($ch);

    if ($response === false) {
        die("CURL ERROR: " . curl_error($ch));
    }

    curl_close($ch);

    $prediction = json_decode($response, true);

    if (!$prediction) {
        die("API not returning valid data");
    }

    // Extract data
    $diagnosis = $prediction['primary_disease']['name'] ?? 'General Health Check-up Recommended';
    $confidence = isset($prediction['primary_disease']['confidence']) 
        ? (float)$prediction['primary_disease']['confidence'] : 0.0;
    $severity = ucfirst(strtolower($prediction['primary_disease']['severity'] ?? 'Low'));
    $treatment = $prediction['primary_disease']['treatment'] ?? 'Consult a healthcare professional';
    $precautions = $prediction['primary_disease']['precautions'] ?? 'Follow general health guidelines';

    $top_3 = json_encode(array_map(function($d) {
        return $d['name'] ?? '';
    }, $prediction['top_3_diseases'] ?? []));

    $doctor_advice = "Based on your symptoms: $diagnosis. $treatment";

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO consultations 
    (user_id, name, age, gender, symptoms, diagnosis, confidence_score, top_3_diseases, severity_level, precautions, doctor_advice) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isisssdssss",
        $user_id,
        $name,
        $age,
        $gender,
        $symptoms,
        $diagnosis,
        $confidence,
        $top_3,
        $severity,
        $precautions,
        $doctor_advice
    );

    if (!$stmt->execute()) {
        die("Insert Error: " . $stmt->error);
    }

    $consultation_id = $stmt->insert_id;
    $stmt->close();
}

$conn->close();

// Severity color class
$class = "severity-low";
if ($severity == "Medium") $class = "severity-medium";
if ($severity == "High") $class = "severity-high";
if ($severity == "Critical") $class = "severity-critical";
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Diagnosis Result</title>
    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-align: center;
            padding: 50px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 15px;
        }
        .bar {
            background: #ddd;
            border-radius: 20px;
            overflow: hidden;
        }
        .progress {
            height: 20px;
            background: #00ff88;
        }
        .severity-low { color: lightgreen; }
        .severity-medium { color: yellow; }
        .severity-high { color: orange; }
        .severity-critical { color: red; }
    </style>
</head>

<body>

<div class="card">
    <h1>🩺 AI Diagnosis Result</h1>

    <h2><?php echo $diagnosis; ?></h2>

    <h3>Confidence:</h3>
    <div class="bar">
        <div class="progress" style="width: <?php echo ($confidence * 100); ?>%"></div>
    </div>
    <p><?php echo round($confidence * 100); ?>%</p>

    <h3>Severity:</h3>
    <p class="<?php echo $class; ?>"><?php echo $severity; ?></p>

    <h3>Precautions:</h3>
    <p><?php echo $precautions; ?></p>

    <h3>Doctor Advice:</h3>
    <p><?php echo $doctor_advice; ?></p>

    <h3>Top 3 Diseases:</h3>
    <ul>
        <?php 
        $diseases = json_decode($top_3, true);
        if ($diseases) {
            foreach ($diseases as $d) {
                echo "<li>$d</li>";
            }
        }
        ?>
    </ul>

</div>

</body>
</html>