<?php
require_once '../config.php';
require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Ambil semua data kendaraan
$query = "SELECT k.*, 
          u1.nama as nama_petugas_masuk,
          u2.nama as nama_petugas_keluar
          FROM kendaraan k
          LEFT JOIN users u1 ON k.petugas_masuk = u1.id
          LEFT JOIN users u2 ON k.petugas_keluar = u2.id
          ORDER BY k.id DESC";

$result = mysqli_query($conn, $query);

// START HTML
$html = '
<h2 style="text-align:center;">Laporan Data Kendaraan</h2>
<hr>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
<tr>
    <th>ID</th>
    <th>Plat</th>
    <th>Jenis</th>
    <th>Masuk</th>
    <th>Keluar</th>
    <th>Durasi</th>
    <th>Biaya</th>
    <th>Status</th>
    <th>Petugas Masuk</th>
    <th>Petugas Keluar</th>
</tr>
';

while ($row = mysqli_fetch_assoc($result)) {

    $html .= '
    <tr>
        <td>'.$row['id'].'</td>
        <td>'.$row['plat_nomor'].'</td>
        <td>'.strtoupper($row['jenis_kendaraan']).'</td>
        <td>'.date('d/m/Y H:i', strtotime($row['waktu_masuk'])).'</td>
        <td>'.($row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '-').'</td>
        <td>'.($row['durasi'] ?? '-').'</td>
        <td>'.($row['biaya'] ? 'Rp '.number_format($row['biaya'],0,',','.') : '-').'</td>
        <td>'.strtoupper($row['status']).'</td>
        <td>'.($row['nama_petugas_masuk'] ?? '-').'</td>
        <td>'.($row['nama_petugas_keluar'] ?? '-').'</td>
    </tr>';
}

$html .= '</table>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // biar muat banyak kolom
$dompdf->render();

// Output
$dompdf->stream("data_kendaraan.pdf", array("Attachment" => true));