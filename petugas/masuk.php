<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

// Proses kendaraan masuk
if (isset($_POST['simpan'])) {
    $plat_nomor = strtoupper(mysqli_real_escape_string($conn, $_POST['plat_nomor']));
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $waktu_masuk = date('Y-m-d H:i:s');
    $petugas_id = $_SESSION['user_id'];
    
    // Cek apakah plat nomor sudah ada yang parkir
    $cek = mysqli_query($conn, "SELECT * FROM kendaraan WHERE plat_nomor = '$plat_nomor' AND status = 'parkir'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Plat nomor ini masih parkir!";
    } else {
        // Cek kapasitas
        $cek_kapasitas = mysqli_query($conn, "SELECT * FROM kapasitas WHERE jenis_kendaraan = '$jenis_kendaraan'");
        $kapasitas = mysqli_fetch_assoc($cek_kapasitas);
        
        if ($kapasitas['slot_terisi'] >= $kapasitas['total_slot']) {
            $error = "Kapasitas parkir " . strtoupper($jenis_kendaraan) . " sudah penuh!";
        } else {
            // Simpan data kendaraan
            $query = "INSERT INTO kendaraan (plat_nomor, jenis_kendaraan, waktu_masuk, petugas_masuk) 
                      VALUES ('$plat_nomor', '$jenis_kendaraan', '$waktu_masuk', $petugas_id)";
            
            if (mysqli_query($conn, $query)) {
                // Update kapasitas
                mysqli_query($conn, "UPDATE kapasitas SET slot_terisi = slot_terisi + 1 WHERE jenis_kendaraan = '$jenis_kendaraan'");
                $success = "Kendaraan berhasil masuk parkir!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Ambil data kendaraan yang sedang parkir
$query_parkir = "SELECT * FROM kendaraan WHERE status = 'parkir' ORDER BY waktu_masuk DESC LIMIT 10";
$result_parkir = mysqli_query($conn, $query_parkir);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kendaraan Masuk - Petugas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #28a745; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; background: #218838; padding: 8px 15px; border-radius: 3px; }
        .container { padding: 30px; max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 3px; width: 100%; font-size: 16px; }
        button { padding: 12px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 3px; margin-bottom: 15px; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 3px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kendaraan Masuk</h1>
        <a href="index.php">← Kembali</a>
    </div>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Input Kendaraan Masuk</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Plat Nomor</label>
                    <input type="text" name="plat_nomor" placeholder="Contoh: B1234XYZ" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" required>
                        <option value="">-- Pilih --</option>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                    </select>
                </div>
                
                <button type="submit" name="simpan">SIMPAN - KENDARAAN MASUK</button>
            </form>
        </div>
        
        <div class="card">
            <h3>10 Kendaraan Terakhir Masuk</h3>
            <table>
                <tr>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Waktu Masuk</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result_parkir)): ?>
                <tr>
                    <td><?php echo $row['plat_nomor']; ?></td>
                    <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
