<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AI Doctor Consultation</title>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 400px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #f5576c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .alert {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .alert-danger {
            background: #fee;
            color: red;
        }

        .alert-success {
            background: #efe;
            color: green;
        }
    </style>
</head>

<body>

<div class="card">
    <h2>✨ Register</h2>

    <!-- ✅ SESSION MESSAGES -->
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
        </div>
    <?php unset($_SESSION['error']); } ?>

    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; ?>
        </div>
    <?php unset($_SESSION['success']); } ?>

    <form action="auth.php" method="POST">

        <input type="hidden" name="action" value="register">

        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>

        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <input type="number" name="age" placeholder="Age" required>

        <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
        </select>

        <button type="submit" class="btn">Register</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>