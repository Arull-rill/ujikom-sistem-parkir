<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';

// Update Tarif
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $tarif = (int)$_POST['tarif'];
    
    $query = "UPDATE tarif SET tarif_per_jam = $tarif WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Tarif berhasil diupdate!";
    }
}

// Ambil data tarif
$query = "SELECT * FROM tarif";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Tarif - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; background: #0056b3; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        input { padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kelola Tarif Parkir</h1>
        <a href="index.php">← Kembali</a>
    </div>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Tarif Parkir Per Jam</h3>
            <table>
                <tr>
                    <th>Jenis Kendaraan</th>
                    <th>Tarif Per Jam (Rp)</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <form method="POST">
                        <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                        <td>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="tarif" value="<?php echo $row['tarif_per_jam']; ?>" required>
                        </td>
                        <td>
                            <button type="submit" name="update">Update</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
