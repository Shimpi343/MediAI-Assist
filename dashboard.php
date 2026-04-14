<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$password = "040301@Shilki1";
$dbname = "doctor_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();

// Fetch statistics
$total_consultations = $conn->query("SELECT COUNT(*) as count FROM consultations WHERE user_id = $user_id")->fetch_assoc()['count'];
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE user_id = $user_id")->fetch_assoc()['count'];
$completed_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE user_id = $user_id AND status = 'Completed'")->fetch_assoc()['count'];

// Fetch recent consultations
$recent_consultations = $conn->query("SELECT * FROM consultations WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Get disease frequency for pie chart
$disease_data = $conn->query("
    SELECT diagnosis, COUNT(*) as count 
    FROM consultations 
    WHERE user_id = $user_id 
    GROUP BY diagnosis 
    ORDER BY count DESC 
    LIMIT 10
");

// Convert to JSON for charts
$diseases = [];
$disease_counts = [];
while ($row = $disease_data->fetch_assoc()) {
    $diseases[] = $row['diagnosis'];
    $disease_counts[] = $row['count'];
}

// Get health trend (consultations per week)
$health_trend = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM consultations 
    WHERE user_id = $user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$trend_dates = [];
$trend_counts = [];
while ($row = $health_trend->fetch_assoc()) {
    $trend_dates[] = $row['date'];
    $trend_counts[] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Doctor Consultation</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar h1 {
            color: white;
            font-size: 24px;
        }

        .navbar a, .navbar button {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .navbar a:hover, .navbar button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: slideUp 0.5s ease;
        }

        .stat-card h3 {
            color: #888;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-card .icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s ease;
        }

        .chart-card h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
        }

        .table-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.7s ease;
        }

        .table-card h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .btn {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #764ba2;
        }

        .btn-secondary {
            background: #f5576c;
        }

        .btn-secondary:hover {
            background: #d63447;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            table {
                font-size: 12px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <h1>🏥 Health Dashboard</h1>
        <div>
            <a href="consult_new.html">📝 New Consultation</a>
            <a href="chatbot.html">💬 Chat</a>
            <button onclick="location.href='auth.php?action=logout'">🚪 Logout</button>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="grid">
            <div class="stat-card">
                <div class="icon">📋</div>
                <h3>Total Consultations</h3>
                <div class="number"><?php echo $total_consultations; ?></div>
            </div>
            <div class="stat-card">
                <div class="icon">📅</div>
                <h3>Appointments</h3>
                <div class="number"><?php echo $total_appointments; ?></div>
            </div>
            <div class="stat-card">
                <div class="icon">✅</div>
                <h3>Completed</h3>
                <div class="number"><?php echo $completed_appointments; ?></div>
            </div>
            <div class="stat-card">
                <div class="icon">👤</div>
                <h3>User</h3>
                <div class="number"><?php echo htmlspecialchars($user_result['first_name'][0]); ?></div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Disease Distribution Pie Chart -->
            <div class="chart-card">
                <h3>📊 Disease Distribution</h3>
                <canvas id="diseaseChart"></canvas>
            </div>

            <!-- Health Trend Line Chart -->
            <div class="chart-card">
                <h3>📈 Health Trend (30 Days)</h3>
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Recent Consultations Table -->
        <div class="table-card">
            <h3>📋 Recent Consultations</h3>
            <?php if ($recent_consultations->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Symptoms</th>
                            <th>Diagnosis</th>
                            <th>Confidence</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($consultation = $recent_consultations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($consultation['created_at'])); ?></td>
                                <td><?php echo substr($consultation['symptoms'], 0, 30) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($consultation['diagnosis']); ?></td>
                                <td><?php echo isset($consultation['confidence_score']) ? $consultation['confidence_score'] . '%' : 'N/A'; ?></td>
                                <td>
                                    <a href="download_pdf.php?id=<?php echo $consultation['id']; ?>" class="btn">📥 PDF</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No consultations yet. <a href="consult.html">Start a consultation</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Disease Distribution Chart
        const diseaseCtx = document.getElementById('diseaseChart').getContext('2d');
        new Chart(diseaseCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($diseases); ?>,
                datasets: [{
                    data: <?php echo json_encode($disease_counts); ?>,
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe',
                        '#00f2fe',
                        '#43e97b',
                        '#fa709a',
                        '#fee140',
                        '#30cfd0'
                    ],
                    borderColor: 'white',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Health Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_dates); ?>,
                datasets: [{
                    label: 'Consultations',
                    data: <?php echo json_encode($trend_counts); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
