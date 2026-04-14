<?php
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

// Fetch consultation records
$sql = "SELECT id, name, symptoms, diagnosis FROM consultations ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation History</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(to right, #f3e5f5, #e1f5fe);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            color: #4a148c;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            margin-bottom: 30px;
            font-size: 32px;
            background: linear-gradient(to right, #8e24aa, #ce93d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 3s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { text-shadow: 0 0 5px #ba68c8; }
            50% { text-shadow: 0 0 12px #e1bee7; }
        }

        table {
            width: 95%;
            max-width: 1000px;
            border-collapse: separate;
            border-spacing: 0 15px;
        }

        th {
            background-color: #ab47bc;
            color: white;
            padding: 14px;
            border-radius: 10px 10px 0 0;
            font-size: 16px;
        }

        td {
            background: #ffffff;
            padding: 14px;
            text-align: center;
            border-bottom: 2px solid #f3e5f5;
            border-top: 2px solid transparent;
            transition: all 0.3s ease;
        }

        tr:hover td {
            background-color: #fce4ec;
            transform: scale(1.01);
        }

        a {
            background-color: #7b1fa2;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        a:hover {
            background-color: #4a148c;
        }

        p {
            margin-top: 50px;
            font-size: 18px;
            color: #777;
        }

        @media(max-width: 768px) {
            th, td { font-size: 14px; padding: 10px; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>

<h1>Consultation History</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Patient Name</th>
            <th>Symptoms</th>
            <th>Diagnosis</th>
            <th>PDF</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['symptoms']) ?></td>
                <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                <td>
                    <a href="download_pdf.php?id=<?= urlencode($row['id']) ?>">Download</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No consultation records found.</p>
<?php endif; ?>

<?php $conn->close(); ?>

</body>
</html>
