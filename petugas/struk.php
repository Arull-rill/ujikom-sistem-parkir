<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: keluar.php");
    exit();
}

$id = (int)$_GET['id'];

// Ambil data transaksi
$query = "SELECT k.*, u1.nama as petugas_masuk, u2.nama as petugas_keluar
          FROM kendaraan k
          LEFT JOIN users u1 ON k.petugas_masuk = u1.id
          LEFT JOIN users u2 ON k.petugas_keluar = u2.id
          WHERE k.id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: keluar.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Struk Pembayaran Parkir</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: monospace;
            background: #e5e5e5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 230px;
            background: #fff;
            padding: 8px;
        }

        .struk {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            /* BIKIN LEBIH TEBAL */
        }

        .title {
            font-size: 14px;
            font-weight: 900;
            /* super tebal */
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .total {
            border-top: 1px solid #000;
            margin-top: 5px;
            padding-top: 5px;
            font-size: 13px;
        }

        .amount {
            font-size: 18px;
            font-weight: 900;
        }

        .footer {
            font-size: 11px;
            margin-top: 5px;
        }

        .buttons {
            margin-top: 10px;
            text-align: center;
        }

        .btn {
            padding: 6px 10px;
            font-size: 12px;
            margin: 3px;
            border: none;
            cursor: pointer;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                background: white;
                display: block;
            }

            .container {
                width: 100%;
                padding: 5px;
            }

            .buttons {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="struk">
            <div class="title">PARKIR SISWA</div>

            <div class="separator"></div>

            <div class="row">
                <span>No</span>
                <span><?php echo str_pad($data['id'], 4, '0', STR_PAD_LEFT); ?></span>
            </div>

            <div class="row">
                <span>Plat</span>
                <span><?php echo $data['plat_nomor']; ?></span>
            </div>

            <div class="row">
                <span>Jenis</span>
                <span><?php echo strtoupper($data['jenis_kendaraan']); ?></span>
            </div>

            <div class="separator"></div>

            <div class="row">
                <span>Masuk</span>
                <span><?php echo date('d/m H:i', strtotime($data['waktu_masuk'])); ?></span>
            </div>

            <div class="row">
                <span>Keluar</span>
                <span><?php echo date('d/m H:i', strtotime($data['waktu_keluar'])); ?></span>
            </div>

            <div class="row">
                <span>Durasi</span>
                <span><?php echo $data['durasi']; ?> Jam</span>
            </div>

            <div class="separator"></div>

            <div class="total">
                <div class="row">
                    <span>TOTAL</span>
                    <span class="amount">Rp<?php echo number_format($data['biaya'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="separator"></div>

            <div class="footer">
                <div><?php echo $data['petugas_keluar']; ?></div>
                <div>Terima kasih</div>
            </div>
        </div>

        <div class="buttons">
            <button onclick="window.print()" class="btn btn-print">CETAK STRUK</button>
            <button onclick="window.location.href='keluar.php'" class="btn btn-back">KEMBALI</button>
        </div>

        <script>
            // Auto print saat halaman dibuka
            window.onload = function() {
                // Uncomment baris berikut jika ingin auto print
                // window.print();
            }
        </script>
</body>

</html>