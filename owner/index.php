<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../index.php");
    exit();
}

// Default tanggal hari ini
$tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-d');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');

// Query total pendapatan
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

// Query pendapatan per hari
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
<html>
<head>
    <title>Dashboard Owner - Sistem Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #6c757d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .header a { color: white; text-decoration: none; background: #5a6268; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; }
        .filter { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter form { display: flex; gap: 15px; align-items: end; }
        .filter .form-group { flex: 1; }
        .filter label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        .filter input { padding: 10px; border: 1px solid #ddd; border-radius: 3px; width: 100%; }
        .filter button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-box h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-box .number { font-size: 28px; font-weight: bold; color: #6c757d; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Owner</h1>
        <div>
            <span>Halo, <?php echo $_SESSION['nama']; ?></span> | 
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="filter">
            <form method="GET">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="tanggal_dari" value="<?php echo $tanggal_dari; ?>">
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="tanggal_sampai" value="<?php echo $tanggal_sampai; ?>">
                </div>
                <button type="submit">TAMPILKAN LAPORAN</button>
            </form>
        </div>
        
        <h2 style="margin-bottom: 20px;">Laporan Periode: <?php echo date('d/m/Y', strtotime($tanggal_dari)); ?> - <?php echo date('d/m/Y', strtotime($tanggal_sampai)); ?></h2>
        
        <div class="stats">
            <div class="stat-box">
                <h3>TOTAL PENDAPATAN</h3>
                <div class="number">Rp <?php echo number_format($laporan['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
            </div>
            
            <div class="stat-box">
                <h3>TOTAL TRANSAKSI</h3>
                <div class="number"><?php echo $laporan['total_transaksi'] ?? 0; ?></div>
            </div>
            
            <div class="stat-box">
                <h3>KENDARAAN MOTOR</h3>
                <div class="number"><?php echo $laporan['total_motor'] ?? 0; ?></div>
            </div>
            
            <div class="stat-box">
                <h3>KENDARAAN MOBIL</h3>
                <div class="number"><?php echo $laporan['total_mobil'] ?? 0; ?></div>
            </div>
        </div>
        
        <div class="card">
            <h3>Pendapatan Per Hari</h3>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Pendapatan</th>
                </tr>
                <?php 
                if (mysqli_num_rows($result_harian) > 0):
                    while ($row = mysqli_fetch_assoc($result_harian)): 
                ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo $row['jumlah']; ?> Kendaraan</td>
                    <td>Rp <?php echo number_format($row['pendapatan'], 0, ',', '.'); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: #999;">Tidak ada data pada periode ini</td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>
