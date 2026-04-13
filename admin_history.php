<?php
require_once 'db_config.php';

// Fetch all deleted orders from history
$result  = $conn->query("SELECT * FROM order_history ORDER BY deleted_at DESC");
$history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$total   = count($history);

function statusBadge($status) {
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
    <title>OrderEase — Order History</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ink: #0d0d0d; --paper: #f5f2eb; --cream: #ede9df;
            --accent: #d4601a; --muted: #7a7265; --white: #ffffff;
            --border: #ddd8cf; --sidebar-w: 280px;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--paper); color: var(--ink); min-height: 100vh; display: flex; }

        /* SIDEBAR */
        .sidebar { width: var(--sidebar-w); background: var(--ink); min-height: 100vh; display: flex; flex-direction: column; padding: 32px 24px; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar-logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 22px; color: var(--white); display: flex; align-items: center; gap: 8px; margin-bottom: 40px; }
        .logo-dot { width: 8px; height: 8px; background: var(--accent); border-radius: 50%; }
        .nav-label { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #4a4540; margin-bottom: 10px; margin-top: 24px; padding-left: 4px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: #8a8076; font-size: 14px; text-decoration: none; transition: background 0.15s, color 0.15s; margin-bottom: 2px; }
        .nav-item:hover { background: #1e1e1c; color: var(--white); }
        .nav-item.active { background: var(--accent); color: var(--white); }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-bottom { margin-top: auto; padding-top: 24px; border-top: 1px solid #1e1e1c; }
        .sidebar-bottom a { display: flex; align-items: center; gap: 8px; color: #4a4540; text-decoration: none; font-size: 13px; padding: 8px 12px; border-radius: 8px; transition: color 0.15s, background 0.15s; }
        .sidebar-bottom a:hover { color: #e05555; background: #1e1e1c; }

        /* MAIN */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* TOPBAR */
        .topbar { display: flex; align-items: center; justify-content: space-between; padding: 24px 36px; background: var(--paper); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50; }
        .topbar-left h1 { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; }
        .topbar-left p { font-size: 13px; color: var(--muted); margin-top: 2px; }

        /* CONTENT */
        .content { padding: 32px 36px; flex: 1; }

        /* STAT */
        .stat-card { background: var(--white); border-radius: 14px; padding: 24px; border: 1px solid var(--border); display: inline-flex; align-items: center; gap: 16px; margin-bottom: 28px; }
        .stat-icon { font-size: 28px; }
        .stat-num { font-family: 'Syne', sans-serif; font-size: 32px; font-weight: 700; }
        .stat-label { font-size: 13px; color: var(--muted); }

        /* TABLE */
        .table-section { background: var(--white); border-radius: 16px; border: 1px solid var(--border); overflow: hidden; }
        .table-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border); }
        .table-title { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; }
        .table-count { font-size: 12px; color: var(--muted); background: var(--cream); padding: 3px 10px; border-radius: 20px; margin-left: 10px; }

        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 12px 20px; font-size: 11px; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); text-align: left; background: var(--paper); border-bottom: 1px solid var(--border); }
        tbody tr { border-bottom: 1px solid #f0ece4; transition: background 0.1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #faf8f4; }
        td { padding: 14px 20px; font-size: 14px; vertical-align: middle; }

        .tracking-id { font-family: 'Syne', sans-serif; font-size: 13px; font-weight: 600; letter-spacing: 0.5px; }
        .customer-name { font-weight: 500; }
        .customer-address { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .deleted-at { font-size: 12px; color: #c62828; background: #fce8e6; padding: 3px 10px; border-radius: 20px; }

        .empty-state { text-align: center; padding: 64px 32px; color: var(--muted); }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 16px; }
        .empty-state h3 { font-family: 'Syne', sans-serif; font-size: 18px; color: var(--ink); margin-bottom: 8px; }

        .btn { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: none; transition: all 0.15s; }
        .btn-primary { background: var(--ink); color: var(--white); }
        .btn-primary:hover { background: #222; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .content { padding: 20px 16px; }
            .topbar { padding: 16px; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo"><span class="logo-dot"></span> OrderEase</div>
    <span class="nav-label">Main Menu</span>
    <a href="admin_dashboard.php" class="nav-item"><span class="nav-icon">📦</span> Dashboard</a>
    <a href="admin_add.php" class="nav-item"><span class="nav-icon">➕</span> Add Order</a>
    <a href="track.php" class="nav-item"><span class="nav-icon">🔍</span> Track Order</a>
    <a href="admin_history.php" class="nav-item active"><span class="nav-icon">🗂️</span> Order History</a>
    <div class="sidebar-bottom">
        <a href="?logout=1">⏻ &nbsp;Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h1>Order History</h1>
            <p>All deleted orders are stored here</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
    </div>

    <div class="content">

        <!-- STAT -->
        <div class="stat-card">
            <div class="stat-icon">🗂️</div>
            <div>
                <div class="stat-num"><?= $total ?></div>
                <div class="stat-label">Deleted Orders</div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-section">
            <div class="table-header">
                <div style="display:flex;align-items:center;">
                    <span class="table-title">Deleted Orders</span>
                    <span class="table-count"><?= $total ?> records</span>
                </div>
            </div>

            <?php if (empty($history)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🗂️</div>
                    <h3>No deleted orders yet</h3>
                    <p>Deleted orders will appear here automatically.</p>
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
                            <th>Deleted At</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($history as $order): ?>
                        <tr>
                            <td><span class="tracking-id"><?= htmlspecialchars($order['tracking_id'] ?? '—') ?></span></td>
                            <td>
                                <div class="customer-name"><?= htmlspecialchars($order['customer_name'] ?? '—') ?></div>
                                <div class="customer-address"><?= htmlspecialchars(substr($order['customer_address'] ?? '', 0, 40)) . (strlen($order['customer_address'] ?? '') > 40 ? '…' : '') ?></div>
                            </td>
                            <td style="font-size:12px;color:var(--muted);">
                                <?php
                                $products = json_decode($order['products'] ?? '[]', true);
                                if ($products && is_array($products)) {
                                    foreach ($products as $p) {
                                        echo htmlspecialchars($p['name'] ?? '') . ' x' . ($p['qty'] ?? 1) . '<br>';
                                    }
                                } else {
                                    echo htmlspecialchars($order['products'] ?? '—');
                                }
                                ?>
                            </td>
                            <td><?= statusBadge($order['payment_status'] ?? 'Unknown') ?></td>
                            <td><?= statusBadge($order['delivery_status'] ?? 'Unknown') ?></td>
                            <td><span class="deleted-at">🗑 <?= htmlspecialchars($order['deleted_at'] ?? '—') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
