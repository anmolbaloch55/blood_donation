<?php
session_start();
include("db.php");

// 🛡 Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized Access");
}

$popup_message = "";

// ✅ Handle Notify Action
if (isset($_GET['donor_id']) && isset($_GET['request_id'])) {
    $donor_id = $_GET['donor_id'];
    $request_id = $_GET['request_id'];

    $result = mysqli_query($conn, "SELECT * FROM blood_requests WHERE id = $request_id");
    $req = mysqli_fetch_assoc($result);
    $recipient_id = $req['recipient_id'];
    $blood_group = $req['blood_group'];
    $city = $req['city'];
    $reason = $req['reason'];

    $msg = "Urgent need for $blood_group blood in $city. Reason: $reason";

    $stmt = $conn->prepare("INSERT INTO notifications (donor_id, recipient_id, blood_group, city, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $donor_id, $recipient_id, $blood_group, $city, $msg);
    $stmt->execute();
    $stmt->close();

    $popup_message = "✅ Notification sent successfully!";
}

// ✅ Handle Approve/Reject Blood Requests
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE blood_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    $stmt->close();
}

// ✅ Search Donors
$search_results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $group = $_POST['blood_group'];
    $city = $_POST['city'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE role='donor' AND blood_group=? AND city LIKE ?");
    $like_city = "%$city%";
    $stmt->bind_param("ss", $group, $like_city);
    $stmt->execute();
    $search_results = $stmt->get_result();
    $stmt->close();
}

// ✅ Fetch Blood Requests
$requests = mysqli_query($conn, "SELECT r.*, u.name, u.phone FROM blood_requests r JOIN users u ON r.recipient_id = u.id ORDER BY r.request_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        .container { background: white; padding: 25px; border-radius: 10px; max-width: 1000px; margin: auto; box-shadow: 0 0 10px gray; }
        h2 { color: #b30000; text-align: center; }
        table, th, td { border: 1px solid #ccc; border-collapse: collapse; padding: 10px; }
        table { width: 100%; margin-top: 20px; }
        form { margin-top: 20px; text-align: center; }
        input, select { padding: 8px; margin: 0 5px; border-radius: 4px; border: 1px solid #ccc; }
        button, .btn { padding: 8px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: bold; }
        .approve { background-color: #28a745; color: white; }
        .reject { background-color: #dc3545; color: white; }
        .notify { background-color: #007bff; color: white; }
        .notify:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛡️ Admin Dashboard - Blood Donation System-CS/IT</h2>

    <?php if ($popup_message): ?>
      <script>alert("<?= $popup_message ?>");</script>
    <?php endif; ?>

    <h3>📋 Blood Requests</h3>
    <table>
        <tr>
            <th>Recipient</th>
            <th>Blood Group</th>
            <th>City</th>
            <th>Reason</th>
            <th>Phone</th>
            <th>Document</th>
            <th>Status</th>
            <th>Action</th>
            <th>Date</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($requests)) { ?>
        <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['blood_group'] ?></td>
            <td><?= $row['city'] ?></td>
            <td><?= $row['reason'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td>
                <?php if ($row['document_path']) { ?>
                <a href="<?= $row['document_path'] ?>" target="_blank">View</a>
                <?php } ?>
            </td>
            <td><?= ucfirst($row['status']) ?></td>
            <td>
                <?php if ($row['status'] === 'pending') { ?>
                    <a class="btn approve" href="?action=approve&id=<?= $row['id'] ?>">Approve</a>
                    <a class="btn reject" href="?action=reject&id=<?= $row['id'] ?>">Reject</a>
                <?php } else { echo "—"; } ?>
            </td>
            <td><?= $row['request_date'] ?></td>
        </tr>
        <?php } ?>
    </table>

    <h3>🔍 Search Donors</h3>
    <form method="POST">
        <select name="blood_group" required>
            <option value="">Blood Group</option>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
        </select>
        <input type="text" name="city" placeholder="Enter city">
        <button type="submit" name="search">Search</button>
    </form>

    <?php if (!empty($search_results)) { ?>
    <h3>Donor Search Results:</h3>
    <table>
        <tr>
            <th>Name</th><th>Blood Group</th><th>City</th><th>Phone</th><th>Email</th><th>Notify</th>
        </tr>
        <?php while($donor = mysqli_fetch_assoc($search_results)) { ?>
        <tr>
            <td><?= $donor['name'] ?></td>
            <td><?= $donor['blood_group'] ?></td>
            <td><?= $donor['city'] ?></td>
            <td><?= $donor['phone'] ?></td>
            <td><?= $donor['email'] ?></td>
            <td>
                <!-- 🛑 Notify needs latest request ID (get last approved or pending one) -->
                <?php
                    $blood_req = mysqli_query($conn, "SELECT id FROM blood_requests WHERE blood_group = '{$donor['blood_group']}' AND city = '{$donor['city']}' ORDER BY request_date DESC LIMIT 1");
                    $last_req = mysqli_fetch_assoc($blood_req);
                ?>
                <?php if ($last_req): ?>
                    <a class="btn notify" href="admin_dashboard.php?donor_id=<?= $donor['id'] ?>&request_id=<?= $last_req['id'] ?>">Notify</a>
                <?php else: ?>
                    <em>No request found</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
</div>

</body>
</html>
