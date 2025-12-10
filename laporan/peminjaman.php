<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
// Safety: pastikan kolom tanggal_kembali ada agar report lengkap
try{ $conn->query("ALTER TABLE peminjaman ADD COLUMN tanggal_kembali DATETIME NULL"); }catch(Exception $e){}
$type = $_GET['type'] ?? 'peminjaman';
if(!in_array($type, ['peminjaman','penghapusan','penyesuaian'], true)) { $type = 'peminjaman'; }

if($type === 'peminjaman') {
  $sql = "
  SELECT 
    p.id_pinjam,
    i.kode_barang,
    i.nama_barang,
    i.kategori,
    p.nama_peminjam,
    p.jumlah_pinjam,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status
  FROM peminjaman p
  JOIN inventaris i ON i.id_barang = p.id_barang
  ORDER BY p.tanggal_pinjam DESC, p.id_pinjam DESC";
  $rows = $conn->query($sql);
} elseif($type === 'penghapusan') {
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
  $penghapusan = $conn->query('SELECT * FROM penghapusan ORDER BY id_hapus DESC');
} else {
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
  $penyesuaian = $conn->query('SELECT * FROM penyesuaian_stok ORDER BY id_adjust DESC');
}

include __DIR__.'/../includes/header.php';
?>
<h2>Laporan</h2>
<div class="actions">
  <a class="btn<?= $type==='peminjaman'?' secondary':'' ?>" href="?type=peminjaman">Peminjaman</a>
  <a class="btn<?= $type==='penghapusan'?' secondary':'' ?>" href="?type=penghapusan">Penghapusan</a>
  <a class="btn<?= $type==='penyesuaian'?' secondary':'' ?>" href="?type=penyesuaian">Penyesuaian Stok</a>
</div>
<div class="actions">
  <?php if($type==='peminjaman'): ?>
    <a class="btn" href="export_peminjaman_excel.php">Export Excel</a>
  <?php elseif($type==='penghapusan'): ?>
    <a class="btn" href="export_penghapusan_excel.php">Export Excel</a>
  <?php else: ?>
    <a class="btn" href="export_penyesuaian_excel.php">Export Excel</a>
  <?php endif; ?>
  <button class="btn secondary" onclick="window.print()">Export PDF</button>
  <a class="btn secondary" href="/INVENKAS/pinjam/index.php">Ke Peminjaman</a>
</div>

<?php if($type==='peminjaman'): ?>
  <h2>Laporan Peminjaman</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Kode</th>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Nama Peminjam</th>
        <th>Jumlah Dipinjam</th>
        <th>Tanggal Pinjam</th>
        <th>Tanggal Kembali</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if($rows){ while($r=$rows->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($r['kode_barang']) ?></td>
          <td><?= htmlspecialchars($r['nama_barang']) ?></td>
          <td><?= htmlspecialchars($r['kategori']) ?></td>
          <td><?= htmlspecialchars($r['nama_peminjam']) ?></td>
          <td><?= (int)$r['jumlah_pinjam'] ?></td>
          <td><?= htmlspecialchars($r['tanggal_pinjam']) ?></td>
          <td><?= htmlspecialchars($r['tanggal_kembali'] ?? '-') ?></td>
          <td><?= htmlspecialchars($r['status']) ?></td>
        </tr>
      <?php endwhile; } ?>
    </tbody>
  </table>
<?php elseif($type==='penghapusan'): ?>
  <h2>Laporan Penghapusan Barang</h2>
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
      <?php if($penghapusan){ while($h=$penghapusan->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($h['kode_barang']) ?></td>
          <td><?= htmlspecialchars($h['nama_barang']) ?></td>
          <td><?= htmlspecialchars($h['kategori']) ?></td>
          <td><?= (int)$h['jumlah_terakhir'] ?></td>
          <td><?= htmlspecialchars($h['kondisi']) ?></td>
          <td><?= htmlspecialchars($h['lokasi']) ?></td>
          <td><?= htmlspecialchars($h['alasan']) ?></td>
          <td><?= htmlspecialchars($h['tanggal_hapus']) ?></td>
        </tr>
      <?php endwhile; } ?>
    </tbody>
  </table>
<?php else: ?>
  <h2>Laporan Penyesuaian Stok</h2>
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
      <?php if($penyesuaian){ while($p=$penyesuaian->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($p['kode_barang']) ?></td>
          <td><?= htmlspecialchars($p['nama_barang']) ?></td>
          <td><?= (int)$p['qty_awal'] ?></td>
          <td><?= (int)$p['qty_baru'] ?></td>
          <td><?= (int)$p['selisih'] ?></td>
          <td><?= htmlspecialchars($p['alasan']) ?></td>
          <td><?= htmlspecialchars($p['tanggal_adjust']) ?></td>
        </tr>
      <?php endwhile; } ?>
    </tbody>
  </table>
<?php endif; ?>
<?php include __DIR__.'/../includes/footer.php'; ?>
