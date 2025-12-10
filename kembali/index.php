<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
$trans=$conn->query("SELECT p.*, i.nama_barang, i.kode_barang FROM peminjaman p JOIN inventaris i ON i.id_barang=p.id_barang WHERE p.status='Dipinjam' ORDER BY p.id_pinjam DESC");
include __DIR__.'/../includes/header.php';
?>
<h2>Pengembalian Barang</h2>
<table class="table">
  <thead>
    <tr>
      <th>Kode</th><th>Nama Barang</th><th>Peminjam</th><th>Jumlah</th><th>Tanggal</th><th>Status</th><th class="no-print">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php while($t=$trans->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($t['kode_barang']) ?></td>
      <td><?= htmlspecialchars($t['nama_barang']) ?></td>
      <td><?= htmlspecialchars($t['nama_peminjam']) ?></td>
      <td><?= (int)$t['jumlah_pinjam'] ?></td>
      <td><?= htmlspecialchars($t['tanggal_pinjam']) ?></td>
      <td><?= htmlspecialchars($t['status']) ?></td>
      <td class="no-print">
        <a class="btn" href="proses_kembali.php?id=<?= $t['id_pinjam'] ?>" data-confirm="Tandai sudah kembali?">Kembalikan</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php include __DIR__.'/../includes/footer.php'; ?>
