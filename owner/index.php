<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../index.php");
    exit();
}

$tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-d');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');

$query_total = "SELECT 
                SUM(biaya) as total_pendapatan,
                COUNT(*) as total_transaksi,
                SUM(CASE WHEN jenis_kendaraan = 'motor' THEN 1 ELSE 0 END) as total_motor,
                SUM(CASE WHEN jenis_kendaraan = 'mobil' THEN 1 ELSE 0 END) as total_mobil
                FROM kendaraan 
                WHERE DATE(waktu_keluar) BETWEEN '$tanggal_dari' AND '$tanggal_sampai' 
                AND status = 'keluar'";
$result_total = mysqli_query($conn, $query_total);
$laporan = mysqli_fetch_assoc($result_total);

$query_harian = "SELECT 
                 DATE(waktu_keluar) as tanggal,
                 SUM(biaya) as pendapatan,
                 COUNT(*) as jumlah
                 FROM kendaraan 
                 WHERE DATE(waktu_keluar) BETWEEN '$tanggal_dari' AND '$tanggal_sampai' 
                 AND status = 'keluar'
                 GROUP BY DATE(waktu_keluar)
                 ORDER BY tanggal DESC";
$result_harian = mysqli_query($conn, $query_harian);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - Sistem Parkir</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/dashboard.css">
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="header-icon">
            <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <h1>Sistem Parkir</h1>
            <span>Laporan Owner</span>
        </div>
    </div>
    <div class="header-right">
        <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)); ?></div>
        <span class="user-name"><?= $_SESSION['nama']; ?></span>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
</header>

<div class="container">

    <!-- FILTER -->
    <p class="section-label">Filter Periode</p>
    <div class="filter-card">
        <form method="GET">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="tanggal_dari" value="<?= $tanggal_dari; ?>">
                </div>
                <div class="filter-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="tanggal_sampai" value="<?= $tanggal_sampai; ?>">
                </div>
                <button type="submit" class="btn-filter">Tampilkan Laporan</button>
            </div>
        </form>
    </div>

    <!-- STATS -->
    <div class="periode-bar">
        <p class="section-label" style="margin-bottom:0">Ringkasan</p>
        <span class="periode-range">
            <?= date('d/m/Y', strtotime($tanggal_dari)); ?> — <?= date('d/m/Y', strtotime($tanggal_sampai)); ?>
        </span>
    </div>

    <div class="stats-grid">
        <div class="stat-card pendapatan">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value green">Rp <?= number_format($laporan['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
        </div>
        <div class="stat-card transaksi">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?= $laporan['total_transaksi'] ?? 0; ?></div>
        </div>
        <div class="stat-card motor">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg></div>
            <div class="stat-label">Motor</div>
            <div class="stat-value"><?= $laporan['total_motor'] ?? 0; ?></div>
        </div>
        <div class="stat-card mobil">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M5 11l1.5-4.5h11L19 11M17.5 16a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm-9 0a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zM3 11h18v5H3z"/></svg></div>
            <div class="stat-label">Mobil</div>
            <div class="stat-value"><?= $laporan['total_mobil'] ?? 0; ?></div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-header">
            <h2>Pendapatan Per Hari</h2>
            <a href="export_pdf.php?tanggal_dari=<?= $tanggal_dari; ?>&tanggal_sampai=<?= $tanggal_sampai; ?>" class="btn-export">Export PDF</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result_harian) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result_harian)): ?>
                    <tr>
                        <td class="tanggal-cell"><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td><span class="jumlah-badge"><?= $row['jumlah']; ?> Kendaraan</span></td>
                        <td class="pendapatan-cell">Rp <?= number_format($row['pendapatan'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">
                            <div class="empty-state">
                                <div class="empty-icon">📊</div>
                                <p>Tidak ada data pada periode ini</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>