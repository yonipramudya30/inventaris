<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
// Pastikan tabel penyesuaian_stok ada (safety net bila migrasi belum dijalankan)
$conn->query("CREATE TABLE IF NOT EXISTS penyesuaian_stok (
  id_adjust INT AUTO_INCREMENT PRIMARY KEY,
  id_barang INT NOT NULL,
  kode_barang VARCHAR(50) NOT NULL,
  nama_barang VARCHAR(100) NOT NULL,
  qty_awal INT NOT NULL,
  qty_baru INT NOT NULL,
  selisih INT NOT NULL,
  alasan VARCHAR(100) NOT NULL,
  tanggal_adjust DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");
$rows = $conn->query('SELECT * FROM penyesuaian_stok ORDER BY id_adjust DESC');
include __DIR__.'/../includes/header.php';
?>
<h2>Laporan Penyesuaian Stok</h2>
<style>
@media print {
  .actions { display:none !important; }
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #888; padding: 6px 8px; }
  th { background: #e5e7eb; text-align: left; }
}
</style>
<div class="actions">
  <a class="btn" href="export_penyesuaian_excel.php">Export Excel</a>
  <button class="btn secondary" onclick="window.print()">Export PDF</button>
  <a class="btn secondary" href="/INVENKAS/barang/index.php">Ke Data Barang</a>
</div>
<table class="table">
  <thead>
    <tr>
      <th>Kode</th>
      <th>Nama</th>
      <th>Qty Awal</th>
      <th>Qty Baru</th>
      <th>Selisih</th>
      <th>Alasan</th>
      <th>Tanggal</th>
    </tr>
  </thead>
  <tbody>
    <?php if($rows){ while($r=$rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['kode_barang']) ?></td>
        <td><?= htmlspecialchars($r['nama_barang']) ?></td>
        <td><?= (int)$r['qty_awal'] ?></td>
        <td><?= (int)$r['qty_baru'] ?></td>
        <td><?= (int)$r['selisih'] ?></td>
        <td><?= htmlspecialchars($r['alasan']) ?></td>
        <td><?= htmlspecialchars($r['tanggal_adjust']) ?></td>
      </tr>
    <?php endwhile; } ?>
  </tbody>
</table>
<?php include __DIR__.'/../includes/footer.php'; ?>
