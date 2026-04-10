<?php
// admin_dashboard.php - OrderEase Admin Dashboard (Redesigned)
session_start();

// Uncomment below to enforce login via index.php
// if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
//     header('Location: index.php');
//     exit;
// }

require_once 'db_config.php';

// Fetch all tracking records
$result = $conn->query("SELECT * FROM tracking_items ORDER BY id DESC");
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Stats
$total = count($orders);
$delivered = count(array_filter($orders, fn($o) => strtolower($o['delivery_status'] ?? '') === 'delivered'));
$pending   = count(array_filter($orders, fn($o) => in_array(strtolower($o['delivery_status'] ?? ''), ['pending', 'processing'])));
$in_transit = count(array_filter($orders, fn($o) => strtolower($o['delivery_status'] ?? '') === 'in transit'));
$paid = count(array_filter($orders, fn($o) => strtolower($o['payment_status'] ?? '') === 'paid'));

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Search
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $result2 = $conn->query("SELECT * FROM tracking_items WHERE tracking_id LIKE '%$s%' OR customer_name LIKE '%$s%' ORDER BY id DESC");
    $orders = $result2 ? $result2->fetch_all(MYSQLI_ASSOC) : [];
}

function statusBadge($status, $type = 'delivery') {
    $s = strtolower(trim($status ?? 'unknown'));
    $map = [
        'delivered'  => ['bg'=>'#e6f4ea','color'=>'#2d7a3a','label'=>'Delivered'],
        'in transit' => ['bg'=>'#fff3e0','color'=>'#c26a00','label'=>'In Transit'],
        'pending'    => ['bg'=>'#fff8e1','color'=>'#a07800','label'=>'Pending'],
        'processing' => ['bg'=>'#e8f0fe','color'=>'#1a56db','label'=>'Processing'],
        'cancelled'  => ['bg'=>'#fce8e6','color'=>'#c62828','label'=>'Cancelled'],
        'paid'       => ['bg'=>'#e6f4ea','color'=>'#2d7a3a','label'=>'Paid'],
        'unpaid'     => ['bg'=>'#fce8e6','color'=>'#c62828','label'=>'Unpaid'],
        'cod'        => ['bg'=>'#f3e5f5','color'=>'#7b1fa2','label'=>'COD'],
    ];
    $d = $map[$s] ?? ['bg'=>'#f0f0f0','color'=>'#555','label'=>ucfirst($status)];
    return "<span style='background:{$d['bg']};color:{$d['color']};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;'>{$d['label']}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OrderEase — Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --ink: #0d0d0d;
    --paper: #f5f2eb;
    --cream: #ede9df;
    --accent: #d4601a;
    --accent-soft: #fff0e8;
    --muted: #7a7265;
    --white: #ffffff;
    --border: #ddd8cf;
    --sidebar-w: 280px;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--paper);
    color: var(--ink);
    min-height: 100vh;
    display: flex;
}

/* SIDEBAR */
.sidebar {
    width: var(--sidebar-w);
    background: var(--ink);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 32px 24px;
    position: fixed;
    top: 0; left: 0;
    z-index: 100;
}

.sidebar-logo {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 22px;
    color: var(--white);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 40px;
}
.logo-dot { width: 8px; height: 8px; background: var(--accent); border-radius: 50%; }

.nav-label {
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #4a4540;
    margin-bottom: 10px;
    margin-top: 24px;
    padding-left: 4px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    color: #8a8076;
    font-size: 14px;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
    margin-bottom: 2px;
}
.nav-item:hover { background: #1e1e1c; color: var(--white); }
.nav-item.active { background: var(--accent); color: var(--white); }
.nav-icon { font-size: 16px; width: 20px; text-align: center; }

.sidebar-bottom {
    margin-top: auto;
    padding-top: 24px;
    border-top: 1px solid #1e1e1c;
}
.sidebar-bottom a {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #4a4540;
    text-decoration: none;
    font-size: 13px;
    padding: 8px 12px;
    border-radius: 8px;
    transition: color 0.15s, background 0.15s;
}
.sidebar-bottom a:hover { color: #e05555; background: #1e1e1c; }

/* MAIN */
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* TOPBAR */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 36px;
    background: var(--paper);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 50;
}

.topbar-left h1 {
    font-family: 'Syne', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--ink);
}
.topbar-left p { font-size: 13px; color: var(--muted); margin-top: 2px; }

.topbar-actions { display: flex; gap: 12px; align-items: center; }

.search-box {
    display: flex;
    align-items: center;
    background: var(--cream);
    border: 1.5px solid var(--border);
    border-radius: 8px;
    padding: 8px 14px;
    gap: 8px;
}
.search-box input {
    border: none;
    background: transparent;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: var(--ink);
    outline: none;
    width: 200px;
}
.search-box button {
    background: none; border: none; cursor: pointer;
    color: var(--muted); font-size: 14px;
}

.btn {
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    transition: all 0.15s;
}
.btn-primary { background: var(--ink); color: var(--white); }
.btn-primary:hover { background: #222; }
.btn-danger { background: #fce8e6; color: #c62828; }
.btn-danger:hover { background: #f4d0cd; }
.btn-warning { background: #fff3e0; color: #c26a00; }
.btn-warning:hover { background: #ffe4b5; }

/* CONTENT */
.content { padding: 32px 36px; flex: 1; }

/* STATS CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: var(--white);
    border-radius: 14px;
    padding: 24px;
    border: 1px solid var(--border);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 0 14px 0 80px;
    opacity: 0.08;
}
.stat-card.total::before { background: #0d0d0d; }
.stat-card.delivered::before { background: #2d7a3a; }
.stat-card.transit::before { background: #c26a00; }
.stat-card.pending::before { background: #1a56db; }

.stat-icon {
    font-size: 22px;
    margin-bottom: 12px;
}
.stat-num {
    font-family: 'Syne', sans-serif;
    font-size: 36px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 4px;
}
.stat-label {
    font-size: 13px;
    color: var(--muted);
}

/* TABLE SECTION */
.table-section {
    background: var(--white);
    border-radius: 16px;
    border: 1px solid var(--border);
    overflow: hidden;
}

.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}
.table-title {
    font-family: 'Syne', sans-serif;
    font-size: 16px;
    font-weight: 700;
}
.table-count {
    font-size: 12px;
    color: var(--muted);
    background: var(--cream);
    padding: 3px 10px;
    border-radius: 20px;
    margin-left: 10px;
}

table { width: 100%; border-collapse: collapse; }
thead th {
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--muted);
    text-align: left;
    background: var(--paper);
    border-bottom: 1px solid var(--border);
}
tbody tr { border-bottom: 1px solid #f0ece4; transition: background 0.1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #faf8f4; }

td {
    padding: 14px 20px;
    font-size: 14px;
    vertical-align: middle;
}

.tracking-id {
    font-family: 'Syne', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    letter-spacing: 0.5px;
}
.customer-name { font-weight: 500; }
.customer-address { font-size: 12px; color: var(--muted); margin-top: 2px; }

.product-list { font-size: 12px; color: var(--muted); line-height: 1.5; }

.actions { display: flex; gap: 6px; }

.empty-state {
    text-align: center;
    padding: 64px 32px;
    color: var(--muted);
}
.empty-state .empty-icon { font-size: 48px; margin-bottom: 16px; }
.empty-state h3 { font-family: 'Syne', sans-serif; font-size: 18px; color: var(--ink); margin-bottom: 8px; }
.empty-state p { font-size: 14px; }

/* ALERT */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 24px;
}
.alert-success { background: #e6f4ea; color: #2d7a3a; border: 1px solid #b7dfbe; }
.alert-error { background: #fce8e6; color: #c62828; border: 1px solid #f4c0bc; }

@media (max-width: 1100px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; }
    .content { padding: 20px 16px; }
    .topbar { padding: 16px; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
    .search-box input { width: 140px; }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="logo-dot"></span> OrderEase
    </div>

    <span class="nav-label">Main Menu</span>
    <a href="admin_dashboard.php" class="nav-item active">
        <span class="nav-icon">📦</span> Dashboard
    </a>
    <a href="admin_add.php" class="nav-item">
        <span class="nav-icon">➕</span> Add Order
    </a>
    <a href="track.php" class="nav-item">
        <span class="nav-icon">🔍</span> Track Order
    </a>

    <div class="sidebar-bottom">
        <a href="?logout=1">⏻ &nbsp;Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <h1>Dashboard</h1>
            <p><?= date('l, F j, Y') ?></p>
        </div>
        <div class="topbar-actions">
            <form method="GET" action="">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Go</button>
                </div>
            </form>
            <a href="admin_add.php" class="btn btn-primary">+ Add Order</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">✓ Order deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">✓ Order updated successfully.</div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">📦</div>
                <div class="stat-num"><?= $total ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card delivered">
                <div class="stat-icon">✅</div>
                <div class="stat-num"><?= $delivered ?></div>
                <div class="stat-label">Delivered</div>
            </div>
            <div class="stat-card transit">
                <div class="stat-icon">🚚</div>
                <div class="stat-num"><?= $in_transit ?></div>
                <div class="stat-label">In Transit</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-num"><?= $pending ?></div>
                <div class="stat-label">Pending / Processing</div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-section">
            <div class="table-header">
                <div style="display:flex;align-items:center;">
                    <span class="table-title">All Orders</span>
                    <span class="table-count"><?= count($orders) ?> records</span>
                </div>
                <?php if ($search): ?>
                    <a href="admin_dashboard.php" class="btn btn-warning">✕ Clear Search</a>
                <?php endif; ?>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3><?= $search ? 'No results found' : 'No orders yet' ?></h3>
                    <p><?= $search ? 'Try a different search term.' : 'Add your first order to get started.' ?></p>
                    <?php if (!$search): ?>
                        <a href="admin_add.php" class="btn btn-primary" style="margin-top:16px;display:inline-flex;">+ Add First Order</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tracking ID</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Payment</th>
                            <th>Delivery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><span class="tracking-id"><?= htmlspecialchars($order['tracking_id'] ?? '—') ?></span></td>
                            <td>
                                <div class="customer-name"><?= htmlspecialchars($order['customer_name'] ?? '—') ?></div>
                                <div class="customer-address"><?= htmlspecialchars(substr($order['customer_address'] ?? '', 0, 40)) . (strlen($order['customer_address'] ?? '') > 40 ? '…' : '') ?></div>
                            </td>
                            <td>
                                <div class="product-list">
                                    <?php
                                    // Try to display products/quantities if stored as JSON or plain text
                                    $products = $order['products'] ?? $order['product_name'] ?? '—';
                                    echo htmlspecialchars(is_string($products) ? $products : json_encode($products));
                                    ?>
                                </div>
                            </td>
                            <td><?= statusBadge($order['payment_status'] ?? 'Unknown', 'payment') ?></td>
                            <td><?= statusBadge($order['delivery_status'] ?? 'Unknown', 'delivery') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="admin_update.php?tracking_id=<?= urlencode($order['tracking_id'] ?? '') ?>" class="btn btn-warning" title="Edit">✏️ Edit</a>
                                    <a href="admin_delete.php?tracking_id=<?= urlencode($order['tracking_id'] ?? '') ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Delete this order?')">🗑</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

</body>
</html>
