<?php
header('Content-Type: application/json');

// Chatbot knowledge base
$chatbot_responses = array(
    'greeting' => array(
        'Hi', 'Hello', 'Hey',
    ),
    'health_questions' => array(
        'What is diabetes?' => 'Diabetes is a condition where your body cannot properly regulate blood sugar levels. It can lead to serious health complications. Please consult a doctor for diagnosis.',
        'What is COVID-19?' => 'COVID-19 is a respiratory illness caused by the SARS-CoV-2 virus. Symptoms include fever, cough, and shortness of breath. Vaccination is recommended.',
        'How to prevent flu?' => 'To prevent flu: Get vaccinated annually, wash hands frequently, avoid touching face, cover mouth when coughing, stay home when sick.',
        'What is asthma?' => 'Asthma is a chronic respiratory condition causing inflammation of airways. Triggers include allergies, exercise, cold air. Use inhalers as prescribed.',
        'How to manage stress?' => 'Stress management: Practice meditation, regular exercise, adequate sleep, healthy diet, social support. Seek professional help if needed.',
        'How to maintain healthy weight?' => 'Maintain healthy weight: Balanced diet with whole grains, lean protein, fruits, vegetables. Regular exercise 150 min/week. Avoid processed food.',
        'What about depression?' => 'Depression is a serious mental health condition. Symptoms include persistent sadness, loss of interest, sleep changes. Please consult a mental health professional.',
        'How to prevent heart disease?' => 'Prevent heart disease: Regular exercise, heart-healthy diet (low salt/fat), manage stress, quit smoking, maintain healthy weight, regular check-ups.',
    ),
    'symptoms' => array(
        'I have fever' => 'Fever can indicate various infections (flu, cold, COVID-19, etc.). Stay hydrated, rest, and monitor temperature. Use paracetamol if needed. Consult a doctor if it persists > 3 days.',
        'I have cough' => 'Cough can be caused by cold, flu, asthma, or allergies. Drink warm fluids, use cough drops, avoid irritants. See a doctor if severe or persists > 2 weeks.',
        'I have headache' => 'Headache causes: stress, dehydration, fatigue, migraines. Rest in quiet dark room, drink water, take pain relief. Severe/frequent headaches need medical evaluation.',
        'I have stomach pain' => 'Stomach pain can result from food poisoning, indigestion, IBS, etc. Avoid heavy food, drink water, use antacids. See doctor if severe or persistent.',
        'I am tired' => 'Fatigue causes: lack of sleep, stress, anemia, thyroid issues. Ensure 7-8 hours sleep, exercise, balanced diet. Persistent fatigue needs medical check-up.',
    ),
    'appointment' => array(
        'Book appointment' => 'To book an appointment: Go to Appointments section, select date/time, choose a doctor, and confirm. You\'ll receive confirmation via email.',
        'How to reschedule?' => 'To reschedule: Log in to your account, go to Appointments, select the appointment, click Reschedule, choose new date/time.',
        'Cancel appointment' => 'To cancel: Go to Appointments, select appointment, click Cancel. Cancellation must be done 24 hours before appointment.',
    ),
    'fallback' => 'I understand your question. For more detailed information, please: 1) Start a consultation, 2) Chat with a doctor, or 3) Visit a healthcare facility.',
);

// Function to find best matching response
function findBestResponse($user_message) {
    global $chatbot_responses;
    
    $message_lower = strtolower($user_message);
    
    // Check greeting
    foreach ($chatbot_responses['greeting'] as $greeting) {
        if (stripos($message_lower, $greeting) !== false) {
            return "Hello! 👋 Welcome to AI Doctor Consultation. How can I help you today? You can:\n- Describe symptoms for diagnosis\n- Ask health questions\n- Book appointments\n- Chat about health concerns";
        }
    }
    
    // Check health questions
    foreach ($chatbot_responses['health_questions'] as $question => $answer) {
        if (stripos($message_lower, strtolower($question)) !== false || 
            stripos(strtolower($question), $message_lower) !== false) {
            return $answer;
        }
    }
    
    // Check symptoms
    foreach ($chatbot_responses['symptoms'] as $symptom => $answer) {
        if (stripos($message_lower, strtolower($symptom)) !== false) {
            return $answer;
        }
    }
    
    // Check appointment
    foreach ($chatbot_responses['appointment'] as $apt_question => $apt_answer) {
        if (stripos($message_lower, strtolower($apt_question)) !== false) {
            return $apt_answer;
        }
    }
    
    return $chatbot_responses['fallback'];
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_message = isset($data['message']) ? trim($data['message']) : '';
    
    if (empty($user_message)) {
        echo json_encode(['error' => 'Empty message']);
        exit;
    }
    
    // Get bot response
    $bot_response = findBestResponse($user_message);
    
    // Save to chat history (optional, needs session)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['user_id'])) {
        $host = "localhost";
        $user = "root";
        $password = "040301@Shilki1";
        $dbname = "doctor_db";
        
        $conn = new mysqli($host, $user, $password, $dbname);
        if (!$conn->connect_error) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO chat_history (user_id, user_message, bot_response) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $user_message, $bot_response);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
    
    echo json_encode([
        'user_message' => htmlspecialchars($user_message),
        'bot_response' => $bot_response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
