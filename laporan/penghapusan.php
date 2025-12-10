<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
// Pastikan tabel penghapusan ada (safety net bila migrasi belum dijalankan)
$conn->query("CREATE TABLE IF NOT EXISTS penghapusan (
  id_hapus INT AUTO_INCREMENT PRIMARY KEY,
  id_barang INT,
  kode_barang VARCHAR(50) NOT NULL,
  nama_barang VARCHAR(100) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  jumlah_terakhir INT NOT NULL,
  kondisi VARCHAR(50) NOT NULL,
  lokasi VARCHAR(100) NOT NULL,
  alasan VARCHAR(255) NOT NULL,
  tanggal_hapus DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");
$rows=$conn->query('SELECT * FROM penghapusan ORDER BY id_hapus DESC');
include __DIR__.'/../includes/header.php';
?>
<h2>Laporan Penghapusan Barang</h2>
<div class="actions">
  <a class="btn" href="export_penghapusan_excel.php">Export Excel</a>
  <button class="btn secondary" onclick="window.print()">Export PDF</button>
  <a class="btn secondary" href="/INVENKAS/barang/index.php">Ke Data Barang</a>
</div>
<table class="table">
  <thead>
    <tr>
      <th>Kode</th>
      <th>Nama</th>
      <th>Kategori</th>
      <th>Jumlah Terakhir</th>
      <th>Kondisi</th>
      <th>Lokasi</th>
      <th>Alasan</th>
      <th>Tanggal Hapus</th>
    </tr>
  </thead>
  <tbody>
    <?php if($rows){ while($r=$rows->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($r['kode_barang']) ?></td>
      <td><?= htmlspecialchars($r['nama_barang']) ?></td>
      <td><?= htmlspecialchars($r['kategori']) ?></td>
      <td><?= (int)$r['jumlah_terakhir'] ?></td>
      <td><?= htmlspecialchars($r['kondisi']) ?></td>
      <td><?= htmlspecialchars($r['lokasi']) ?></td>
      <td><?= htmlspecialchars($r['alasan']) ?></td>
      <td><?= htmlspecialchars($r['tanggal_hapus']) ?></td>
    </tr>
    <?php endwhile; } ?>
  </tbody>
</table>
<?php include __DIR__.'/../includes/footer.php'; ?>
