<?php
// Enhanced Professional PDF Generation System

require_once('tcpdf/tcpdf.php');

$host = "localhost";
$username = "root";
$password = "040301@Shilki1";
$database = "doctor_db";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$consultation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch consultation details
$sql = "SELECT c.*, u.first_name, u.last_name, u.email, u.phone 
        FROM consultations c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consultation_id);
$stmt->execute();
$result = $stmt->get_result();
$consultation = $result->fetch_assoc();

if (!$consultation) {
    die("❌ Consultation not found.");
}

// Create TCPDF object
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(30, 58, 138);
        $this->Cell(0, 15, '🏥 AI-Powered Medical Report', 0, false, 'C', false, '', 0, false);
        $this->Ln(8);
        
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Digital Health Consultation Record', 0, false, 'C', false, '', 0, false);
        $this->Ln(5);
        
        $this->SetDrawColor(51, 98, 157);
        $this->SetLineWidth(0.5);
        $this->Line(20, $this->GetY(), 190, $this->GetY());
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages() . ' | Generated: ' . date('Y-m-d H:i:s'), 0, false, 'C', false, '', 0, false);
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(15, 30, 15);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// 1. PATIENT INFORMATION
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(30, 58, 138);
$pdf->Cell(0, 8, '1. PATIENT INFORMATION', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);

$patient_info = array(
    'Patient Name' => htmlspecialchars($consultation['first_name'] . ' ' . $consultation['last_name']),
    'Age' => $consultation['age'] . ' years',
    'Gender' => $consultation['gender'],
    'Contact Email' => htmlspecialchars($consultation['email']) ?? 'N/A',
    'Phone' => htmlspecialchars($consultation['phone']) ?? 'N/A',
    'Consultation Date' => date('M d, Y H:i', strtotime($consultation['created_at']))
);

$pdf->SetFillColor(240, 245, 250);
foreach ($patient_info as $key => $value) {
    $pdf->Cell(60, 7, $key . ':', 0, 0, 'L', true);
    $pdf->Cell(0, 7, $value, 0, 1, 'L', true);
}

$pdf->Ln(5);

// 2. SYMPTOMS REPORTED
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(30, 58, 138);
$pdf->Cell(0, 8, '2. REPORTED SYMPTOMS', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);

$symptoms = explode(", ", $consultation['symptoms']);
$pdf->SetFillColor(240, 245, 250);
$pdf->Cell(10, 7, '', 0, 0, 'L', true);
$pdf->MultiCell(0, 7, implode(', ', $symptoms), 0, 'J', true);

$pdf->Ln(5);

// 3. MEDICAL ASSESSMENT
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(30, 58, 138);
$pdf->Cell(0, 8, '3. PRELIMINARY DIAGNOSIS', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(220, 50, 50);
$pdf->SetFillColor(255, 240, 240);
$pdf->MultiCell(0, 7, htmlspecialchars($consultation['diagnosis']), 0, 'L', true);

$pdf->Ln(3);

// 4. CONFIDENCE & TOP 3 DISEASES
if (!empty($consultation['confidence_score'])) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 6, 'Confidence Level:', 0, 0);
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(30, 150, 50);
    $pdf->Cell(0, 6, $consultation['confidence_score'] . '%', 0, 1);
}

if (!empty($consultation['top_3_diseases'])) {
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, 'Top 3 Possible Conditions:', 0, 1);
    
    $pdf->SetFont('helvetica', '', 9);
    $top_diseases = json_decode($consultation['top_3_diseases'], true) ?? array();
    $counter = 1;
    foreach ($top_diseases as $disease) {
        $pdf->Cell(5, 5, $counter . '.', 0, 0);
        $pdf->Cell(0, 5, $disease, 0, 1);
        $counter++;
    }
}

$pdf->Ln(5);

// 5. SEVERITY ASSESSMENT
if (!empty($consultation['severity_level'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 8, '4. SEVERITY ASSESSMENT', 0, 1, 'L');
    
    $severity_color = array(
        'Low' => array(76, 175, 80),
        'Medium' => array(255, 152, 0),
        'High' => array(244, 67, 54),
        'Critical' => array(156, 39, 176)
    );
    
    $severity = $consultation['severity_level'];
    $colors = $severity_color[$severity] ?? array(0, 0, 0);
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($colors[0], $colors[1], $colors[2]);
    $pdf->Cell(0, 8, '● ' . $severity, 0, 1);
}

$pdf->Ln(5);

// 6. PRECAUTIONS & RECOMMENDATIONS
if (!empty($consultation['precautions'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 8, '5. RECOMMENDED PRECAUTIONS', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(240, 245, 250);
    $pdf->MultiCell(0, 6, htmlspecialchars($consultation['precautions']), 0, 'J', true);
}

$pdf->Ln(5);

// 7. DOCTOR'S ADVICE
if (!empty($consultation['doctor_advice'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 8, '6. DOCTOR ADVICE', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(240, 245, 250);
    $pdf->MultiCell(0, 6, htmlspecialchars($consultation['doctor_advice']), 0, 'J', true);
}

$pdf->Ln(10);

// 8. IMPORTANT DISCLAIMER
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(150, 0, 0);
$pdf->SetFillColor(255, 240, 240);
$pdf->MultiCell(0, 5, 
    "⚠️ DISCLAIMER: This report is AI-generated for informational purposes only and is not a substitute for professional medical advice. " .
    "This diagnosis should not be used for self-treatment. Please consult with a qualified healthcare professional for proper diagnosis and treatment.",
    0, 'J', true
);

// Output PDF
$filename = 'Consultation_Report_' . $consultation_id . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'D'); // 'D' forces download

$conn->close();
?>
