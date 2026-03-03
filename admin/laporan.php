<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Hitung total pendapatan
$query = "SELECT SUM(biaya) as total_pendapatan, COUNT(*) as total_transaksi
          FROM kendaraan 
          WHERE DATE(waktu_keluar) = '$tanggal' AND status = 'keluar'";
$result = mysqli_query($conn, $query);
$laporan = mysqli_fetch_assoc($result);

// Data detail
$query_detail = "SELECT * FROM kendaraan 
                 WHERE DATE(waktu_keluar) = '$tanggal' AND status = 'keluar'
                 ORDER BY waktu_keluar DESC";
$result_detail = mysqli_query($conn, $query_detail);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; background: #0056b3; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter { margin-bottom: 20px; }
        .filter input { padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .filter button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .stat-box h4 { color: #666; font-size: 14px; margin-bottom: 5px; }
        .stat-box .number { font-size: 24px; font-weight: bold; color: #007bff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pendapatan</h1>
        <a href="index.php">← Kembali</a>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="filter">
                <form method="GET">
                    <label>Pilih Tanggal:</label>
                    <input type="date" name="tanggal" value="<?php echo $tanggal; ?>">
                    <button type="submit">Tampilkan</button>
                </form>
            </div>
            
            <div class="stats">
                <div class="stat-box">
                    <h4>TOTAL PENDAPATAN</h4>
                    <div class="number">Rp <?php echo number_format($laporan['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                </div>
                
                <div class="stat-box">
                    <h4>TOTAL TRANSAKSI</h4>
                    <div class="number"><?php echo $laporan['total_transaksi'] ?? 0; ?> Kendaraan</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>Detail Transaksi Tanggal <?php echo date('d/m/Y', strtotime($tanggal)); ?></h3>
            <table>
                <tr>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Durasi (jam)</th>
                    <th>Biaya</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result_detail)): ?>
                <tr>
                    <td><?php echo $row['plat_nomor']; ?></td>
                    <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                    <td><?php echo date('H:i', strtotime($row['waktu_masuk'])); ?></td>
                    <td><?php echo date('H:i', strtotime($row['waktu_keluar'])); ?></td>
                    <td><?php echo $row['durasi']; ?> jam</td>
                    <td>Rp <?php echo number_format($row['biaya'], 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
