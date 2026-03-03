<?php
session_start();
include "koneksi.php";

if (isset($_POST['register'])) {

    $username = htmlspecialchars($_POST['username']);
    $password = md5($_POST['password']);
    $nama     = htmlspecialchars($_POST['nama']);
    $role     = $_POST['role'];

    // Cek username sudah ada atau belum
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        $query = "INSERT INTO users (username, password, role, nama) 
                  VALUES ('$username', '$password', '$role', '$nama')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Register berhasil!'); window.location='login.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Register</h2>

<form method="POST">
    <input type="text" name="nama" placeholder="Nama Lengkap" required><br><br>
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <select name="role" required>
        <option value="">Pilih Role</option>
        <option value="admin">Admin</option>
        <option value="petugas">Petugas</option>
        <option value="owner">Owner</option>
    </select><br><br>

    <button type="submit" name="register">Register</button>
</form>

<a href="login.php">Sudah punya akun? Login</a>

</body>
</html>
