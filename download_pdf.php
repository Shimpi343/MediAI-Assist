<?php
// Include TCPDF library (ensure TCPDF is installed)
require_once('tcpdf/tcpdf.php');



// Database connection
$host = "localhost";
$username = "root";
$password = "040301@Shilki1";
$database = "doctor_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the 'id' from the URL
$consultation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch consultation details
$sql = "SELECT * FROM consultations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consultation_id);
$stmt->execute();
$result = $stmt->get_result();
$consultation = $result->fetch_assoc();

if (!$consultation) {
    die("Consultation not found.");
}

// Create new TCPDF instance
$pdf = new TCPDF();

// Add a page
$pdf->AddPage();

// Set the title and content for the PDF
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, 'Consultation Details', '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(10);

// Write consultation details to the PDF
$pdf->Write(0, "Name: " . $consultation['name'], '', 0, 'L', true, 0, false, false, 0);
$pdf->Write(0, "Age: " . $consultation['age'], '', 0, 'L', true, 0, false, false, 0);
$pdf->Write(0, "Gender: " . $consultation['gender'], '', 0, 'L', true, 0, false, false, 0);
$pdf->Write(0, "Symptoms: " . $consultation['symptoms'], '', 0, 'L', true, 0, false, false, 0);
$pdf->Write(0, "Diagnosis: " . $consultation['diagnosis'], '', 0, 'L', true, 0, false, false, 0);

// Output the PDF to browser
$pdf->Output('consultation_' . $consultation_id . '.pdf', 'D'); // 'D' will force download
exit;
?>

