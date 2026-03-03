<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$data_kendaraan = null;
$show_payment = false;

// Cari kendaraan berdasarkan plat nomor
if (isset($_POST['cari'])) {
    $plat_nomor = strtoupper(mysqli_real_escape_string($conn, $_POST['plat_nomor']));
    
    $query = "SELECT k.*, t.tarif_per_jam 
              FROM kendaraan k
              JOIN tarif t ON k.jenis_kendaraan = t.jenis_kendaraan
              WHERE k.plat_nomor = '$plat_nomor' AND k.status = 'parkir'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $data_kendaraan = mysqli_fetch_assoc($result);
        
        // Hitung durasi dan biaya
        $waktu_masuk = strtotime($data_kendaraan['waktu_masuk']);
        $waktu_keluar = time();
        $durasi_detik = $waktu_keluar - $waktu_masuk;
        $durasi_jam = ceil($durasi_detik / 3600); // Pembulatan ke atas
        
        $biaya = $durasi_jam * $data_kendaraan['tarif_per_jam'];
        
        $data_kendaraan['durasi_jam'] = $durasi_jam;
        $data_kendaraan['biaya'] = $biaya;
        $data_kendaraan['waktu_keluar_formatted'] = date('Y-m-d H:i:s');
        
        $show_payment = true;
    } else {
        $error = "Plat nomor tidak ditemukan atau sudah keluar!";
    }
}

// Proses pembayaran
if (isset($_POST['bayar'])) {
    $id = (int)$_POST['id'];
    $durasi = (int)$_POST['durasi'];
    $biaya = (int)$_POST['biaya'];
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $waktu_keluar = mysqli_real_escape_string($conn, $_POST['waktu_keluar']);
    $petugas_id = $_SESSION['user_id'];
    
    // Update data kendaraan
    $query = "UPDATE kendaraan SET 
              waktu_keluar = '$waktu_keluar',
              durasi = $durasi,
              biaya = $biaya,
              status = 'keluar',
              petugas_keluar = $petugas_id
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        // Update kapasitas
        mysqli_query($conn, "UPDATE kapasitas SET slot_terisi = slot_terisi - 1 WHERE jenis_kendaraan = '$jenis_kendaraan'");
        
        // Redirect ke halaman cetak struk
        header("Location: struk.php?id=$id");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kendaraan Keluar - Petugas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #28a745; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; background: #218838; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input { padding: 10px; border: 1px solid #ddd; border-radius: 3px; width: 100%; font-size: 16px; }
        button { padding: 12px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background: #218838; }
        .btn-bayar { background: #007bff; width: 100%; }
        .btn-bayar:hover { background: #0056b3; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 3px; margin-bottom: 15px; font-weight: bold; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: bold; color: #666; }
        .detail-value { color: #333; }
        .total { background: #f8f9fa; padding: 15px; margin-top: 15px; border-radius: 3px; }
        .total h3 { color: #007bff; font-size: 24px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kendaraan Keluar</h1>
        <a href="index.php">← Kembali</a>
    </div>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Cari Kendaraan</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Plat Nomor</label>
                    <input type="text" name="plat_nomor" placeholder="Contoh: B1234XYZ" required autofocus>
                </div>
                <button type="submit" name="cari">CARI KENDARAAN</button>
            </form>
        </div>
        
        <?php if ($show_payment && $data_kendaraan): ?>
        <div class="card">
            <h3>Detail Pembayaran</h3>
            
            <div class="detail-row">
                <span class="detail-label">Plat Nomor</span>
                <span class="detail-value"><?php echo $data_kendaraan['plat_nomor']; ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Jenis Kendaraan</span>
                <span class="detail-value"><?php echo strtoupper($data_kendaraan['jenis_kendaraan']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Waktu Masuk</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($data_kendaraan['waktu_masuk'])); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Waktu Keluar</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i'); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Durasi Parkir</span>
                <span class="detail-value"><?php echo $data_kendaraan['durasi_jam']; ?> Jam</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Tarif Per Jam</span>
                <span class="detail-value">Rp <?php echo number_format($data_kendaraan['tarif_per_jam'], 0, ',', '.'); ?></span>
            </div>
            
            <div class="total">
                <h3>TOTAL BAYAR: Rp <?php echo number_format($data_kendaraan['biaya'], 0, ',', '.'); ?></h3>
            </div>
            
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="id" value="<?php echo $data_kendaraan['id']; ?>">
                <input type="hidden" name="durasi" value="<?php echo $data_kendaraan['durasi_jam']; ?>">
                <input type="hidden" name="biaya" value="<?php echo $data_kendaraan['biaya']; ?>">
                <input type="hidden" name="jenis_kendaraan" value="<?php echo $data_kendaraan['jenis_kendaraan']; ?>">
                <input type="hidden" name="waktu_keluar" value="<?php echo $data_kendaraan['waktu_keluar_formatted']; ?>">
                <button type="submit" name="bayar" class="btn-bayar">BAYAR & CETAK STRUK</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
