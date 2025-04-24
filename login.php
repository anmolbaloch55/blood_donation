<?php
session_start();
include("db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($row['role'] == 'donor') {
                header("Location: donor_dashboard.php");
            } elseif ($row['role'] == 'recipient') {
                header("Location: recipient_dashboard.php");
            } else {
                $error = "Invalid user role!";
            }
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML Part -->
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background-color: #fff0f0;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 80px;
        }
        h2 {
            color: #b30000;
        }
        form {
            background-color: white;
            display: inline-block;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
        }
        input {
            margin: 10px;
            padding: 10px;
            width: 250px;
        }
        button {
            padding: 10px 25px;
            background-color: #b30000;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #800000;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Login - Blood Donation System</h2>
    
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Enter your email" required><br>
        <input type="password" name="password" placeholder="Enter your password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
