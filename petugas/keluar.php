<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$data_kendaraan = null;
$show_payment = false;

// =======================
// CARI KENDARAAN (PAKAI KODE TIKET)
// =======================
if (isset($_POST['cari'])) {
    $kode_tiket = mysqli_real_escape_string($conn, $_POST['kode_tiket']);

    $query = "SELECT k.*, t.tarif_per_jam 
              FROM kendaraan k
              JOIN tarif t ON k.jenis_kendaraan = t.jenis_kendaraan
              WHERE k.kode_tiket = '$kode_tiket' AND k.status = 'parkir'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $data_kendaraan = mysqli_fetch_assoc($result);

        // hitung durasi
        $waktu_masuk = strtotime($data_kendaraan['waktu_masuk']);
        $waktu_keluar = time();
        $durasi_detik = $waktu_keluar - $waktu_masuk;
        $durasi_jam = ceil($durasi_detik / 3600);

        $biaya = $durasi_jam * $data_kendaraan['tarif_per_jam'];

        $data_kendaraan['durasi_jam'] = $durasi_jam;
        $data_kendaraan['biaya'] = $biaya;
        $data_kendaraan['waktu_keluar_formatted'] = date('Y-m-d H:i:s');

        $show_payment = true;
    } else {
        $error = "Kode tiket tidak ditemukan!";
    }
}

// =======================
// PROSES BAYAR
// =======================
if (isset($_POST['bayar'])) {
    $id = (int)$_POST['id'];
    $durasi = (int)$_POST['durasi'];
    $biaya = (int)$_POST['biaya'];
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $area_parkir = $_POST['area_parkir'];
    $waktu_keluar = $_POST['waktu_keluar'];
    $petugas_id = $_SESSION['user_id'];

    // update kendaraan
    $query = "UPDATE kendaraan SET 
              waktu_keluar = '$waktu_keluar',
              durasi = $durasi,
              biaya = $biaya,
              status = 'keluar',
              petugas_keluar = $petugas_id
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {

        // update kapasitas
        mysqli_query($conn, "
        UPDATE kapasitas 
        SET slot_terisi = slot_terisi - 1 
        WHERE jenis_kendaraan = '$jenis_kendaraan' 
        AND area_parkir = '$area_parkir'
        ");

        header("Location: struk.php?id=$id");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kendaraan Keluar</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
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
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            --amber: #d97706;
            --amber-light: #fef3c7;
            --red: #dc2626;
            --red-light: #fee2e2;
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
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-icon {
            width: 36px;
            height: 36px;
            background: var(--blue);
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

        .header h1 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
        }

        .btn-back {
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            transition: all 0.15s;
        }

        .btn-back:hover {
            background: var(--bg);
            color: var(--text);
        }

        /* LAYOUT */
        .container {
            max-width: 640px;
            margin: 0 auto;
            padding: 28px 32px;
        }

        /* CARD */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-header-icon svg {
            width: 14px;
            height: 14px;
        }

        .card-header h2 {
            font-size: 13px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        /* FORM CARI */
        .search-row {
            display: flex;
            gap: 10px;
        }

        .input-scan {
            flex: 1;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            letter-spacing: 0.05em;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: var(--text);
            outline: none;
            transition: border 0.15s, box-shadow 0.15s;
            text-transform: uppercase;
        }

        .input-scan:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-cari {
            padding: 11px 20px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s;
        }

        .btn-cari:hover {
            background: #1d4ed8;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .alert-error {
            background: var(--red-light);
            color: var(--red);
            border: 1px solid #fecaca;
        }

        /* DETAIL PEMBAYARAN */
        .detail-grid {
            display: grid;
            gap: 1px;
            background: var(--border);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            padding: 11px 14px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        .detail-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .detail-value.kode {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            letter-spacing: 0.08em;
        }

        .detail-value.biaya {
            font-size: 18px;
            font-weight: 700;
            color: var(--green);
            letter-spacing: -0.02em;
        }

        .tag {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .tag-motor {
            background: var(--amber-light);
            color: #92400e;
        }

        .tag-mobil {
            background: var(--blue-light);
            color: #1e40af;
        }

        .btn-bayar {
            width: 100%;
            padding: 13px;
            background: var(--green);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            letter-spacing: 0.01em;
        }

        .btn-bayar:hover {
            background: var(--green-dark);
        }

        .btn-bayar:active {
            transform: scale(0.98);
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .empty-icon {
            font-size: 32px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 13px;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="header-brand">
            <div class="header-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h1>Kendaraan Keluar</h1>
        </div>
        <a href="index.php" class="btn-back">← Dashboard</a>
    </header>

    <div class="container">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <!-- FORM CARI -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#dbeafe;">
                    <svg viewBox="0 0 24 24" style="fill:#2563eb">
                        <path d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <h2>Scan / Input Kode Tiket</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="search-row">
                        <input class="input-scan" type="text" name="kode_tiket"
                            placeholder="Scan atau ketik kode tiket..."
                            value="<?= isset($_POST['kode_tiket']) ? htmlspecialchars($_POST['kode_tiket']) : ''; ?>"
                            required autofocus>
                        <button class="btn-cari" name="cari">Cari</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DETAIL PEMBAYARAN -->
        <?php if ($show_payment && $data_kendaraan): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:#dcfce7;">
                        <svg viewBox="0 0 24 24" style="fill:#15803d">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2>Detail Pembayaran</h2>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-row">
                            <span class="detail-label">Kode Tiket</span>
                            <span class="detail-value kode"><?= $data_kendaraan['kode_tiket']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jenis Kendaraan</span>
                            <span class="tag <?= $data_kendaraan['jenis_kendaraan'] === 'motor' ? 'tag-motor' : 'tag-mobil'; ?>"><?= strtoupper($data_kendaraan['jenis_kendaraan']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Area Parkir</span>
                            <span class="detail-value">Lahan <?= $data_kendaraan['area_parkir']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Waktu Masuk</span>
                            <span class="detail-value"><?= date('d/m/Y H:i', strtotime($data_kendaraan['waktu_masuk'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Durasi Parkir</span>
                            <span class="detail-value"><?= $data_kendaraan['durasi_jam']; ?> Jam</span>
                        </div>
                        <div class="detail-row" style="background:#f0fdf4;">
                            <span class="detail-label" style="color:#15803d;font-weight:600;">Total Biaya</span>
                            <span class="detail-value biaya">Rp <?= number_format($data_kendaraan['biaya'], 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $data_kendaraan['id']; ?>">
                        <input type="hidden" name="durasi" value="<?= $data_kendaraan['durasi_jam']; ?>">
                        <input type="hidden" name="biaya" value="<?= $data_kendaraan['biaya']; ?>">
                        <input type="hidden" name="jenis_kendaraan" value="<?= $data_kendaraan['jenis_kendaraan']; ?>">
                        <input type="hidden" name="area_parkir" value="<?= $data_kendaraan['area_parkir']; ?>">
                        <input type="hidden" name="waktu_keluar" value="<?= $data_kendaraan['waktu_keluar_formatted']; ?>">
                        <button class="btn-bayar" name="bayar">Bayar & Cetak Struk</button>
                    </form>
                </div>
            </div>

        <?php elseif (!$show_payment): ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <p>Masukkan kode tiket untuk melihat detail pembayaran</p>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>