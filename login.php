<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Doctor Consultation</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .alert-danger {
            background: #fee;
            color: red;
        }

        .alert-success {
            background: #efe;
            color: green;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="card">

        <div class="header">
            <h1>🧠 Welcome Back</h1>
            <p>Your AI Health Assistant</p>
        </div>

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

        <!-- ✅ LOGIN FORM -->
        <form action="auth.php" method="POST">

            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="footer">
            Don't have an account? <a href="register.php">Register</a>
        </div>

    </div>
</div>

</body>
</html>