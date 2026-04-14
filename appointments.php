<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$host = "localhost";
$user = "root";
$password = "040301@Shilki1";
$dbname = "doctor_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book') {
    $doctor_id = (int)$_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $notes = $_POST['notes'];
    
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, notes, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iiss", $user_id, $doctor_id, $appointment_datetime, $notes);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment booked successfully!";
    } else {
        $_SESSION['error'] = "Failed to book appointment.";
    }
    $stmt->close();
}

// Fetch all doctors
$doctors = $conn->query("SELECT * FROM doctors WHERE available_status = TRUE");

// Fetch user's appointments
$appointments = $conn->query("
    SELECT a.*, d.name as doctor_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = $user_id
    ORDER BY a.appointment_date DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📅 Book Appointment</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .doctor-option {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .doctor-option:hover {
            border-color: #667eea;
            background: #f0f3ff;
        }

        .doctor-option input[type="radio"] {
            width: auto;
            margin-right: 10px;
        }

        .doctor-info {
            display: inline-block;
        }

        .doctor-info h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .doctor-info p {
            color: #888;
            font-size: 12px;
        }

        .appointment-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }

        .appointment-item h4 {
            color: #333;
            margin-bottom: 8px;
        }

        .appointment-info {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 8px;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status.completed {
            background: #bfe5d0;
            color: #0c5460;
        }

        .no-appointments {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Book an Appointment</h1>
            <p>Schedule a consultation with our doctors</p>
        </div>

        <div class="grid">
            <!-- Booking Form -->
            <div class="card">
                <h2>New Appointment</h2>

                <?php
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <form method="POST">
                    <input type="hidden" name="action" value="book">

                    <div class="form-group">
                        <label>Select Doctor</label>
                        <?php while ($doctor = $doctors->fetch_assoc()): ?>
                            <div class="doctor-option">
                                <input 
                                    type="radio" 
                                    name="doctor_id" 
                                    value="<?php echo $doctor['id']; ?>" 
                                    required
                                >
                                <div class="doctor-info">
                                    <h4><?php echo htmlspecialchars($doctor['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($doctor['specialization']); ?> | ⭐ <?php echo $doctor['rating']; ?>/5</p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="form-group">
                        <label>📅 Appointment Date</label>
                        <input 
                            type="date" 
                            name="appointment_date" 
                            required
                            min="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>⏰ Preferred Time</label>
                        <select name="appointment_time" required>
                            <option value="">Select Time</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                            <option value="17:00">05:00 PM</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>📝 Notes (Optional)</label>
                        <textarea 
                            name="notes" 
                            placeholder="Tell the doctor about your symptoms or concerns..."
                        ></textarea>
                    </div>

                    <button type="submit" class="btn">✅ Book Appointment</button>
                </form>
            </div>

            <!-- Appointments History -->
            <div class="card">
                <h2>Your Appointments</h2>

                <?php if ($appointments->num_rows > 0): ?>
                    <?php while ($appointment = $appointments->fetch_assoc()): ?>
                        <div class="appointment-item">
                            <h4>🩺 <?php echo htmlspecialchars($appointment['doctor_name']); ?></h4>
                            <div class="appointment-info">
                                <p><strong>📋 Specialization:</strong> <?php echo htmlspecialchars($appointment['specialization']); ?></p>
                                <p><strong>📅 Date & Time:</strong> <?php echo date('M d, Y H:i', strtotime($appointment['appointment_date'])); ?></p>
                                <?php if ($appointment['notes']): ?>
                                    <p><strong>📝 Notes:</strong> <?php echo htmlspecialchars($appointment['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="status <?php echo strtolower($appointment['status']); ?>">
                                <?php echo $appointment['status']; ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-appointments">
                        <p>No appointments booked yet.</p>
                        <p>Book your first appointment using the form on the left.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
