<?php
session_start();
include("db.php");

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    die("Unauthorized Access");
}

$user_id = $_SESSION['user_id'];

// Get donor details
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle availability update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_status'])) {
    $new_status = $_POST['availability'];
    $stmt = $conn->prepare("UPDATE users SET availability=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: donor_dashboard.php");
    exit();
}

// Fetch notifications for this donor
$notif_query = "
    SELECT n.*, u.name AS recipient_name, u.phone AS recipient_phone
    FROM notifications n
    JOIN users u ON n.recipient_id = u.id
    WHERE n.donor_id = $user_id
    ORDER BY n.created_at DESC
";
$notifications = mysqli_query($conn, $notif_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donor Dashboard</title>
    <style>
        body { font-family: Arial; background-color: #f4f4f4; padding: 20px; }
        .container {
            max-width: 600px; margin: auto;
            background: white; padding: 20px;
            border-radius: 10px; box-shadow: 0 0 10px gray;
        }
        h2 { color: #b30000; text-align: center; }
        p { font-size: 16px; line-height: 1.6; }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #b30000;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #800000;
        }
        .notifications { margin-top: 30px; }
        .notification {
            background: #e2e2e2;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 6px;
            border-left: 5px solid #b30000;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome, Donor</h2>
    <p><strong>Name:</strong> <?= $user['name'] ?></p>
    <p><strong>Email:</strong> <?= $user['email'] ?></p>
    <p><strong>Blood Group:</strong> <?= $user['blood_group'] ?></p>
    <p><strong>City:</strong> <?= $user['city'] ?></p>
    <p><strong>Phone:</strong> <?= $user['phone'] ?></p>
    <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
    <p><strong>Availability:</strong> <?= ucfirst($user['availability']) ?></p>

    <form method="POST">
        <label><b>Update Your Availability</b></label>
        <select name="availability" required>
            <option value="available" <?= $user['availability'] === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="not available" <?= $user['availability'] === 'not available' ? 'selected' : '' ?>>Not Available</option>
        </select>
        <button type="submit" name="set_status">Update Status</button>
    </form>

    <!-- 🔔 Notifications -->
    <div class="notifications">
        <h3>🔔 Notifications</h3>
        <?php if (mysqli_num_rows($notifications) > 0): ?>
            <?php while ($n = mysqli_fetch_assoc($notifications)): ?>
                <div class="notification">
                    <p><strong>Message:</strong> <?= $n['message'] ?></p>
                    <p><strong>From Recipient:</strong> <?= $n['recipient_name'] ?> (<?= $n['recipient_phone'] ?>)</p>
                    <p><strong>Date:</strong> <?= date("d M Y h:i A", strtotime($n['created_at'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
