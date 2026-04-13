<?php
require_once 'db_config.php';

if (isset($_GET['tracking_id']) && !empty($_GET['tracking_id'])) {
    $tracking_id = $conn->real_escape_string($_GET['tracking_id']);
    $sql = "DELETE FROM tracking_items WHERE tracking_id = '$tracking_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php?deleted=1");
        exit();
    }
}
$conn->close();
header("Location: admin_dashboard.php");
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Tracking Item</title>
    <link rel="stylesheet" href="../style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2 class="page-title"><i class="fas fa-trash-alt"></i> Delete Status</h2>
        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <p class="back-link"><a href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></p>
    </div>
</body>
</html>