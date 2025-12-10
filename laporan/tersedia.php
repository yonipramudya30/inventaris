<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
$rows=$conn->query('SELECT * FROM inventaris WHERE jumlah>0 ORDER BY nama_barang');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/INVENKAS/assets/css/style.css">
<title>Laporan Barang Tersedia</title>
</head>
<body>
<div class="print-header"><h3>Laporan Barang Tersedia</h3></div>
<div class="no-print" style="padding:10px;"><button class="btn" onclick="window.print()">Cetak</button></div>
<table class="table">
  <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Jumlah</th><th>Kondisi</th><th>Lokasi</th><th>Tanggal</th></tr></thead>
  <tbody>
  <?php while($r=$rows->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($r['kode_barang']) ?></td>
      <td><?= htmlspecialchars($r['nama_barang']) ?></td>
      <td><?= htmlspecialchars($r['kategori']) ?></td>
      <td><?= (int)$r['jumlah'] ?></td>
      <td><?= htmlspecialchars($r['kondisi']) ?></td>
      <td><?= htmlspecialchars($r['lokasi']) ?></td>
      <td><?= htmlspecialchars($r['tanggal_input']) ?></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</body>
</html>
