<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

$kode = '';
$success = '';
$error = '';

// =====================
// AMBIL TIKET
// =====================
if (isset($_POST['motor']) || isset($_POST['mobil'])) {

    // tentukan jenis
    if (isset($_POST['motor'])) {
        $jenis_kendaraan = 'motor';
    } else {
        $jenis_kendaraan = 'mobil';
    }

    // generate kode unik
    $kode = strtoupper(substr(md5(uniqid()), 0, 8));
    $waktu_masuk = date('Y-m-d H:i:s');
    $petugas_id = $_SESSION['user_id'];


    // cek kapasitas semua area
    $cek_area = mysqli_query($conn, "
    SELECT * FROM kapasitas 
    WHERE jenis_kendaraan = '$jenis_kendaraan'
    ORDER BY area_parkir ASC
");

    $area_parkir = null;

    while ($row = mysqli_fetch_assoc($cek_area)) {
        if ($row['slot_terisi'] < $row['total_slot']) {
            $area_parkir = $row['area_parkir'];
            break;
        }
    }

    // kalau semua penuh
    if (!$area_parkir) {
        $error = "Semua area parkir penuh!";
    } else {

        // lanjut cek kapasitas area terpilih
        $cek_kapasitas = mysqli_query($conn, "
        SELECT * FROM kapasitas 
        WHERE jenis_kendaraan = '$jenis_kendaraan' 
        AND area_parkir = '$area_parkir'
    ");

        $kapasitas = mysqli_fetch_assoc($cek_kapasitas);

        // simpan data
        $query = "INSERT INTO kendaraan 
    (kode_tiket, jenis_kendaraan, area_parkir, waktu_masuk, petugas_masuk) 
    VALUES 
    ('$kode', '$jenis_kendaraan', '$area_parkir', '$waktu_masuk', $petugas_id)";

        if (mysqli_query($conn, $query)) {

            // update kapasitas
            mysqli_query($conn, "
                UPDATE kapasitas 
                SET slot_terisi = slot_terisi + 1 
                WHERE jenis_kendaraan = '$jenis_kendaraan' 
                AND area_parkir = '$area_parkir'
            ");

            $success = "Tiket berhasil dibuat!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Ambil data kendaraan parkir
$query_parkir = "SELECT * FROM kendaraan WHERE status = 'parkir' ORDER BY waktu_masuk DESC LIMIT 10";
$result_parkir = mysqli_query($conn, $query_parkir);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesin Tiket Parkir</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="masuk.css">
</head>

<body>

    <header class="header">
        <div class="header-brand">
            <div class="header-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <h1>Mesin Tiket Parkir</h1>
        </div>
        <a href="index.php" class="btn-back">← Dashboard</a>
    </header>

    <div class="container">

        <!-- KIRI: MESIN -->
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="mesin-box">
                        <div class="mesin-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div class="mesin-title">Kendaraan Masuk</div>
                        <div class="mesin-sub">Tekan tombol untuk mencetak tiket</div>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success; ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-error"><?= $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <button name="motor">TIKET MOTOR</button>
                            <button name="mobil">TIKET MOBIL</button>
                        </form>
                    </div>

                    <?php if ($kode): ?>
                        <div class="tiket-wrapper no-print">
                            <div class="tiket">
                                <div class="tiket-title">TIKET PARKIR</div>
                                <hr class="tiket-divider">
                                <div class="tiket-info">KODE TIKET</div>
                                <div class="tiket-kode"><?= $kode; ?></div>
                                <hr class="tiket-divider">
                                <div class="tiket-info">
                                    Masuk: <?= date('d/m/Y H:i'); ?><br>
                                    Jenis: <?= strtoupper($jenis_kendaraan); ?> | Area: <?= $area_parkir; ?>
                                </div>
                                <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= $kode; ?>&code=Code128" alt="barcode">
                                <hr class="tiket-divider">
                                <div class="tiket-note">Simpan tiket ini. Hilang = denda.</div>
                            </div>
                            <button class="btn-print no-print" onclick="window.print()">Print Tiket</button>
                        </div>
                        <script>
                            window.onload = function() {
                                window.print();
                            }
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- KANAN: TABEL -->
        <div>
            <p class="section-label">Kendaraan Sedang Parkir (10 Terakhir)</p>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Jenis</th>
                            <th>Area</th>
                            <th>Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_parkir)): ?>
                            <tr>
                                <td><span class="mono"><?= $row['kode_tiket']; ?></span></td>
                                <td><span class="tag <?= $row['jenis_kendaraan'] === 'motor' ? 'tag-motor' : 'tag-mobil'; ?>"><?= strtoupper($row['jenis_kendaraan']); ?></span></td>
                                <td><?= $row['area_parkir']; ?></td>
                                <td><?= date('H:i', strtotime($row['waktu_masuk'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>