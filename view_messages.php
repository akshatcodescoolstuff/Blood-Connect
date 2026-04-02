<?php
session_start();
include 'config.php';

// Simple admin check (you can expand this)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied. Admin login required.");
}

$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #cc0000; color: white; }
        tr:hover { background: #f5f5f5; }
        .unread { background: #fff3f3; font-weight: bold; }
        .read { background: #f9f9f9; }
        .message-preview { max-width: 300px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    </style>
</head>
<body>
    <h1>Contact Messages</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr class="<?php echo $row['status'] == 'unread' ? 'unread' : 'read'; ?>">
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td class="message-preview" title="<?php echo htmlspecialchars($row['message']); ?>">
                    <?php echo htmlspecialchars(substr($row['message'], 0, 50)) . '...'; ?>
                </td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <a href="view_message.php?id=<?php echo $row['id']; ?>">View</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>