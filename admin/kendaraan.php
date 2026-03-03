<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil data kendaraan
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
<html>
<head>
    <title>Data Kendaraan - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; background: #0056b3; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background: #f8f9fa; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 12px; }
        .badge-parkir { background: #28a745; color: white; }
        .badge-keluar { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Kendaraan</h1>
        <a href="index.php">← Kembali</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h3>Semua Data Kendaraan</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                    <th>Durasi (jam)</th>
                    <th>Biaya</th>
                    <th>Status</th>
                    <th>Petugas Masuk</th>
                    <th>Petugas Keluar</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['plat_nomor']; ?></td>
                    <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                    <td><?php echo $row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '-'; ?></td>
                    <td><?php echo $row['durasi'] ? $row['durasi'] : '-'; ?></td>
                    <td><?php echo $row['biaya'] ? 'Rp ' . number_format($row['biaya'], 0, ',', '.') : '-'; ?></td>
                    <td>
                        <span class="badge badge-<?php echo $row['status']; ?>">
                            <?php echo strtoupper($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $row['nama_petugas_masuk']; ?></td>
                    <td><?php echo $row['nama_petugas_keluar'] ? $row['nama_petugas_keluar'] : '-'; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
