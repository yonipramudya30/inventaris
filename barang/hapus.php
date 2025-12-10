<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$st=$conn->prepare('SELECT * FROM inventaris WHERE id_barang=?');
$st->bind_param('i',$id);
$st->execute();
$data=$st->get_result()->fetch_assoc();
include __DIR__.'/../includes/header.php';
?>
<h2>Hapus Barang</h2>
<?php if(!$data): ?>
  <div>Data tidak ditemukan.</div>
  <a class="btn" href="index.php">Kembali</a>
  <?php include __DIR__.'/../includes/footer.php'; exit; endif; ?>
<div class="card" style="background:#fff;padding:12px;border-radius:8px;margin-bottom:12px;">
  <div><strong>Kode:</strong> <?= htmlspecialchars($data['kode_barang']) ?></div>
  <div><strong>Nama:</strong> <?= htmlspecialchars($data['nama_barang']) ?></div>
  <div><strong>Kategori:</strong> <?= htmlspecialchars($data['kategori']) ?></div>
  <div><strong>Jumlah:</strong> <?= (int)$data['jumlah'] ?></div>
  <div><strong>Kondisi:</strong> <?= htmlspecialchars($data['kondisi']) ?></div>
  <div><strong>Lokasi:</strong> <?= htmlspecialchars($data['lokasi']) ?></div>
</div>
<form class="form" method="post" action="proses_hapus.php">
  <input type="hidden" name="id" value="<?= (int)$data['id_barang'] ?>">
  <input name="alasan" placeholder="Alasan penghapusan (mis. rusak, tidak layak pakai)" required>
  <div class="actions">
    <button class="btn danger" type="submit">Hapus Permanen</button>
    <a class="btn secondary" href="index.php">Batal</a>
  </div>
</form>
<?php include __DIR__.'/../includes/footer.php'; ?>
