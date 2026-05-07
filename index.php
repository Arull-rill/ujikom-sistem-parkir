<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // CEK STATUS
        if ($user['status'] != 'aktif') {
            $error = "Akun anda sudah dinonaktifkan!";
        } else {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];

            header("Location: " . $user['role'] . "/index.php");
            exit();
        }

    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Parkir Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* BACKGROUND PATTERN */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(22,163,74,0.07) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(37,99,235,0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
        }

        /* CARD */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        /* TOP ACCENT */
        .card-accent {
            height: 4px;
            background: linear-gradient(90deg, #16a34a, #22c55e);
        }

        .card-body {
            padding: 36px 36px 32px;
        }

        /* BRAND */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: #dcfce7;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .brand-icon svg {
            width: 28px;
            height: 28px;
            fill: #15803d;
        }

        .brand h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.03em;
            margin-bottom: 4px;
        }

        .brand p {
            font-size: 13px;
            color: #64748b;
            font-weight: 400;
        }

        /* ALERT */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #dc2626;
            flex-shrink: 0;
        }

        /* FORM */
        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            fill: #9ca3af;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 11px 12px 11px 38px;
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #0f172a;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #16a34a;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #cbd5e1;
        }

        /* BUTTON */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
            box-shadow: 0 1px 3px rgba(22,163,74,0.3);
        }

        button[type="submit"]:hover {
            background: #15803d;
            box-shadow: 0 4px 12px rgba(22,163,74,0.3);
        }

        button[type="submit"]:active {
            transform: scale(0.98);
        }

        /* FOOTER */
        .card-footer {
            padding: 14px 36px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }

        .card-footer p {
            font-size: 12px;
            color: #94a3b8;
        }

        .card-footer span {
            color: #16a34a;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">

            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24"><path d="M5 11l1.5-4.5h11L19 11M17.5 16a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm-9 0a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zM3 11h18v5H3z"/></svg>
                </div>
                <h1>Sistem Parkir</h1>
                <p>Masuk untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
            <div class="alert">
                <div class="alert-dot"></div>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input type="text" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit">Masuk</button>
            </form>

        </div>
        <div class="card-footer">
            <p>Sistem Parkir Siswa &nbsp;·&nbsp; <span>v1.0</span></p>
        </div>
    </div>
</div>

</body>
</html>