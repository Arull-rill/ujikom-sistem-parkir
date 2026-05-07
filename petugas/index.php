<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

// =======================
// MOTOR A
// =======================
$motor_a_terisi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM kendaraan 
     WHERE jenis_kendaraan='motor' AND area_parkir='A' AND status='parkir'"
));

$motor_a_kapasitas = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT total_slot FROM kapasitas 
     WHERE jenis_kendaraan='motor' AND area_parkir='A'"
));

// =======================
// MOTOR B
// =======================
$motor_b_terisi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM kendaraan 
     WHERE jenis_kendaraan='motor' AND area_parkir='B' AND status='parkir'"
));

$motor_b_kapasitas = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT total_slot FROM kapasitas 
     WHERE jenis_kendaraan='motor' AND area_parkir='B'"
));

// =======================
// MOBIL A
// =======================
$mobil_a_terisi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM kendaraan 
     WHERE jenis_kendaraan='mobil' AND area_parkir='A' AND status='parkir'"
));

$mobil_a_kapasitas = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT total_slot FROM kapasitas 
     WHERE jenis_kendaraan='mobil' AND area_parkir='A'"
));

// =======================
// MOBIL B
// =======================
$mobil_b_terisi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM kendaraan 
     WHERE jenis_kendaraan='mobil' AND area_parkir='B' AND status='parkir'"
));

$mobil_b_kapasitas = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT total_slot FROM kapasitas 
     WHERE jenis_kendaraan='mobil' AND area_parkir='B'"
));

// TOTAL PARKIR
$query_parkir = "SELECT COUNT(*) as total FROM kendaraan WHERE status = 'parkir'";
$result_parkir = mysqli_query($conn, $query_parkir);
$total_parkir = mysqli_fetch_assoc($result_parkir)['total'];

// =======================
// 20 KENDARAAN TERAKHIR MASUK
// =======================
$query_last = "SELECT * FROM kendaraan 
               WHERE status = 'parkir' 
               ORDER BY waktu_masuk DESC 
               LIMIT 20";

$result_last = mysqli_query($conn, $query_last);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Sistem Parkir</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --green: #16a34a;
            --green-light: #dcfce7;
            --green-dark: #15803d;
            --blue: #2563eb;
            --blue-light: #dbeafe;
            --amber: #d97706;
            --amber-light: #fef3c7;
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* HEADER */
        .header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-icon {
            width: 36px;
            height: 36px;
            background: var(--green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-icon svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .header-brand h1 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .header-brand span {
            font-size: 12px;
            color: var(--muted);
            font-weight: 400;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            background: var(--green-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--green-dark);
        }

        .user-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
        }

        .btn-logout {
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            transition: all 0.15s;
        }

        .btn-logout:hover {
            background: var(--bg);
            color: var(--text);
        }

        /* MAIN */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px 32px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 12px;
        }

        /* KAPASITAS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--green);
        }

        .stat-card.total::before {
            background: linear-gradient(90deg, var(--green), var(--blue));
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .stat-value .total-num {
            font-size: 26px;
            font-weight: 700;
            color: var(--green);
        }

        .stat-sub {
            font-size: 11px;
            color: var(--muted);
            margin-top: 6px;
        }

        .progress-bar {
            height: 4px;
            background: var(--border);
            border-radius: 99px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--green);
            border-radius: 99px;
            transition: width 0.4s ease;
        }

        .progress-fill.warn {
            background: var(--amber);
        }

        .progress-fill.full {
            background: #dc2626;
        }

        /* MENU */
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 28px;
        }

        .menu-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            text-decoration: none;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow);
            transition: all 0.2s;
        }

        .menu-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
            border-color: var(--green);
        }

        .menu-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .menu-icon.green {
            background: var(--green-light);
        }

        .menu-icon.blue {
            background: var(--blue-light);
        }

        .menu-icon svg {
            width: 22px;
            height: 22px;
        }

        .menu-icon.green svg {
            fill: var(--green-dark);
        }

        .menu-icon.blue svg {
            fill: var(--blue);
        }

        .menu-text h3 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .menu-text p {
            font-size: 12px;
            color: var(--muted);
        }

        .menu-arrow {
            margin-left: auto;
            color: var(--border);
            font-size: 18px;
        }

        /* TABLE CARD */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h2 {
            font-size: 14px;
            font-weight: 600;
        }

        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 99px;
            background: var(--green-light);
            color: var(--green-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            padding: 10px 20px;
            background: #f8fafc;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        tbody td {
            padding: 11px 20px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background: #f8fafc;
        }

        .td-plat {
            font-family: monospace;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            background: var(--bg);
            padding: 3px 8px;
            border-radius: 5px;
            display: inline-block;
        }

        .tag {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 5px;
            display: inline-block;
        }

        .tag-motor {
            background: var(--amber-light);
            color: #92400e;
        }

        .tag-mobil {
            background: var(--blue-light);
            color: #1e40af;
        }

        .tag-area {
            background: var(--green-light);
            color: var(--green-dark);
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header class="header">
        <div class="header-brand">
            <div class="header-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 11l1.5-4.5h11L19 11M17.5 16a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm-9 0a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zM3 11h18v5H3z" />
                </svg>
            </div>
            <div>
                <h1>Sistem Parkir</h1>
                <span>Dashboard Petugas</span>
            </div>
        </div>
        <div class="header-right">
            <div class="header-user">
                <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)); ?></div>
                <span class="user-name"><?= $_SESSION['nama']; ?></span>
            </div>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </header>

    <div class="container">

        <!-- KAPASITAS -->
        <p class="section-label">Kapasitas Parkir</p>
        <div class="stats-grid">

            <?php
            $slots = [
                ['label' => 'Motor — Lahan A', 'terisi' => $motor_a_terisi['total'], 'total' => $motor_a_kapasitas['total_slot'] ?? 0],
                ['label' => 'Motor — Lahan B', 'terisi' => $motor_b_terisi['total'], 'total' => $motor_b_kapasitas['total_slot'] ?? 0],
                ['label' => 'Mobil — Lahan A', 'terisi' => $mobil_a_terisi['total'], 'total' => $mobil_a_kapasitas['total_slot'] ?? 0],
                ['label' => 'Mobil — Lahan B', 'terisi' => $mobil_b_terisi['total'], 'total' => $mobil_b_kapasitas['total_slot'] ?? 0],
            ];
            foreach ($slots as $s):
                $pct = $s['total'] > 0 ? round(($s['terisi'] / $s['total']) * 100) : 0;
                $cls = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
            ?>
                <div class="stat-card">
                    <div class="stat-label"><?= $s['label']; ?></div>
                    <div class="stat-value"><?= $s['terisi']; ?><span style="font-size:14px;font-weight:500;color:var(--muted)"> / <?= $s['total']; ?></span></div>
                    <div class="progress-bar">
                        <div class="progress-fill <?= $cls; ?>" style="width:<?= $pct; ?>%"></div>
                    </div>
                    <div class="stat-sub"><?= $pct; ?>% terisi</div>
                </div>
            <?php endforeach; ?>

            <div class="stat-card total">
                <div class="stat-label">Total Kendaraan</div>
                <div class="stat-value"><span class="total-num"><?= $total_parkir; ?></span></div>
                <div class="stat-sub">sedang parkir</div>
            </div>

        </div>

        <!-- MENU -->
        <p class="section-label">Transaksi</p>
        <div class="menu-grid">
            <a href="masuk.php" class="menu-card">
                <div class="menu-icon green">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="menu-text">
                    <h3>Kendaraan Masuk</h3>
                    <p>Input kendaraan baru masuk parkir</p>
                </div>
                <span class="menu-arrow">→</span>
            </a>

            <a href="keluar.php" class="menu-card">
                <div class="menu-icon blue">
                    <svg viewBox="0 0 24 24">
                        <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="menu-text">
                    <h3>Kendaraan Keluar</h3>
                    <p>Proses pembayaran &amp; cetak struk</p>
                </div>
                <span class="menu-arrow">→</span>
            </a>
        </div>

        <!-- TABLE -->
        <div class="table-card">
            <div class="table-header">
                <h2>20 Kendaraan Terakhir Masuk</h2>
                <span class="badge">Live</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Kode Tiket</th>
                        <th>Jenis</th>
                        <th>Area</th>
                        <th>Waktu Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result_last)): ?>
                        <tr>
                            <td><span class="mono"><?= $row['kode_tiket']; ?></span></td>
                            <td><span class="tag <?= $row['jenis_kendaraan'] === 'motor' ? 'tag-motor' : 'tag-mobil'; ?>"><?= strtoupper($row['jenis_kendaraan']); ?></span></td>
                            <td><span class="tag tag-area">Lahan <?= $row['area_parkir']; ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>