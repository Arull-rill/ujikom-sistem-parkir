<?php
require_once '../config.php';
require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Ambil tanggal dari GET
$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d');
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');

// Query sama kayak punya lu
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

// Query harian
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

// START HTML
$html = '
<h2 style="text-align:center;">Laporan Parkir</h2>
<p>Periode: ' . date('d/m/Y', strtotime($tanggal_dari)) . ' - ' . date('d/m/Y', strtotime($tanggal_sampai)) . '</p>

<h3>Total</h3>
<ul>
    <li>Total Pendapatan: Rp ' . number_format($laporan['total_pendapatan'] ?? 0, 0, ',', '.') . '</li>
    <li>Total Transaksi: ' . $laporan['total_transaksi'] . '</li>
    <li>Motor: ' . $laporan['total_motor'] . '</li>
    <li>Mobil: ' . $laporan['total_mobil'] . '</li>
</ul>

<h3>Detail Harian</h3>
<table border="1" cellpadding="5" cellspacing="0" width="100%">
<tr>
    <th>Tanggal</th>
    <th>Transaksi</th>
    <th>Pendapatan</th>
</tr>
';

while ($row = mysqli_fetch_assoc($result_harian)) {
    $html .= '
    <tr>
        <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
        <td>' . $row['jumlah'] . '</td>
        <td>Rp ' . number_format($row['pendapatan'], 0, ',', '.') . '</td>
    </tr>';
}

$html .= '</table>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output
$dompdf->stream("laporan_parkir.pdf", array("Attachment" => true));
