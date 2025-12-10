<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
// Pastikan kolom tanggal_kembali ada
try{ $conn->query("ALTER TABLE peminjaman ADD COLUMN tanggal_kembali DATETIME NULL"); }catch(Exception $e){}

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_peminjaman.xls"');

$sql = "
SELECT 
  i.kode_barang,
  i.nama_barang,
  i.kategori,
  p.nama_peminjam,
  p.jumlah_pinjam,
  p.tanggal_pinjam,
  p.tanggal_kembali
FROM peminjaman p
JOIN inventaris i ON i.id_barang = p.id_barang
ORDER BY p.tanggal_pinjam DESC, p.id_pinjam DESC";
$res = $conn->query($sql);

echo '<html><head><meta charset="utf-8">'
    .'<style>
      table{border-collapse:collapse;width:100%;}
      th,td{border:1px solid #888;padding:6px 8px;font-family:Segoe UI,Arial,sans-serif;font-size:12px}
      th{background:#e5e7eb;text-align:left}
      h3{font-family:Segoe UI,Arial,sans-serif}
    </style></head><body>';
echo '<h3>Laporan Peminjaman</h3>';
echo '<table><thead><tr>
        <th>Kode</th>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Nama Peminjam</th>
        <th>Jumlah Dipinjam</th>
        <th>Tanggal Pinjam</th>
        <th>Tanggal Kembali</th>
      </tr></thead><tbody>';
if($res){
  while($r = $res->fetch_assoc()){
    echo '<tr>'
        .'<td>'.htmlspecialchars($r['kode_barang']).'</td>'
        .'<td>'.htmlspecialchars($r['nama_barang']).'</td>'
        .'<td>'.htmlspecialchars($r['kategori']).'</td>'
        .'<td>'.htmlspecialchars($r['nama_peminjam']).'</td>'
        .'<td>'.(int)$r['jumlah_pinjam'].'</td>'
        .'<td>'.htmlspecialchars($r['tanggal_pinjam']).'</td>'
        .'<td>'.htmlspecialchars($r['tanggal_kembali'] ?? '').'</td>'
        .'</tr>';
  }
}
echo '</tbody></table></body></html>';
exit;
