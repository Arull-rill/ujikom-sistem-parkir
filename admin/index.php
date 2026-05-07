<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$motor_a = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_slot) as total_slot, SUM(slot_terisi) as slot_terisi FROM kapasitas WHERE jenis_kendaraan='motor' AND area_parkir='A'"));
$motor_b = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_slot) as total_slot, SUM(slot_terisi) as slot_terisi FROM kapasitas WHERE jenis_kendaraan='motor' AND area_parkir='B'"));
$mobil_a = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_slot) as total_slot, SUM(slot_terisi) as slot_terisi FROM kapasitas WHERE jenis_kendaraan='mobil' AND area_parkir='A'"));
$mobil_b = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_slot) as total_slot, SUM(slot_terisi) as slot_terisi FROM kapasitas WHERE jenis_kendaraan='mobil' AND area_parkir='B'"));

$query_parkir = "SELECT COUNT(*) as total FROM kendaraan WHERE status = 'parkir'";
$result_parkir = mysqli_query($conn, $query_parkir);
$total_parkir = mysqli_fetch_assoc($result_parkir)['total'];

$query_log = "
SELECT k.plat_nomor, k.jenis_kendaraan, k.area_parkir, k.waktu_masuk, k.waktu_keluar, k.status,
    u1.nama AS petugas_masuk, u2.nama AS petugas_keluar
FROM kendaraan k
LEFT JOIN users u1 ON k.petugas_masuk = u1.id
LEFT JOIN users u2 ON k.petugas_keluar = u2.id
ORDER BY k.created_at DESC LIMIT 10
";
$result_log = mysqli_query($conn, $query_log);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Parkir</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/dashboard.css">
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="header-icon">
            <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
            <h1>Sistem Parkir</h1>
            <span>Dashboard Admin</span>
        </div>
    </div>
    <div class="header-right">
        <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)); ?></div>
        <span class="user-name"><?= $_SESSION['nama']; ?></span>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
</header>

<div class="container">

    <p class="section-label">Kapasitas Parkir</p>
    <div class="stats-grid">
        <?php
        $slots = [
            ['label' => 'Motor — Lahan A', 'terisi' => $motor_a['slot_terisi'] ?? 0, 'total' => $motor_a['total_slot'] ?? 0],
            ['label' => 'Motor — Lahan B', 'terisi' => $motor_b['slot_terisi'] ?? 0, 'total' => $motor_b['total_slot'] ?? 0],
            ['label' => 'Mobil — Lahan A', 'terisi' => $mobil_a['slot_terisi'] ?? 0, 'total' => $mobil_a['total_slot'] ?? 0],
            ['label' => 'Mobil — Lahan B', 'terisi' => $mobil_b['slot_terisi'] ?? 0, 'total' => $mobil_b['total_slot'] ?? 0],
        ];
        foreach ($slots as $s):
            $pct = $s['total'] > 0 ? round(($s['terisi'] / $s['total']) * 100) : 0;
            $cls = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
        ?>
        <div class="stat-card">
            <div class="stat-label"><?= $s['label']; ?></div>
            <div class="stat-value"><?= $s['terisi']; ?><span style="font-size:13px;font-weight:500;color:var(--muted)"> / <?= $s['total']; ?></span></div>
            <div class="progress-bar"><div class="progress-fill <?= $cls; ?>" style="width:<?= $pct; ?>%"></div></div>
            <div class="stat-sub"><?= $pct; ?>% terisi</div>
        </div>
        <?php endforeach; ?>
        <div class="stat-card total">
            <div class="stat-label">Total Kendaraan</div>
            <div class="stat-value"><span class="total-num"><?= $total_parkir; ?></span></div>
            <div class="stat-sub">sedang parkir</div>
        </div>
    </div>

    <p class="section-label">Menu Utama</p>
    <div class="menu-grid">
        <a href="users.php" class="menu-card">
            <div class="menu-icon blue"><svg viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg></div>
            <div class="menu-text"><h3>Kelola User</h3><p>Tambah, edit, hapus user</p></div>
        </a>
        <a href="tarif.php" class="menu-card">
            <div class="menu-icon green"><svg viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="menu-text"><h3>Kelola Tarif</h3><p>Atur tarif parkir</p></div>
        </a>
        <a href="kapasitas.php" class="menu-card">
            <div class="menu-icon amber"><svg viewBox="0 0 24 24"><path d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7zm8 1v8M8 12h8"/></svg></div>
            <div class="menu-text"><h3>Kelola Kapasitas</h3><p>Atur kapasitas parkir</p></div>
        </a>
        <a href="kendaraan.php" class="menu-card">
            <div class="menu-icon purple"><svg viewBox="0 0 24 24"><path d="M5 11l1.5-4.5h11L19 11M17.5 16a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm-9 0a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zM3 11h18v5H3z"/></svg></div>
            <div class="menu-text"><h3>Data Kendaraan</h3><p>Lihat semua data</p></div>
        </a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h2>Log Aktivitas Kendaraan</h2>
            <span class="live-badge">10 Terbaru</span>
        </div>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Plat</th><th>Jenis</th><th>Area</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Petugas Masuk</th><th>Petugas Keluar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_log)): ?>
                <tr>
                    <td><span class="plat"><?= $row['plat_nomor']; ?></span></td>
                    <td><span class="tag <?= $row['jenis_kendaraan'] === 'motor' ? 'tag-motor' : 'tag-mobil'; ?>"><?= strtoupper($row['jenis_kendaraan']); ?></span></td>
                    <td><span class="tag tag-area">Lahan <?= $row['area_parkir']; ?></span></td>
                    <td style="font-size:12px;color:var(--muted)"><?= date('d/m H:i', strtotime($row['waktu_masuk'])); ?></td>
                    <td style="font-size:12px;color:var(--muted)"><?= $row['waktu_keluar'] ? date('d/m H:i', strtotime($row['waktu_keluar'])) : '—'; ?></td>
                    <td><span class="tag <?= $row['status'] == 'parkir' ? 'tag-parkir' : 'tag-keluar'; ?>"><?= strtoupper($row['status']); ?></span></td>
                    <td style="font-size:12px"><?= $row['petugas_masuk'] ?? '—'; ?></td>
                    <td style="font-size:12px"><?= $row['petugas_keluar'] ?? '—'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>
</body>
</html>