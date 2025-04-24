<?php
if (isset($_GET['id'])) {
    $notification_id = $_GET['id'];

    // Mark the notification as read
    $stmt = $conn->prepare("UPDATE notifications SET read_status = 1 WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Notification marked as read'); window.location = 'donor_dashboard.php';</script>";
}
?>
