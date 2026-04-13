<?php
require_once 'db_config.php';

if (isset($_GET['tracking_id']) && !empty($_GET['tracking_id'])) {
    $tracking_id = $conn->real_escape_string($_GET['tracking_id']);

    // Get the order details before deleting
    $select = $conn->query("SELECT * FROM tracking_items WHERE tracking_id = '$tracking_id'");
    $order  = $select->fetch_assoc();

    if ($order) {
        // Save to history first
        $cname    = $conn->real_escape_string($order['customer_name']);
        $caddress = $conn->real_escape_string($order['customer_address']);
        $products = $conn->real_escape_string($order['products']);
        $price    = $conn->real_escape_string($order['price']);
        $odate    = $conn->real_escape_string($order['order_date']);
        $pstatus  = $conn->real_escape_string($order['payment_status']);
        $dstatus  = $conn->real_escape_string($order['delivery_status']);

        $conn->query("INSERT INTO order_history 
            (tracking_id, customer_name, customer_address, products, price, order_date, payment_status, delivery_status)
            VALUES ('$tracking_id', '$cname', '$caddress', '$products', '$price', '$odate', '$pstatus', '$dstatus')");

        // Now delete
        $conn->query("DELETE FROM tracking_items WHERE tracking_id = '$tracking_id'");
    }

    header("Location: admin_dashboard.php?deleted=1");
    exit();
}

$conn->close();
header("Location: admin_dashboard.php");
exit();
?>
