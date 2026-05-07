<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $cekUser = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    if (mysqli_num_rows($cekUser) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password, role, nama, status) VALUES ('$username', '$password', '$role', '$nama', 'aktif')");
        $success = "User berhasil ditambahkan!";
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        $success = "User berhasil dihapus!";
    } else {
        $error = "Tidak bisa hapus akun sendiri!";
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    $edit = mysqli_fetch_assoc($q);
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];
    if (!empty($password)) {
        $password = md5($password);
        mysqli_query($conn, "UPDATE users SET username='$username', nama='$nama', role='$role', password='$password' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', nama='$nama', role='$role' WHERE id=$id");
    }
    $success = "User berhasil diupdate!";
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $cek = mysqli_query($conn, "SELECT status FROM users WHERE id=$id");
    $data = mysqli_fetch_assoc($cek);
    if ($data) {
        $statusBaru = ($data['status'] == 'aktif') ? 'nonaktif' : 'aktif';
        mysqli_query($conn, "UPDATE users SET status='$statusBaru' WHERE id=$id");
        $success = "Status user diubah!";
    }
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/pengguna.css">
</head>
<body>

<header class="header">
    <div class="header-brand">
        <div class="header-icon">
            <svg viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
        </div>
        <h1>Kelola User</h1>
    </div>
    <a href="index.php" class="btn-back">← Dashboard</a>
</header>

<div class="container">

    <?php if ($success): ?>
    <div class="alert alert-success"><div class="alert-dot"></div><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error"><div class="alert-dot"></div><?= $error ?></div>
    <?php endif; ?>

    <div class="layout">
        <!-- FORMS KIRI -->
        <div>
            <!-- FORM TAMBAH -->
            <p class="section-label">Tambah User Baru</p>
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:var(--green-light)">
                        <svg viewBox="0 0 24 24" style="fill:var(--green-dark)"><path d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h2>Form Tambah</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group"><label>Username</label><input type="text" name="username" placeholder="Username" required></div>
                        <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Password" required></div>
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" placeholder="Nama Lengkap" required></div>
                        <div class="form-group"><label>Role</label>
                            <select name="role">
                                <option value="admin">Admin</option>
                                <option value="petugas">Petugas</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <button name="tambah" class="btn btn-green">Tambah User</button>
                    </form>
                </div>
            </div>

            <!-- FORM EDIT -->
            <?php if ($edit): ?>
            <p class="section-label">Edit User</p>
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:var(--amber-light)">
                        <svg viewBox="0 0 24 24" style="fill:#92400e"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <h2>Edit: <?= $edit['username'] ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?= $edit['username'] ?>" required></div>
                        <div class="form-group"><label>Password Baru</label><input type="password" name="password" placeholder="Kosongkan jika tidak diubah"></div>
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" value="<?= $edit['nama'] ?>" required></div>
                        <div class="form-group"><label>Role</label>
                            <select name="role">
                                <option value="admin" <?= $edit['role']=='admin'?'selected':'' ?>>Admin</option>
                                <option value="petugas" <?= $edit['role']=='petugas'?'selected':'' ?>>Petugas</option>
                                <option value="owner" <?= $edit['role']=='owner'?'selected':'' ?>>Owner</option>
                            </select>
                        </div>
                        <button name="update" class="btn btn-amber">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- TABEL KANAN -->
        <div>
            <p class="section-label">Daftar User</p>
            <div class="card">
                <table>
                    <thead>
                        <tr><th>User</th><th>Role</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <span class="initials"><?= strtoupper(substr($row['nama'], 0, 2)); ?></span>
                                <span style="font-weight:600;font-size:13px"><?= $row['nama'] ?></span>
                                <br><span style="font-size:11px;color:var(--muted);margin-left:36px">@<?= $row['username'] ?></span>
                            </td>
                            <td>
                                <span class="tag tag-<?= $row['role'] ?>"><?= strtoupper($row['role']) ?></span>
                            </td>
                            <td>
                                <span class="tag <?= $row['status']=='aktif' ? 'tag-aktif' : 'tag-nonaktif' ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?= $row['id'] ?>" class="action-btn btn-edit">Edit</a>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="?hapus=<?= $row['id'] ?>" class="action-btn btn-hapus" onclick="return confirm('Yakin hapus user?')">Hapus</a>
                                        <a href="?toggle=<?= $row['id'] ?>" class="action-btn <?= $row['status']=='aktif' ? 'btn-toggle-aktif' : 'btn-toggle-nonaktif' ?>">
                                            <?= $row['status']=='aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>