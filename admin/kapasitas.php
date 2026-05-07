<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $total_slot = (int)$_POST['total_slot'];
    if ($total_slot < 0) {
        $error = "Total slot tidak boleh minus!";
    } else {
        $query = "UPDATE kapasitas SET total_slot = $total_slot WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success = "Kapasitas berhasil diupdate!";
        } else {
            $error = "Gagal update kapasitas!";
        }
    }
}

$query = "SELECT * FROM kapasitas ORDER BY jenis_kendaraan, area_parkir";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kapasitas - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/kapasitas.css">
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="header-icon">
            <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <h1>Kelola Kapasitas</h1>
    </div>
    <a href="index.php" class="btn-back">← Dashboard</a>
</header>

<div class="container">

    <?php if ($success): ?>
    <div class="alert alert-success"><div class="alert-dot"></div><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error"><div class="alert-dot"></div><?= $error ?></div>
    <?php endif; ?>

    <p class="section-label">Data Kapasitas Parkir</p>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Area</th>
                    <th>Terisi / Total</th>
                    <th>Penggunaan</th>
                    <th>Atur Slot</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $kosong = $row['total_slot'] - $row['slot_terisi'];
                    $pct = $row['total_slot'] > 0 ? round(($row['slot_terisi'] / $row['total_slot']) * 100) : 0;
                    $cls = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
                ?>
                <tr>
                    <form method="POST">
                        <td><span class="tag <?= $row['jenis_kendaraan']==='motor' ? 'tag-motor' : 'tag-mobil' ?>"><?= strtoupper($row['jenis_kendaraan']) ?></span></td>
                        <td><span class="tag tag-area">Lahan <?= $row['area_parkir'] ?></span></td>
                        <td>
                            <strong><?= $row['slot_terisi'] ?></strong>
                            <span style="color:var(--muted)"> / <?= $row['total_slot'] ?></span>
                            <span style="font-size:11px;color:var(--muted);margin-left:4px">(<?= $kosong ?> kosong)</span>
                        </td>
                        <td>
                            <div class="progress-wrap">
                                <div class="progress-bar"><div class="progress-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div></div>
                                <span class="progress-pct"><?= $pct ?>%</span>
                            </div>
                        </td>
                        <td>
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input class="slot-input" type="number" name="total_slot" value="<?= $row['total_slot'] ?>" min="0" required>
                        </td>
                        <td>
                            <button class="btn-update" type="submit" name="update">Simpan</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>