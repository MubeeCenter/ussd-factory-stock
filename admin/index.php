<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';

// Get counts
$materials_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM materials"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM restock_requests WHERE status = 'pending'"))['total'];
$approved_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM restock_requests WHERE status = 'approved'"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard — Factory Stock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="/mubeetech_icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── RESET & BASE ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 260px;
            background: #1E2A5E;
            color: #fff;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.08);
        }
        .sidebar .logo {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .logo span { color: #2563eb; }
        .sidebar .logo small {
            display: block;
            font-size: 0.65rem;
            font-weight: 400;
            color: rgba(255,255,255,0.5);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .sidebar nav { flex: 1; }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            margin-bottom: 4px;
        }
        .sidebar nav a i { width: 20px; font-size: 1rem; color: rgba(255,255,255,0.4); }
        .sidebar nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar nav a:hover i { color: #fff; }
        .sidebar nav a.active {
            background: rgba(37, 99, 235, 0.2);
            color: #fff;
            border-left: 3px solid #2563eb;
        }
        .sidebar nav a.active i { color: #2563eb; }
        .sidebar .logout {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .logout a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .sidebar .logout a i { width: 20px; font-size: 1rem; color: rgba(255,255,255,0.3); }
        .sidebar .logout a:hover { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .sidebar .logout a:hover i { color: #f87171; }

        /* ── MAIN ── */
        .main {
            margin-left: 260px;
            padding: 2.5rem;
            flex: 1;
            min-height: 100vh;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .main-header h1 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
        }
        .main-header .user {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }
        .main-header .user i { color: #2563eb; }

        /* ── STATS CARDS ── */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .card {
            background: #ffffff;
            padding: 1.75rem;
            border-radius: 16px;
            border: 1px solid #eef2f6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            text-align: center;
            transition: all 0.25s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.06);
            border-color: rgba(37, 99, 235, 0.2);
        }
        .card .number {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 2.6rem;
            font-weight: 800;
            color: #1E2A5E;
            line-height: 1.2;
        }
        .card .label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 6px;
        }
        .card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.2rem;
        }
        .card .icon.blue { background: rgba(37, 99, 235, 0.08); color: #2563eb; }
        .card .icon.gold { background: rgba(212, 175, 55, 0.12); color: #b8860b; }
        .card .icon.green { background: rgba(0, 128, 128, 0.08); color: #008080; }
        .card.pending .number { color: #b8860b; }
        .card.approved .number { color: #008080; }

        /* ── RECENT ACTIVITY ── */
        .recent-section {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #eef2f6;
            padding: 1.75rem;
            margin-top: 2rem;
        }
        .recent-section h3 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .recent-section h3 i { color: #2563eb; }
        .recent-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        .recent-item:last-child { border-bottom: none; }
        .recent-item .status {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .status.pending { background: #fef3c7; color: #b45309; }
        .status.approved { background: #d1fae5; color: #065f46; }
        .status.rejected { background: #fee2e2; color: #991b1b; }
        .status.fulfilled { background: #dbeafe; color: #1e40af; }

        .recent-empty {
            color: #94a3b8;
            text-align: center;
            padding: 2rem 0;
            font-size: 0.9rem;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                min-height: auto;
                padding: 1.25rem;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
            }
            .sidebar .logo { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
            .sidebar nav {
                display: none;
                width: 100%;
                margin-top: 1rem;
                border-top: 1px solid rgba(255,255,255,0.08);
                padding-top: 1rem;
            }
            .sidebar nav.open { display: block; }
            .sidebar .logout { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08); }
            .main { margin-left: 0; padding: 1.5rem; }
            .cards { grid-template-columns: repeat(2, 1fr); }
            .hamburger {
                display: flex;
                background: none;
                border: none;
                color: #fff;
                font-size: 1.4rem;
                cursor: pointer;
                padding: 8px;
            }
        }
        @media (max-width: 480px) {
            .cards { grid-template-columns: 1fr; }
            .main-header h1 { font-size: 1.4rem; }
        }
        .hamburger { display: none; }
        .hamburger:hover { background: rgba(255,255,255,0.08); border-radius: 8px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="logo">
        <img src="../mubeetech_icon.png" alt="MubeeTech" style="height:35px; margin-right:8px; vertical-align:middle; display:inline-block;">
        Mubee<span>Tech</span>
        <small>Factory Stock Admin</small>
    </div>
        <button class="hamburger" id="menuToggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <nav id="sidebarNav">
            <a href="index.php" class="active"><i class="fas fa-chart-simple"></i> Dashboard</a>
            <a href="materials.php"><i class="fas fa-cubes"></i> Materials</a>
            <a href="restocks.php"><i class="fas fa-truck"></i> Restock Requests</a>
            <div class="logout">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="main-header">
            <h1>📊 Dashboard</h1>
            <div class="user"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></div>
        </div>

        <!-- Stats -->
        <div class="cards">
            <div class="card">
                <div class="icon blue"><i class="fas fa-cubes"></i></div>
                <div class="number"><?= $materials_count ?></div>
                <div class="label">Total Materials</div>
            </div>
            <div class="card pending">
                <div class="icon gold"><i class="fas fa-clock"></i></div>
                <div class="number"><?= $pending_count ?></div>
                <div class="label">Pending Restock Requests</div>
            </div>
            <div class="card approved">
                <div class="icon green"><i class="fas fa-check-circle"></i></div>
                <div class="number"><?= $approved_count ?></div>
                <div class="label">Approved Requests</div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-section">
            <h3><i class="fas fa-clock-rotate-left"></i> Recent Restock Activity</h3>
            <?php
            $recent_query = "
                SELECT r.*, m.name as material_name 
                FROM restock_requests r 
                LEFT JOIN materials m ON r.material_code = m.code 
                ORDER BY r.created_at DESC LIMIT 5
            ";
            $recent_result = mysqli_query($conn, $recent_query);
            $has_recent = mysqli_num_rows($recent_result) > 0;
            ?>
            <?php if ($has_recent): ?>
                <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                <div class="recent-item">
                    <span><strong><?= htmlspecialchars($row['material_name'] ?? $row['material_code']) ?></strong> — <?= htmlspecialchars($row['requested_by']) ?></span>
                    <span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="recent-empty">No recent restock activity.</div>
            <?php endif; ?>
            <?php mysqli_free_result($recent_result); ?>
        </div>

        <p style="color: #94a3b8; text-align: center; margin-top: 2.5rem; font-size: 0.8rem;">
            USSD Factory Stock Check — Admin Panel • MubeeTech
        </p>
    </main>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar nav').classList.toggle('open');
        });
    </script>
</body>
</html>