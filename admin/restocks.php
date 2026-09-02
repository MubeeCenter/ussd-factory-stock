<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['status'])) {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['status'];
    
    // Update the request status
    $stmt = mysqli_prepare($conn, "UPDATE restock_requests SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // --- SMS Notification to Requester ---
    $query = "SELECT r.requested_by, m.name as material_name 
              FROM restock_requests r 
              LEFT JOIN materials m ON r.material_code = m.code 
              WHERE r.id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $request = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($request && !empty($request['requested_by'])) {
        $phone = $request['requested_by'];
        $material = $request['material_name'] ?? 'material';
        
        // Prepare SMS message based on status
        if ($status === 'approved') {
            $message = "Restock request for $material APPROVED. Stock will be replenished shortly. - Factory Stock";
        } elseif ($status === 'rejected') {
            $message = "Restock request for $material REJECTED. Contact supervisor. - Factory Stock";
        } elseif ($status === 'fulfilled') {
            $message = "Restock request for $material FULFILLED. Stock has been replenished. - Factory Stock";
        } else {
            $message = "Restock request for $material updated to: " . strtoupper($status) . ". - Factory Stock";
        }
        
        // Send SMS via Africa's Talking API
        $username = $at_username;
        $api_key = $at_api_key;
        $from = $at_sender_id;
        
        $url = "https://api.africastalking.com/version1/messaging";
        $data = array(
            'username' => $username,
            'to' => $phone,
            'message' => $message,
            'from' => $from
        );
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . $api_key
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        error_log("SMS sent to $phone: " . $response);
    }
    
    header('Location: restocks.php?msg=updated');
    exit;
}

// Fetch all restock requests with material names
$query = "
    SELECT r.*, m.name as material_name, m.code as material_code 
    FROM restock_requests r 
    LEFT JOIN materials m ON r.material_code = m.code 
    ORDER BY r.created_at DESC
";
$result = mysqli_query($conn, $query);
$requests = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restock Requests — Factory Stock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="/mubeetech_icon.png">
    <style>
        /* ── Same as materials.php ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }
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
        .hamburger { display: none; background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; padding: 8px; }
        .hamburger:hover { background: rgba(255,255,255,0.08); border-radius: 8px; }

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
            margin-bottom: 2rem;
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

        .msg {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .msg-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }

        .card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #eef2f6;
            padding: 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .card h3 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th {
            text-align: left;
            padding: 12px 14px;
            background: #f1f5f9;
            font-weight: 600;
            color: #1e293b;
        }
        td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #fafbfc; }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-fulfilled { background: #dbeafe; color: #1e40af; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 8px;
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
        }
        .btn-success { background: #10b981; color: #fff; }
        .btn-success:hover { background: #059669; transform: translateY(-1px); }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-sm { padding: 4px 12px; font-size: 0.72rem; }
        .form-inline { display: inline-block; margin: 0 2px; }
        .empty-row td { text-align: center; color: #94a3b8; padding: 2rem 0; font-style: italic; }
        .no-action { font-size: 0.78rem; color: #94a3b8; }

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
            .hamburger { display: flex; }
            .main { margin-left: 0; padding: 1.5rem; }
            .main-header h1 { font-size: 1.4rem; }
        }
        @media (max-width: 480px) {
            .main { padding: 1rem; }
            .card { padding: 1.25rem; }
        }
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
            <a href="index.php"><i class="fas fa-chart-simple"></i> Dashboard</a>
            <a href="materials.php"><i class="fas fa-cubes"></i> Materials</a>
            <a href="restocks.php" class="active"><i class="fas fa-truck"></i> Restock Requests</a>
            <div class="logout">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="main-header">
            <h1>🚚 Restock Requests</h1>
            <div class="user"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></div>
        </div>

        <?php if ($msg === 'updated'): ?>
            <div class="msg msg-success"><i class="fas fa-check-circle"></i> Request status updated successfully.</div>
        <?php endif; ?>

        <div class="card">
            <h3>📋 All Requests</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Code</th>
                            <th>Requested By</th>
                            <th style="text-align:center;">Status</th>
                            <th>Date</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['material_name'] ?? 'Unknown') ?></td>
                            <td><strong><?= htmlspecialchars($r['material_code']) ?></strong></td>
                            <td><?= htmlspecialchars($r['requested_by']) ?></td>
                            <td style="text-align:center;">
                                <span class="badge badge-<?= $r['status'] ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></td>
                            <td style="text-align:center;">
                                <?php if ($r['status'] === 'pending'): ?>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                                <?php elseif ($r['status'] === 'approved'): ?>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="status" value="fulfilled">
                                    <button type="submit" class="btn btn-primary btn-sm">Fulfill</button>
                                </form>
                                <?php else: ?>
                                <span class="no-action">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                        <tr class="empty-row"><td colspan="6">No restock requests yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar nav').classList.toggle('open');
        });
    </script>
</body>
</html>