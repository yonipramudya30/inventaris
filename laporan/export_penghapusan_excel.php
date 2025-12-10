<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_penghapusan.xls"');

$sql = "SELECT * FROM penghapusan ORDER BY id_hapus DESC";
$res = $conn->query($sql);

echo '<html><head><meta charset="utf-8">'
    .'<style>
      table{border-collapse:collapse;width:100%;}
      th,td{border:1px solid #888;padding:6px 8px;font-family:Segoe UI,Arial,sans-serif;font-size:12px}
      th{background:#e5e7eb;text-align:left}
      h3{font-family:Segoe UI,Arial,sans-serif}
    </style></head><body>';
echo '<h3>Laporan Penghapusan Barang</h3>';

echo '<table><thead><tr>
        <th>Kode</th>
        <th>Nama</th>
        <th>Kategori</th>
        <th>Jumlah Terakhir</th>
        <th>Kondisi</th>
        <th>Lokasi</th>
        <th>Alasan</th>
        <th>Tanggal Hapus</th>
      </tr></thead><tbody>';
if($res){
  while($r = $res->fetch_assoc()){
    echo '<tr>'
        .'<td>'.htmlspecialchars($r['kode_barang']).'</td>'
        .'<td>'.htmlspecialchars($r['nama_barang']).'</td>'
        .'<td>'.htmlspecialchars($r['kategori']).'</td>'
        .'<td>'.(int)$r['jumlah_terakhir'].'</td>'
        .'<td>'.htmlspecialchars($r['kondisi']).'</td>'
        .'<td>'.htmlspecialchars($r['lokasi']).'</td>'
        .'<td>'.htmlspecialchars($r['alasan']).'</td>'
        .'<td>'.htmlspecialchars($r['tanggal_hapus']).'</td>'
        .'</tr>';
  }
}

echo '</tbody></table></body></html>';
exit;
