<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../config/db.php';

// Debug: Tampilkan data session
// echo '<pre>'; print_r($_SESSION); echo '</pre>';

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: /INVENKAS/login.php');
    exit();
}

// Hanya karyawan yang boleh mengakses halaman ini
if (!isset($_SESSION['user_id'])) {
    header('Location: /INVENKAS/login.php');
    exit();
}
// Hanya tampilkan barang yang stoknya tersedia
$barang = $conn->query("SELECT id_barang, kode_barang, nama_barang, jumlah, gambar 
                       FROM inventaris 
                       WHERE jumlah > 0 
                       ORDER BY nama_barang");

// Ambil data peminjaman terbaru (maksimal 5 data terbaru)
$peminjaman_terbaru = $conn->query("
    SELECT p.*, i.nama_barang, i.kode_barang, i.gambar 
    FROM peminjaman p 
    JOIN inventaris i ON p.id_barang = i.id_barang 
    ORDER BY p.tanggal_pinjam DESC 
    LIMIT 5
");

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Tampilkan pesan sukses/error
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}

// Filters
$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$id_barang_filter = (int)($_GET['id_barang'] ?? 0);
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');

$where = [];
if($q !== ''){
  $safe = '%'.$conn->real_escape_string($q).'%';
  $where[] = "(p.nama_peminjam LIKE '$safe' OR i.nama_barang LIKE '$safe' OR i.kode_barang LIKE '$safe')";
}
if($status === 'Dipinjam' || $status === 'Dikembalikan'){
  $where[] = "p.status='".$conn->real_escape_string($status)."'";
}
if($id_barang_filter>0){
  $where[] = 'p.id_barang='.(int)$id_barang_filter;
}
if($from !== ''){ $where[] = "DATE(p.tanggal_pinjam) >= '".$conn->real_escape_string($from)."'"; }
if($to !== ''){ $where[] = "DATE(p.tanggal_pinjam) <= '".$conn->real_escape_string($to)."'"; }

$sqlTrans = 'SELECT p.*, i.nama_barang, i.kode_barang, i.gambar FROM peminjaman p JOIN inventaris i ON i.id_barang=p.id_barang';
if($where){ $sqlTrans .= ' WHERE '.implode(' AND ',$where); }
$sqlTrans .= ' ORDER BY p.id_pinjam DESC';
$trans=$conn->query($sqlTrans);
include __DIR__.'/../includes/header.php';
?>
<h2>Peminjaman Barang</h2>
<div class="actions">
  <button id="openFilter" class="btn secondary" type="button">Filter</button>
  <button id="openPinjam" class="btn" type="button">Pinjam Barang</button>
</div>
<?php if(!empty($_GET['err'])): ?>
  <div style="color:#ef4444; margin-bottom:10px;">&nbsp;<?= htmlspecialchars($_GET['err']) ?></div>
<?php endif; ?>

<!-- Modal Filter -->
<div id="modalFilter" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="filterTitle">
    <header>
      <h3 id="filterTitle" style="margin:0;">Filter Peminjaman</h3>
      <button type="button" class="btn secondary" data-close="modalFilter">Tutup</button>
    </header>
    <form class="form compact" method="get">
      <div class="filters">
        <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari (kode/nama/peminjam)">
        <select name="status">
          <option value="">Semua Status</option>
          <option value="Dipinjam" <?= $status==='Dipinjam'?'selected':'' ?>>Dipinjam</option>
          <option value="Dikembalikan" <?= $status==='Dikembalikan'?'selected':'' ?>>Dikembalikan</option>
        </select>
        <select name="id_barang">
          <option value="0">Semua Barang</option>
          <?php
            $barang2=$conn->query('SELECT id_barang,kode_barang,nama_barang FROM inventaris ORDER BY nama_barang');
            while($b2=$barang2->fetch_assoc()):
          ?>
            <option value="<?= (int)$b2['id_barang'] ?>" <?= $id_barang_filter==(int)$b2['id_barang']?'selected':'' ?>>[<?= htmlspecialchars($b2['kode_barang']) ?>] <?= htmlspecialchars($b2['nama_barang']) ?></option>
          <?php endwhile; ?>
        </select>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
      </div>
      <div class="modal-actions">
        <a class="btn secondary" href="index.php">Reset</a>
        <button class="btn" type="submit">Terapkan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Pinjam -->
<div id="modalPinjam" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="pinjamTitle">
    <header>
      <h3 id="pinjamTitle" style="margin:0;">Pinjam Barang</h3>
      <button type="button" class="btn secondary" data-close="modalPinjam">Tutup</button>
    </header>
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Peminjaman Barang</h5>
        <button class="btn btn-primary" data-open-modal="modalPinjam">
          <i class="bi bi-plus-circle"></i> Pinjam Barang
        </button>
      </div>
    </div>

    <!-- Notifikasi Peminjaman Terbaru -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title d-flex justify-content-between align-items-center">
          <span>Peminjaman Terbaru</span>
          <span class="badge bg-primary"><?= $peminjaman_terbaru->num_rows ?> Transaksi</span>
        </h5>
        
        <?php if ($peminjaman_terbaru->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Gambar</th>
                  <th>Tanggal</th>
                  <th>Kode Barang</th>
                  <th>Nama Barang</th>
                  <th>Peminjam</th>
                  <th>Jumlah</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $peminjaman_terbaru->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <img src="<?= !empty($row['gambar']) ? htmlspecialchars($row['gambar']) : '/INVENKAS/assets/img/no-image.png' ?>" 
                           alt="<?= htmlspecialchars($row['nama_barang']) ?>" 
                           style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pinjam'])) ?></td>
                    <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($row['nama_peminjam']) ?></td>
                    <td><?= (int)$row['jumlah_pinjam'] ?></td>
                    <td>
                      <span class="badge bg-<?= $row['status'] === 'Dipinjam' ? 'warning' : 'success' ?>">
                        <?= htmlspecialchars($row['status']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">Belum ada data peminjaman.</div>
        <?php endif; ?>
      </div>
    </div>

    <form class="form compact" method="post" action="proses_pinjam.php">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
      <div class="mb-3">
        <div class="d-flex align-items-center mb-2">
          <img id="barangPreview" src="/INVENKAS/assets/img/no-image.png" alt="Preview Barang" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6; border-radius: 4px;">
          <div>
            <div id="barangNama" class="fw-bold">Pilih Barang</div>
            <div id="barangKode" class="text-muted small">Kode: -</div>
            <div id="barangStok" class="text-muted small">Stok: -</div>
          </div>
        </div>
        <select name="id_barang" id="selectBarang" class="form-select" required>
          <option value="">Pilih Barang</option>
          <?php 
          // Reset pointer result set
          $barang->data_seek(0);
          while($b = $barang->fetch_assoc()): 
              $gambar = !empty($b['gambar']) ? htmlspecialchars($b['gambar']) : '/INVENKAS/assets/img/no-image.png';
          ?>
            <option value="<?= $b['id_barang'] ?>" 
                    data-stok="<?= (int)$b['jumlah'] ?>"
                    data-img="<?= $gambar ?>">
              [<?= htmlspecialchars($b['kode_barang']) ?>] <?= htmlspecialchars($b['nama_barang']) ?> - Stok: <?= (int)$b['jumlah'] ?>
            </option>
          <?php endwhile; ?>
        </select>
        <input name="nama_peminjam" placeholder="Nama Peminjam" required>
        <input type="number" name="jumlah_pinjam" placeholder="Jumlah Pinjam" min="1" required>
        <button class="btn" type="submit">Simpan</button>
      </div>
      <small id="stokHint" style="color:#6b7280;"></small>
    </form>
  </div>
</div>

<div class="table-responsive">
<table class="table">
  <thead>
    <tr>
      <th>Gambar</th><th>Kode</th><th>Nama Barang</th><th>Peminjam</th><th>Jumlah</th><th>Tanggal</th><th>Status</th><th class="no-print">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php while($t=$trans->fetch_assoc()): ?>
    <tr>
      <td>
        <img src="<?= !empty($t['gambar']) ? htmlspecialchars($t['gambar']) : '/INVENKAS/assets/img/no-image.png' ?>" 
             alt="<?= htmlspecialchars($t['nama_barang']) ?>" 
             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
      </td>
      <td><?= htmlspecialchars($t['kode_barang']) ?></td>
      <td><?= htmlspecialchars($t['nama_barang']) ?></td>
      <td><?= htmlspecialchars($t['nama_peminjam']) ?></td>
      <td><?= (int)$t['jumlah_pinjam'] ?></td>
      <td><?= htmlspecialchars($t['tanggal_pinjam']) ?></td>
      <td><?= htmlspecialchars($t['status']) ?></td>
      <td class="no-print">
        <?php if($t['status']==='Dipinjam'): ?>
          <a class="btn" href="../kembali/proses_kembali.php?id=<?= $t['id_pinjam'] ?>" data-confirm="Konfirmasi pengembalian?">Kembalikan</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>
<script>
(function(){
  var select = document.getElementById('selectBarang');
  var qty = document.querySelector('#modalPinjam input[name="jumlah_pinjam"]');
  var hint = document.getElementById('stokHint');
  var imgPreview = document.getElementById('barangPreview');
  var barangNama = document.getElementById('barangNama');
  var barangKode = document.getElementById('barangKode');
  var barangStok = document.getElementById('barangStok');

  // Update preview saat barang dipilih
  select.addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
      var imgSrc = selectedOption.getAttribute('data-img');
      var kode = selectedOption.text.match(/\[(.*?)\]/)[1];
      var nama = selectedOption.text.split('] ')[1].split(' - ')[0];
      var stok = selectedOption.getAttribute('data-stok');
      
      imgPreview.src = imgSrc || '/INVENKAS/assets/img/no-image.png';
      barangNama.textContent = nama;
      barangKode.textContent = 'Kode: ' + kode;
      barangStok.textContent = 'Stok: ' + stok;
      
      // Update max value untuk input jumlah
      qty.max = stok;
      if (parseInt(qty.value) > stok) {
        qty.value = stok;
      }
      hint.textContent = 'Maksimal: ' + stok;
    } else {
      imgPreview.src = '/INVENKAS/assets/img/no-image.png';
      barangNama.textContent = 'Pilih Barang';
      barangKode.textContent = 'Kode: -';
      barangStok.textContent = 'Stok: -';
      hint.textContent = '';
    }
  });

  function updateMax() {
    if(!select) return;
    var opt=select.options[select.selectedIndex];
    var stok=opt && opt.getAttribute('data-stok');
    if(stok){
      qty.max=stok;
      if(qty.value && parseInt(qty.value,10)>parseInt(stok,10)) qty.value=stok;
      hint.textContent='Maksimal dapat dipinjam: '+stok;
    } else {
      if(qty) qty.removeAttribute('max');
      hint.textContent='';
    }
  }
  if(select){
    select.addEventListener('change', updateMax);
    updateMax();
  }

  function openModal(id){
    var el=document.getElementById(id);
    if(el){
      el.classList.add('open');
      el.setAttribute('aria-hidden','false');
      document.body.classList.add('modal-open');
      // autofocus first input/select
      var first = el.querySelector('input,select,button');
      if(first) { try{ first.focus(); }catch(e){} }
    }
  }
  function closeModal(id){
    var el=document.getElementById(id);
    if(el){
      el.classList.remove('open');
      el.setAttribute('aria-hidden','true');
      document.body.classList.remove('modal-open');
    }
  }
  document.getElementById('openFilter').addEventListener('click', function(){ openModal('modalFilter'); });
  document.getElementById('openPinjam').addEventListener('click', function(){ openModal('modalPinjam'); });
  document.querySelectorAll('[data-close]').forEach(function(btn){
    btn.addEventListener('click', function(){ closeModal(btn.getAttribute('data-close')); });
  });
  // close on overlay click
  document.querySelectorAll('.modal-overlay').forEach(function(ov){
    ov.addEventListener('click', function(e){ if(e.target===ov){ ov.classList.remove('open'); ov.setAttribute('aria-hidden','true'); } });
  });
  // close on Escape
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){
      document.querySelectorAll('.modal-overlay.open').forEach(function(ov){
        ov.classList.remove('open');
        ov.setAttribute('aria-hidden','true');
      });
      document.body.classList.remove('modal-open');
    }
  });
})();
</script>
