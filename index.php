<?php
// index.php - OrderEase Landing & Login Page
// Simple session-based login (customize credentials as needed)
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Default credentials - change these!
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrderEase — Delivery Tracking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #0d0d0d;
            --paper: #f5f2eb;
            --cream: #ede9df;
            --accent: #d4601a;
            --accent-dark: #b34d12;
            --muted: #7a7265;
            --white: #ffffff;
            --border: #d6d0c4;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--paper);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Left panel */
        .hero {
            flex: 1;
            background: var(--ink);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.12;
            bottom: -120px; left: -120px;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.07;
            top: -80px; right: 60px;
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 28px;
            color: var(--white);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-dot {
            width: 10px; height: 10px;
            background: var(--accent);
            border-radius: 50%;
            display: inline-block;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-label {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(38px, 4vw, 58px);
            font-weight: 800;
            color: var(--white);
            line-height: 1.05;
            margin-bottom: 20px;
        }

        .hero-title span {
            color: var(--accent);
        }

        .hero-desc {
            color: #8a8580;
            font-size: 15px;
            line-height: 1.7;
            max-width: 360px;
        }

        .stats-row {
            display: flex;
            gap: 32px;
            position: relative;
            z-index: 1;
        }

        .stat { }
        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--white);
        }
        .stat-label {
            font-size: 12px;
            color: #6b6560;
            margin-top: 2px;
        }

        /* Right panel */
        .login-panel {
            width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px 56px;
            background: var(--paper);
        }

        .login-heading {
            font-family: 'Syne', sans-serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .login-sub {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid var(--border);
            background: var(--cream);
            border-radius: 8px;
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 96, 26, 0.12);
        }

        .error-msg {
            background: #fff0eb;
            border: 1px solid #f4c4aa;
            color: var(--accent-dark);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--ink);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 8px;
        }

        .btn-login:hover { background: #222; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0;
            color: var(--border);
            font-size: 12px;
            color: var(--muted);
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .btn-track {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: var(--ink);
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        .btn-track:hover {
            border-color: var(--accent);
            background: #fff8f5;
        }

        .footer-note {
            margin-top: 32px;
            font-size: 12px;
            color: #b0a898;
            text-align: center;
        }

        @media (max-width: 800px) {
            body { flex-direction: column; }
            .hero { padding: 32px; min-height: 260px; }
            .stats-row { display: none; }
            .login-panel { width: 100%; padding: 40px 28px; }
        }
    </style>
</head>
<body>

<div class="hero">
    <div class="logo">
        <span class="logo-dot"></span>
        OrderEase
    </div>

    <div class="hero-content">
        <p class="hero-label">Delivery Tracking System</p>
        <h1 class="hero-title">Track every<br>order with <span>ease.</span></h1>
        <p class="hero-desc">A simple, powerful admin panel for managing product deliveries and keeping your customers in the loop — in real time.</p>
    </div>

    <div class="stats-row">
        <div class="stat">
            <div class="stat-num">100%</div>
            <div class="stat-label">Order visibility</div>
        </div>
        <div class="stat">
            <div class="stat-num">Live</div>
            <div class="stat-label">Status updates</div>
        </div>
        <div class="stat">
            <div class="stat-num">Easy</div>
            <div class="stat-label">Customer tracking</div>
        </div>
    </div>
</div>

<div class="login-panel">
    <h2 class="login-heading">Admin Login</h2>
    <p class="login-sub">Sign in to manage orders and deliveries.</p>

    <?php if ($error): ?>
        <div class="error-msg">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn-login">Sign In to Dashboard →</button>
    </form>

    <div class="divider">or</div>

    <a href="track.php" class="btn-track">🚚 Track an Order (Customer View)</a>

    <p class="footer-note">Default credentials: admin / admin123 — change in index.php</p>
</div>

</body>
</html>
