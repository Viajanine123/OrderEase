<?php
// track.php - Customer Order Tracking Page (Redesigned)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrderEase — Track Your Order</title>
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
            flex-direction: column;
        }

        /* HEADER */
        header {
            background: var(--ink);
            padding: 20px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 22px;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .logo-dot {
            width: 8px; height: 8px;
            background: var(--accent);
            border-radius: 50%;
        }

        .header-link {
            font-size: 13px;
            color: #6b6560;
            text-decoration: none;
            transition: color 0.15s;
        }
        .header-link:hover { color: var(--white); }

        /* HERO SECTION */
        .hero {
            background: var(--ink);
            padding: 64px 48px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.08;
            bottom: -150px; left: -100px;
        }
        .hero::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.06;
            top: -100px; right: -60px;
        }

        .hero-label {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 16px;
        }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(32px, 4vw, 52px);
            font-weight: 800;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .hero-title span { color: var(--accent); }

        .hero-desc {
            color: #6b6560;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }

        /* CARD */
        .card-wrap {
            display: flex;
            justify-content: center;
            margin-top: -40px;
            padding: 0 24px 64px;
            position: relative;
            z-index: 10;
        }

        .card {
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 48px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.08);
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .card-sub {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 32px;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .input-wrap {
            display: flex;
            gap: 0;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrap:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 96, 26, 0.12);
        }

        .input-icon {
            background: var(--cream);
            padding: 0 16px;
            display: flex;
            align-items: center;
            font-size: 18px;
            border-right: 1.5px solid var(--border);
        }

        input[type="text"] {
            flex: 1;
            padding: 14px 16px;
            border: none;
            background: var(--cream);
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            outline: none;
        }

        .btn-track {
            width: 100%;
            padding: 15px;
            background: var(--ink);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            margin-top: 16px;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-track:hover { background: #222; transform: translateY(-1px); }
        .btn-track:active { transform: translateY(0); }

        .hint {
            margin-top: 16px;
            font-size: 12px;
            color: #b0a898;
            text-align: center;
        }

        /* HOW IT WORKS */
        .steps-section {
            padding: 0 48px 64px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .steps-title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 32px;
            color: var(--ink);
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .step-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            text-align: center;
        }

        .step-num {
            width: 36px; height: 36px;
            background: var(--accent);
            color: var(--white);
            border-radius: 50%;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
        }

        .step-icon { font-size: 24px; margin-bottom: 10px; }
        .step-heading { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .step-desc { font-size: 13px; color: var(--muted); line-height: 1.6; }

        /* FOOTER */
        footer {
            margin-top: auto;
            background: var(--ink);
            padding: 24px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #4a4540;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-text {
            font-size: 12px;
            color: #3a3530;
        }

        @media (max-width: 700px) {
            header { padding: 16px 20px; }
            .hero { padding: 48px 20px 64px; }
            .card { padding: 32px 24px; }
            .steps-grid { grid-template-columns: 1fr; }
            .steps-section { padding: 0 20px 48px; }
            footer { padding: 20px; flex-direction: column; gap: 8px; text-align: center; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="index.php" class="logo">
        <span class="logo-dot"></span> OrderEase
    </a>
    <a href="index.php" class="header-link">← Admin Login</a>
</header>

<!-- HERO -->
<div class="hero">
    <p class="hero-label">Customer Portal</p>
    <h1 class="hero-title">Where is my <span>order?</span></h1>
    <p class="hero-desc">Enter your tracking ID below to get real-time delivery updates.</p>
</div>

<!-- TRACK CARD -->
<div class="card-wrap">
    <div class="card">
        <h2 class="card-title">Track Your Order</h2>
        <p class="card-sub">Use the unique tracking ID provided to you.</p>

        <form action="view_tracking.php" method="get">
            <label for="tracking_id">Tracking ID</label>
            <div class="input-wrap">
                <span class="input-icon">🚚</span>
                <input type="text" id="tracking_id" name="tracking_id"
                    placeholder="e.g., ORD123, BBB236" required autocomplete="off">
            </div>
            <button type="submit" class="btn-track">Track My Order →</button>
        </form>

        <p class="hint">Your tracking ID was provided when your order was placed.</p>
    </div>
</div>

<!-- HOW IT WORKS -->
<div class="steps-section">
    <h3 class="steps-title">How it works</h3>
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-icon">📋</div>
            <div class="step-heading">Enter Tracking ID</div>
            <div class="step-desc">Type the unique tracking ID you received when your order was placed.</div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-icon">🔍</div>
            <div class="step-heading">We Find Your Order</div>
            <div class="step-desc">Our system instantly looks up your order and delivery details.</div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-icon">📍</div>
            <div class="step-heading">See Live Status</div>
            <div class="step-desc">View the current delivery status, payment info, and order details.</div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-logo">
        <span style="width:6px;height:6px;background:var(--accent);border-radius:50%;display:inline-block;"></span>
        OrderEase
    </div>
    <span class="footer-text">© <?= date('Y') ?> OrderEase. Delivery Tracking System.</span>
</footer>

</body>
</html>
