<?php
include('db.php');
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
  $blood_group = $_POST['blood_group'];
  $city = $_POST['city'];
  $phone = $_POST['phone'];
  $role = $_POST['role'];

  if ($role === 'admin') {
    $error = "Admin registration not allowed.";
  } else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, blood_group, city, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $password, $blood_group, $city, $phone, $role);

    if ($stmt->execute()) {
      $success = "✅ Registration successful! <a href='login.php'>Login here</a>";
    } else {
      $error = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fff0f0;
    }
    .container {
      width: 400px;
      margin: 40px auto;
      padding: 30px;
      background-color: #fff;
      border: 2px solid #e74c3c;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(231, 76, 60, 0.3);
    }
    h2 {
      text-align: center;
      color: #e74c3c;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin: 8px 0 15px 0;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    input[type=submit] {
      background-color: #e74c3c;
      color: white;
      cursor: pointer;
      font-weight: bold;
    }
    .msg {
      text-align: center;
      font-weight: bold;
      color: green;
    }
    .error {
      text-align: center;
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Department of Computer Science</h2>

  <?php if ($success) echo "<div class='msg'>$success</div>"; ?>
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>

  <form method="POST" action="">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="city" placeholder="City" required>
    <input type="text" name="phone" placeholder="Phone Number" required>

    <select name="role" required>
      <option value="">Select Role</option>
      <option value="donor">Donor</option>
      <option value="recipient">Recipient</option>
    </select>

    <select name="blood_group" required>
      <option value="">Select Blood Group</option>
      <option value="A+">A+</option>
      <option value="A-">A-</option>
      <option value="B+">B+</option>
      <option value="B-">B-</option>
      <option value="O+">O+</option>
      <option value="O-">O-</option>
      <option value="AB+">AB+</option>
      <option value="AB-">AB-</option>
    </select>

    <input type="submit" name="register" value="Register">
  </form>
</div>

</body>
</html>
