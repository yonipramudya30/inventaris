<?php
require __DIR__.'/includes/auth_check.php';
require __DIR__.'/config/db.php';
$barang = (int)$conn->query('SELECT COUNT(*) c FROM inventaris')->fetch_assoc()['c'];
$dipinjam = (int)$conn->query("SELECT COALESCE(SUM(jumlah_pinjam),0) s FROM peminjaman WHERE status='Dipinjam'")->fetch_assoc()['s'];
// Jumlah pada tabel inventaris adalah stok aktual (setelah dipinjam/ dikembalikan)
$stok_aktual = (int)$conn->query('SELECT COALESCE(SUM(jumlah),0) s FROM inventaris')->fetch_assoc()['s'];
$trans = (int)$conn->query('SELECT COUNT(*) c FROM peminjaman')->fetch_assoc()['c'];
$tersedia = $stok_aktual; // sinkron dengan data barang yang memang tersedia saat ini

// Data tambahan untuk section bawah
$recentLoans = $conn->query("SELECT p.*, i.kode_barang, i.nama_barang FROM peminjaman p JOIN inventaris i ON i.id_barang=p.id_barang ORDER BY p.id_pinjam DESC LIMIT 5");
$lowStock = $conn->query("SELECT kode_barang,nama_barang,kategori,jumlah FROM inventaris WHERE jumlah<=5 ORDER BY jumlah ASC, nama_barang ASC LIMIT 8");
$byKategori = $conn->query("SELECT kategori, COALESCE(SUM(jumlah),0) total FROM inventaris GROUP BY kategori ORDER BY kategori ASC");
$topBorrowed = $conn->query("SELECT i.kode_barang,i.nama_barang, COALESCE(SUM(p.jumlah_pinjam),0) total_pinjam FROM peminjaman p JOIN inventaris i ON i.id_barang=p.id_barang GROUP BY p.id_barang,i.kode_barang,i.nama_barang ORDER BY total_pinjam DESC LIMIT 5");

include __DIR__.'/includes/header.php';
?>
<h2>Dashboard</h2>
<div class="card-grid" style="margin-bottom:12px;">
  <div class="card"><div class="title">Jumlah Barang</div><div class="value"><?php echo $barang; ?></div></div>
  <div class="card"><div class="title">Barang Dipinjam (unit)</div><div class="value"><?php echo $dipinjam; ?></div></div>
  <div class="card"><div class="title">Total Transaksi</div><div class="value"><?php echo $trans; ?></div></div>
</div>

<div class="card-grid" style="margin-bottom:12px;">
  <div class="card">
    <div class="title" style="margin-bottom:6px;">Peminjaman Terbaru</div>
    <table class="table" style="margin:0;">
      <thead>
        <tr><th>Kode</th><th>Nama</th><th>Peminjam</th><th>Qty</th><th>Tanggal</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if($recentLoans){ while($r=$recentLoans->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['kode_barang']) ?></td>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td><?= htmlspecialchars($r['nama_peminjam']) ?></td>
            <td><?= (int)$r['jumlah_pinjam'] ?></td>
            <td><?= htmlspecialchars($r['tanggal_pinjam']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
          </tr>
        <?php endwhile; } ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="title" style="margin-bottom:6px;">Stok Rendah (<=5)</div>
    <table class="table" style="margin:0;">
      <thead>
        <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Qty</th></tr>
      </thead>
      <tbody>
        <?php if($lowStock){ while($s=$lowStock->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($s['kode_barang']) ?></td>
            <td><?= htmlspecialchars($s['nama_barang']) ?></td>
            <td><?= htmlspecialchars($s['kategori']) ?></td>
            <td><?= (int)$s['jumlah'] ?></td>
          </tr>
        <?php endwhile; } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card-grid">
  <div class="card">
    <div class="title" style="margin-bottom:6px;">Stok per Kategori</div>
    <table class="table" style="margin:0;">
      <thead>
        <tr><th>Kategori</th><th>Total Stok</th></tr>
      </thead>
      <tbody>
        <?php if($byKategori){ while($k=$byKategori->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($k['kategori']) ?></td>
            <td><?= (int)$k['total'] ?></td>
          </tr>
        <?php endwhile; } ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="title" style="margin-bottom:6px;">Barang Paling Sering Dipinjam</div>
    <table class="table" style="margin:0;">
      <thead>
        <tr><th>Kode</th><th>Nama</th><th>Total Dipinjam</th></tr>
      </thead>
      <tbody>
        <?php if($topBorrowed){ while($t=$topBorrowed->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($t['kode_barang']) ?></td>
            <td><?= htmlspecialchars($t['nama_barang']) ?></td>
            <td><?= (int)$t['total_pinjam'] ?></td>
          </tr>
        <?php endwhile; } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
