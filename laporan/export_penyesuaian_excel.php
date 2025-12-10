<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_penyesuaian_stok.xls"');

$sql = "SELECT * FROM penyesuaian_stok ORDER BY id_adjust DESC";
$res = $conn->query($sql);

echo '<html><head><meta charset="utf-8">'
    .'<style>
      table{border-collapse:collapse;width:100%;}
      th,td{border:1px solid #888;padding:6px 8px;font-family:Segoe UI,Arial,sans-serif;font-size:12px}
      th{background:#e5e7eb;text-align:left}
      h3{font-family:Segoe UI,Arial,sans-serif}
    </style></head><body>';
echo '<h3>Laporan Penyesuaian Stok</h3>';

echo '<table><thead><tr>
        <th>Kode</th>
        <th>Nama</th>
        <th>Qty Awal</th>
        <th>Qty Baru</th>
        <th>Selisih</th>
        <th>Alasan</th>
        <th>Tanggal</th>
      </tr></thead><tbody>';
if($res){
  while($r = $res->fetch_assoc()){
    echo '<tr>'
        .'<td>'.htmlspecialchars($r['kode_barang']).'</td>'
        .'<td>'.htmlspecialchars($r['nama_barang']).'</td>'
        .'<td>'.(int)$r['qty_awal'].'</td>'
        .'<td>'.(int)$r['qty_baru'].'</td>'
        .'<td>'.(int)$r['selisih'].'</td>'
        .'<td>'.htmlspecialchars($r['alasan']).'</td>'
        .'<td>'.htmlspecialchars($r['tanggal_adjust']).'</td>'
        .'</tr>';
  }
}

echo '</tbody></table></body></html>';
exit;
