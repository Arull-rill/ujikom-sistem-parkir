<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: keluar.php");
    exit();
}

$id = (int)$_GET['id'];

// Ambil data transaksi
$query = "SELECT k.*, u1.nama as petugas_masuk, u2.nama as petugas_keluar
          FROM kendaraan k
          LEFT JOIN users u1 ON k.petugas_masuk = u1.id
          LEFT JOIN users u2 ON k.petugas_keluar = u2.id
          WHERE k.id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: keluar.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembayaran Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border: 2px dashed #333; }
        .struk { text-align: center; }
        .struk h2 { margin-bottom: 5px; }
        .struk h3 { margin-bottom: 20px; font-weight: normal; }
        .separator { border-top: 1px dashed #333; margin: 15px 0; }
        .row { display: flex; justify-content: space-between; margin: 8px 0; }
        .label { font-weight: bold; }
        .total { border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 10px 0; margin: 15px 0; }
        .total .amount { font-size: 24px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        .buttons { text-align: center; margin-top: 20px; }
        .btn { padding: 10px 20px; margin: 5px; cursor: pointer; border: none; border-radius: 3px; font-size: 14px; }
        .btn-print { background: #28a745; color: white; }
        .btn-back { background: #6c757d; color: white; }
        @media print {
            .buttons { display: none; }
            body { padding: 0; background: white; }
            .container { border: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="struk">
            <h2>SISTEM PARKIR SISWA</h2>
            <h3>STRUK PEMBAYARAN</h3>
            
            <div class="separator"></div>
            
            <div class="row">
                <span class="label">No. Transaksi</span>
                <span><?php echo str_pad($data['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <div class="row">
                <span class="label">Tanggal</span>
                <span><?php echo date('d/m/Y', strtotime($data['waktu_keluar'])); ?></span>
            </div>
            
            <div class="separator"></div>
            
            <div class="row">
                <span class="label">Plat Nomor</span>
                <span><?php echo $data['plat_nomor']; ?></span>
            </div>
            
            <div class="row">
                <span class="label">Jenis Kendaraan</span>
                <span><?php echo strtoupper($data['jenis_kendaraan']); ?></span>
            </div>
            
            <div class="separator"></div>
            
            <div class="row">
                <span class="label">Waktu Masuk</span>
                <span><?php echo date('d/m/Y H:i', strtotime($data['waktu_masuk'])); ?></span>
            </div>
            
            <div class="row">
                <span class="label">Waktu Keluar</span>
                <span><?php echo date('d/m/Y H:i', strtotime($data['waktu_keluar'])); ?></span>
            </div>
            
            <div class="row">
                <span class="label">Durasi Parkir</span>
                <span><?php echo $data['durasi']; ?> Jam</span>
            </div>
            
            <div class="total">
                <div class="row">
                    <span class="label">TOTAL BAYAR</span>
                    <span class="amount">Rp <?php echo number_format($data['biaya'], 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <div class="footer">
                <p>Petugas: <?php echo $data['petugas_keluar']; ?></p>
                <p style="margin-top: 10px;">Terima kasih atas kunjungan Anda</p>
                <p>Hati-hati di jalan!</p>
            </div>
        </div>
    </div>
    
    <div class="buttons">
        <button onclick="window.print()" class="btn btn-print">CETAK STRUK</button>
        <button onclick="window.location.href='keluar.php'" class="btn btn-back">KEMBALI</button>
    </div>
    
    <script>
        // Auto print saat halaman dibuka
        window.onload = function() {
            // Uncomment baris berikut jika ingin auto print
            // window.print();
        }
    </script>
</body>
</html>
