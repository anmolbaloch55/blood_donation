<?php
session_start();
include("db.php");

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    die("Unauthorized Access");
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_group = $_POST['blood_group'];
    $city = $_POST['city'];
    $reason = $_POST['reason'];

    // File upload
    $fileName = "";
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $fileName = "uploads/" . time() . "_" . basename($_FILES["document"]["name"]);
        move_uploaded_file($_FILES["document"]["tmp_name"], $fileName);
    }

    $recipient_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO blood_requests (recipient_id, blood_group, city, reason, document_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $recipient_id, $blood_group, $city, $reason, $fileName);

    if ($stmt->execute()) {
        $success = "✅ Blood request submitted successfully!";
    } else {
        $error = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recipient Dashboard</title>
    <style>
        body { font-family: Arial; background-color: #f9f9f9; padding: 20px; }
        .container {
            max-width: 500px; margin: auto; background: #fff; padding: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2); border-radius: 10px;
        }
        h2 { text-align: center; color: #b30000; }
        input, select, textarea {
            width: 100%; padding: 10px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        input[type="submit"] {
            background: #b30000; color: white; font-weight: bold;
            cursor: pointer; border: none;
        }
        .msg { text-align: center; font-weight: bold; color: green; }
        .error { text-align: center; color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Blood Request Form</h2>

    <?php if ($success) echo "<div class='msg'>$success</div>"; ?>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <select name="blood_group" required>
            <option value="">Select Blood Group</option>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
        </select>
        <input type="text" name="city" placeholder="Your City" required>
        <textarea name="reason" placeholder="Why is blood needed?" required></textarea>
        <input type="file" name="document" required>
        <input type="submit" value="Submit Blood Request">
    </form>
</div>
</body>
</html>
