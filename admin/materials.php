<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';

// Handle Add/Edit/Delete operations
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Delete material
if ($action === 'delete' && $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM materials WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: materials.php?msg=deleted');
    exit;
}

// Fetch single material for editing
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM materials WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Handle Add/Update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $threshold = intval($_POST['threshold'] ?? 10);
    $edit_id = intval($_POST['edit_id'] ?? 0);

    if ($edit_id) {
        $stmt = mysqli_prepare($conn, "UPDATE materials SET code = ?, name = ?, quantity = ?, threshold = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssiii", $code, $name, $quantity, $threshold, $edit_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: materials.php?msg=updated');
        exit;
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO materials (code, name, quantity, threshold) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssii", $code, $name, $quantity, $threshold);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: materials.php?msg=added');
        exit;
    }
}

// Fetch all materials
$result = mysqli_query($conn, "SELECT * FROM materials ORDER BY code ASC");
$materials = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Materials — Factory Stock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="/mubeetech_icon.png">
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
        .hamburger { display: none; background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; padding: 8px; }
        .hamburger:hover { background: rgba(255,255,255,0.08); border-radius: 8px; }

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

        /* ── MESSAGES ── */
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
        .msg-deleted { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .msg i { font-size: 1rem; }

        /* ── CARD ── */
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.25s, box-shadow 0.25s;
            background: #f8fafc;
            color: #1e293b;
            outline: none;
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
            background: #ffffff;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 10px;
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-2px); }
        .btn-success { background: #10b981; color: #fff; }
        .btn-success:hover { background: #059669; transform: translateY(-2px); }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; transform: translateY(-2px); }
        .btn-warning { background: #f59e0b; color: #fff; }
        .btn-warning:hover { background: #d97706; transform: translateY(-2px); }
        .btn-sm { padding: 6px 12px; font-size: 0.75rem; }

        /* ── TABLE ── */
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
        .empty-row td {
            text-align: center;
            color: #94a3b8;
            padding: 2rem 0;
            font-style: italic;
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
            .hamburger { display: flex; }
            .main { margin-left: 0; padding: 1.5rem; }
            .form-row { grid-template-columns: 1fr; }
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
            <a href="materials.php" class="active"><i class="fas fa-cubes"></i> Materials</a>
            <a href="restocks.php"><i class="fas fa-truck"></i> Restock Requests</a>
            <div class="logout">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="main-header">
            <h1>📦 Materials</h1>
            <div class="user"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></div>
        </div>

        <?php if ($msg === 'added'): ?>
            <div class="msg msg-success"><i class="fas fa-check-circle"></i> Material added successfully.</div>
        <?php elseif ($msg === 'updated'): ?>
            <div class="msg msg-success"><i class="fas fa-check-circle"></i> Material updated successfully.</div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="msg msg-deleted"><i class="fas fa-trash-alt"></i> Material deleted.</div>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <div class="card">
            <h3><?= $editData ? '✏️ Edit Material' : '➕ Add New Material' ?></h3>
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? 0 ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Material Code</label>
                        <input type="text" name="code" value="<?= htmlspecialchars($editData['code'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Material Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" value="<?= $editData['quantity'] ?? 0 ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Threshold (min stock alert)</label>
                        <input type="number" name="threshold" value="<?= $editData['threshold'] ?? 10 ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?= $editData ? 'Update' : 'Add' ?> Material</button>
                <?php if ($editData): ?>
                    <a href="materials.php" class="btn btn-warning">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Material List -->
        <div class="card">
            <h3>📋 All Materials</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th style="text-align:right;">Quantity</th>
                            <th style="text-align:center;">Threshold</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $m): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['code']) ?></strong></td>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td style="text-align:right;"><?= number_format($m['quantity']) ?></td>
                            <td style="text-align:center;"><?= $m['threshold'] ?></td>
                            <td style="text-align:center;">
                                <a href="materials.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="materials.php?action=delete&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this material?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($materials)): ?>
                        <tr class="empty-row"><td colspan="5">No materials added yet.</td></tr>
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