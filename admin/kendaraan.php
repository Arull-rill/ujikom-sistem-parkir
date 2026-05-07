<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$query = "SELECT k.*, 
          u1.nama as nama_petugas_masuk,
          u2.nama as nama_petugas_keluar
          FROM kendaraan k
          LEFT JOIN users u1 ON k.petugas_masuk = u1.id
          LEFT JOIN users u2 ON k.petugas_keluar = u2.id
          ORDER BY k.id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kendaraan - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/kendaraan.css">
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="header-icon">
            <svg viewBox="0 0 24 24"><path d="M5 11l1.5-4.5h11L19 11M17.5 16a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm-9 0a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zM3 11h18v5H3z"/></svg>
        </div>
        <h1>Data Kendaraan</h1>
    </div>
    <div class="header-right">
        <a href="export_kendaraan.php" class="btn-export">Export PDF</a>
        <a href="index.php" class="btn-back">← Dashboard</a>
    </div>
</header>

<div class="container">
    <p class="section-label">Semua Data Kendaraan</p>
    <div class="card">
        <div class="overflow-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                    <th>Durasi</th>
                    <th>Biaya</th>
                    <th>Status</th>
                    <th>Petugas Masuk</th>
                    <th>Petugas Keluar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="id-cell">#<?= $row['id'] ?></td>
                    <td><span class="plat"><?= $row['plat_nomor'] ?></span></td>
                    <td><span class="tag <?= $row['jenis_kendaraan']==='motor' ? 'tag-motor' : 'tag-mobil' ?>"><?= strtoupper($row['jenis_kendaraan']) ?></span></td>
                    <td class="time-cell"><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
                    <td class="time-cell"><?= $row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '<span class="dash">—</span>' ?></td>
                    <td class="durasi-cell"><?= $row['durasi'] ? $row['durasi'].' jam' : '<span class="dash">—</span>' ?></td>
                    <td class="biaya-cell"><?= $row['biaya'] ? 'Rp '.number_format($row['biaya'],0,',','.') : '<span class="dash" style="font-weight:400">—</span>' ?></td>
                    <td><span class="tag tag-<?= $row['status'] ?>"><?= strtoupper($row['status']) ?></span></td>
                    <td class="petugas-cell"><?= $row['nama_petugas_masuk'] ?? '<span class="dash">—</span>' ?></td>
                    <td class="petugas-cell"><?= $row['nama_petugas_keluar'] ? $row['nama_petugas_keluar'] : '<span class="dash">—</span>' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>