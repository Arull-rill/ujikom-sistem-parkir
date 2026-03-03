<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil data statistik
$query_motor = "SELECT * FROM kapasitas WHERE jenis_kendaraan = 'motor'";
$result_motor = mysqli_query($conn, $query_motor);
$kapasitas_motor = mysqli_fetch_assoc($result_motor);

$query_mobil = "SELECT * FROM kapasitas WHERE jenis_kendaraan = 'mobil'";
$result_mobil = mysqli_query($conn, $query_mobil);
$kapasitas_mobil = mysqli_fetch_assoc($result_mobil);

$query_parkir = "SELECT COUNT(*) as total FROM kendaraan WHERE status = 'parkir'";
$result_parkir = mysqli_query($conn, $query_parkir);
$total_parkir = mysqli_fetch_assoc($result_parkir)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin - Sistem Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .header a { color: white; text-decoration: none; background: #0056b3; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-box h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-box .number { font-size: 32px; font-weight: bold; color: #007bff; }
        .menu { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .menu-item { background: white; padding: 30px; text-align: center; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-decoration: none; color: #333; }
        .menu-item:hover { background: #f0f0f0; }
        .menu-item h3 { margin-bottom: 10px; color: #007bff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin</h1>
        <div>
            <span>Halo, <?php echo $_SESSION['nama']; ?></span> | 
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2 style="margin-bottom: 20px;">Kapasitas Parkir</h2>
        
        <div class="stats">
            <div class="stat-box">
                <h3>MOTOR PARKIR</h3>
                <div class="number"><?php echo $kapasitas_motor['slot_terisi']; ?> / <?php echo $kapasitas_motor['total_slot']; ?></div>
            </div>
            
            <div class="stat-box">
                <h3>MOBIL PARKIR</h3>
                <div class="number"><?php echo $kapasitas_mobil['slot_terisi']; ?> / <?php echo $kapasitas_mobil['total_slot']; ?></div>
            </div>
            
            <div class="stat-box">
                <h3>TOTAL KENDARAAN</h3>
                <div class="number"><?php echo $total_parkir; ?></div>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px;">Menu Utama</h2>
        
        <div class="menu">
            <a href="users.php" class="menu-item">
                <h3>Kelola User</h3>
                <p>Tambah, edit, hapus user</p>
            </a>
            
            <a href="tarif.php" class="menu-item">
                <h3>Kelola Tarif</h3>
                <p>Atur tarif parkir</p>
            </a>
            
            <a href="kapasitas.php" class="menu-item">
                <h3>Kelola Kapasitas</h3>
                <p>Atur kapasitas parkir</p>
            </a>
            
            <a href="kendaraan.php" class="menu-item">
                <h3>Data Kendaraan</h3>
                <p>Lihat semua data kendaraan</p>
            </a>
            
            <a href="laporan.php" class="menu-item">
                <h3>Laporan</h3>
                <p>Laporan transaksi parkir</p>
            </a>
        </div>
    </div>
</body>
</html>
