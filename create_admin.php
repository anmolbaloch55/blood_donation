<?php
include('db.php');

// Admin data
$name = "Anmol";
$email = "anmolajmal24@cs.com";
$password = password_hash("anmol123", PASSWORD_DEFAULT);
$blood_group = "A+";
$city = "Multan";
$phone = "03123456789";
$role = "admin";

// Check if admin already exists
$check = $conn->prepare("SELECT * FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "⚠️ Admin already exists!";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, blood_group, city, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $password, $blood_group, $city, $phone, $role);

    if ($stmt->execute()) {
        echo "✅ Admin created successfully! Now login at: <a href='login.php'>Login</a>";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
