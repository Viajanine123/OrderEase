<?php
// admin_add.php - OrderEase (API-integrated with Inventory System)
require_once 'db_config.php';

$message = "";
$message_type = "";

// Fetch products from Inventory via API
$api_url = "http://localhost/Embodo_FinalProject/api.php?action=get_products";
$api_response = @file_get_contents($api_url);
$inventory_products = [];
if ($api_response) {
    $decoded = json_decode($api_response, true);
    if ($decoded['success']) {
        $inventory_products = $decoded['products'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tracking_id      = $conn->real_escape_string($_POST['tracking_id']);
    $customer_name    = $conn->real_escape_string($_POST['customer_name']);
    $customer_address = $conn->real_escape_string($_POST['customer_address']);
    $order_date       = $conn->real_escape_string($_POST['order_date']);
    $payment_status   = $conn->real_escape_string($_POST['payment_status']);
    $delivery_status  = $conn->real_escape_string($_POST['delivery_status']);

    // Build products array
    $products_array = [];
    if (isset($_POST['product_name']) && is_array($_POST['product_name'])) {
        foreach ($_POST['product_name'] as $index => $name) {
            $qty = isset($_POST['product_qty'][$index]) ? (int)$_POST['product_qty'][$index] : 0;
            if (!empty(trim($name)) && $qty > 0) {
                $products_array[] = ['name' => trim($name), 'qty' => $qty];
            }
        }
    }

    // Call API to place order (checks stock + deducts inventory)
    $api_payload = json_encode([
        'tracking_id'      => $_POST['tracking_id'],
        'customer_name'    => $_POST['customer_name'],
        'customer_address' => $_POST['customer_address'],
        'products'         => $products_array,
        'payment_status'   => $_POST['payment_status']
    ]);

    $ch = curl_init("http://localhost/Embodo_FinalProject/api.php?action=place_order");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $api_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $api_result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($api_result, true);

    if ($response && $response['success']) {
        $message = "✅ Order placed successfully! Tracking ID: " . htmlspecialchars($_POST['tracking_id']);
        if (!empty($response['warnings'])) {
            $message .= "<br><br>" . implode("<br>", $response['warnings']);
        }
        $message_type = "success";
    } else {
        $errors = $response['errors'] ?? [$response['error'] ?? 'Unknown error.'];
        $message = "❌ " . implode("<br>", $errors);
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrderEase — Add Order</title>
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
        .topbar { display: flex; align-items: center; justify-content: space-between; padding: 24px 36px; background: var(--paper); border-bottom: 1px solid var(--border); }
        .topbar-left h1 { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; }
        .topbar-left p { font-size: 13px; color: var(--muted); margin-top: 2px; }

        .content { padding: 36px; flex: 1; display: flex; gap: 28px; align-items: flex-start; }

        /* FORM CARD */
        .form-card { background: var(--white); border-radius: 20px; border: 1px solid var(--border); padding: 36px; flex: 1; max-width: 680px; box-shadow: 0 8px 32px rgba(0,0,0,0.05); }
        .card-title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .card-sub { font-size: 13px; color: var(--muted); margin-bottom: 28px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }

        label { font-size: 11px; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); }

        input[type="text"], input[type="number"], input[type="date"],
        select, textarea {
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            background: var(--cream);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        textarea { resize: vertical; min-height: 80px; }
        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212,96,26,0.1);
        }

        /* PRODUCTS SECTION */
        .products-section { margin: 24px 0; }
        .products-label { font-size: 11px; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; display: block; }

        .product-entry {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 10px;
            align-items: center;
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }

        .product-entry select { background: var(--white); }

        .stock-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
            font-weight: 500;
        }
        .stock-ok   { background: #e6f4ea; color: #2d7a3a; }
        .stock-low  { background: #fff3e0; color: #c26a00; }
        .stock-out  { background: #fce8e6; color: #c62828; }

        .btn-remove {
            background: #fce8e6; color: #c62828;
            border: none; border-radius: 6px;
            padding: 8px 12px; cursor: pointer;
            font-size: 13px; font-family: 'DM Sans', sans-serif;
            transition: background 0.15s;
        }
        .btn-remove:hover { background: #f4d0cd; }

        .btn-add-product {
            background: var(--cream);
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            color: var(--muted);
            transition: border-color 0.15s, color 0.15s;
            margin-top: 4px;
        }
        .btn-add-product:hover { border-color: var(--accent); color: var(--accent); }

        /* TOTAL PRICE */
        .total-box {
            background: var(--ink);
            color: var(--white);
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .total-label { font-size: 13px; color: #8a8076; }
        .total-amount { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; }

        /* ALERTS */
        .alert { padding: 14px 18px; border-radius: 10px; font-size: 13px; margin-bottom: 24px; line-height: 1.6; }
        .alert-success { background: #e6f4ea; color: #2d7a3a; border: 1px solid #b7dfbe; }
        .alert-error   { background: #fce8e6; color: #c62828; border: 1px solid #f4c0bc; }

        /* BUTTONS */
        .btn-row { display: flex; gap: 12px; }
        .btn { padding: 13px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: none; transition: all 0.15s; }
        .btn-primary { background: var(--ink); color: var(--white); flex: 1; justify-content: center; }
        .btn-primary:hover { background: #222; }
        .btn-secondary { background: var(--cream); color: var(--ink); border: 1.5px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }

        /* INVENTORY PANEL */
        .inventory-panel {
            width: 280px;
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 24px;
            position: sticky;
            top: 36px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.05);
        }
        .inv-title { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .inv-sub { font-size: 12px; color: var(--muted); margin-bottom: 16px; }
        .inv-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0ece4; font-size: 13px; }
        .inv-item:last-child { border-bottom: none; }
        .inv-name { font-weight: 500; }
        .inv-qty { font-size: 12px; padding: 2px 8px; border-radius: 20px; }

        @media (max-width: 1100px) { .inventory-panel { display: none; } }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .content { padding: 20px 16px; flex-direction: column; }
            .form-grid { grid-template-columns: 1fr; }
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
    <a href="admin_add.php" class="nav-item active"><span class="nav-icon">➕</span> Add Order</a>
    <a href="track.php" class="nav-item"><span class="nav-icon">🔍</span> Track Order</a>
    <div class="sidebar-bottom">
        <a href="?logout=1">⏻ &nbsp;Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h1>Add New Order</h1>
            <p>Products are loaded live from Inventory System</p>
        </div>
    </div>

    <div class="content">
        <!-- FORM -->
        <div class="form-card">
            <div class="card-title">📋 New Order</div>
            <div class="card-sub">Fill in the details below. Products and stock are synced with the Inventory System.</div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" action="admin_add.php" id="orderForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tracking ID</label>
                        <input type="text" name="tracking_id" placeholder="e.g., ORD001" required>
                    </div>
                    <div class="form-group">
                        <label>Order Date</label>
                        <input type="date" name="order_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select name="payment_status">
                            <option value="Pending">Pending</option>
                            <option value="Paid">Paid</option>
                            <option value="COD">COD</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Customer Address</label>
                        <textarea name="customer_address" placeholder="Full address" required></textarea>
                    </div>
                    <div class="form-group full">
                        <label>Delivery Status</label>
                        <select name="delivery_status">
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Out for Delivery">Out for Delivery</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <!-- PRODUCTS -->
                <div class="products-section">
                    <span class="products-label">Products & Quantity</span>
                    <div id="products_container"></div>
                    <button type="button" class="btn-add-product" id="add_product">+ Add Another Product</button>
                </div>

                <!-- TOTAL -->
                <div class="total-box">
                    <span class="total-label">Estimated Total</span>
                    <span class="total-amount" id="total_display">₱0.00</span>
                </div>
                <input type="hidden" name="price" id="price_input" value="0">

                <div class="btn-row">
                    <a href="admin_dashboard.php" class="btn btn-secondary">← Cancel</a>
                    <button type="submit" class="btn btn-primary">📦 Place Order</button>
                </div>
            </form>
        </div>

        <!-- INVENTORY PANEL -->
        <div class="inventory-panel">
            <div class="inv-title">📊 Inventory Stock</div>
            <div class="inv-sub">Live from Inventory System</div>
            <?php if (empty($inventory_products)): ?>
                <p style="font-size:13px;color:var(--muted);">Could not connect to Inventory API.</p>
            <?php else: ?>
                <?php foreach ($inventory_products as $p): ?>
                    <?php
                    $qty = (int)$p['quantity'];
                    $cls = $qty <= 5 ? 'stock-out' : ($qty <= 20 ? 'stock-low' : 'stock-ok');
                    ?>
                    <div class="inv-item">
                        <span class="inv-name"><?= htmlspecialchars($p['product_name']) ?></span>
                        <span class="inv-qty <?= $cls ?>"><?= $qty ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Products data from PHP/API
const inventoryProducts = <?= json_encode($inventory_products) ?>;

function getProductOptions(selectedName = '') {
    let options = '<option value="">-- Select Product --</option>';
    inventoryProducts.forEach(p => {
        const selected = p.product_name === selectedName ? 'selected' : '';
        options += `<option value="${p.product_name}" data-price="${p.price}" data-stock="${p.quantity}" ${selected}>${p.product_name} (₱${parseFloat(p.price).toLocaleString()})</option>`;
    });
    return options;
}

function getStockBadge(qty) {
    if (qty <= 0)  return `<span class="stock-badge stock-out">Out of stock</span>`;
    if (qty <= 5)  return `<span class="stock-badge stock-low">Low: ${qty} left</span>`;
    return `<span class="stock-badge stock-ok">${qty} in stock</span>`;
}

function addProductRow(name = '', qty = 1) {
    const container = document.getElementById('products_container');
    const div = document.createElement('div');
    div.classList.add('product-entry');
    div.innerHTML = `
        <select name="product_name[]" class="product-select" required>
            ${getProductOptions(name)}
        </select>
        <input type="number" name="product_qty[]" class="product-qty" value="${qty}" min="1" placeholder="Qty" required style="width:80px;">
        <div class="stock-info">${getStockBadge(0)}</div>
        <button type="button" class="btn-remove">✕</button>
    `;
    container.appendChild(div);

    const select = div.querySelector('.product-select');
    const qtyInput = div.querySelector('.product-qty');
    const stockInfo = div.querySelector('.stock-info');

    function updateStock() {
        const opt = select.options[select.selectedIndex];
        const stock = opt ? parseInt(opt.dataset.stock || 0) : 0;
        stockInfo.innerHTML = getStockBadge(stock);
        updateTotal();
    }

    select.addEventListener('change', updateStock);
    qtyInput.addEventListener('input', updateTotal);

    // Trigger if pre-selected
    if (name) updateStock();

    div.querySelector('.btn-remove').addEventListener('click', function() {
        if (container.children.length > 1) {
            div.remove();
            updateTotal();
        } else {
            alert('You must have at least one product.');
        }
    });
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.product-entry').forEach(entry => {
        const select = entry.querySelector('.product-select');
        const qty    = parseInt(entry.querySelector('.product-qty').value) || 0;
        const opt    = select.options[select.selectedIndex];
        const price  = opt ? parseFloat(opt.dataset.price || 0) : 0;
        total += price * qty;
    });
    document.getElementById('total_display').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('price_input').value = total.toFixed(2);
}

document.getElementById('add_product').addEventListener('click', () => addProductRow());

// Init with one row
addProductRow();
</script>

</body>
</html>
