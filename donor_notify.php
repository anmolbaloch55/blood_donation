<?php
include('db.php');

if (isset($_GET['donor_id']) && isset($_GET['request_id'])) {
    $donor_id = $_GET['donor_id'];
    $request_id = $_GET['request_id'];

    // Get recipient info from request
    $result = mysqli_query($conn, "SELECT * FROM blood_requests WHERE id = $request_id");
    $req = mysqli_fetch_assoc($result);
    $recipient_id = $req['recipient_id'];
    $blood_group = $req['blood_group'];
    $city = $req['city'];
    $reason = $req['reason'];

    $msg = "🔔 Urgent Need for $blood_group blood in $city. Reason: $reason";

    // Insert into notifications table
    $stmt = $conn->prepare("INSERT INTO notifications (donor_id, recipient_id, blood_group, city, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $donor_id, $recipient_id, $blood_group, $city, $msg);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Notification sent successfully!'); window.history.back();</script>";
    } else {
        echo "<script>alert('❌ Failed to send notification'); window.history.back();</script>";
    }

    $stmt->close();
}
?>
