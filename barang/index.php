<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
$rows = $conn->query('SELECT * FROM inventaris ORDER BY id_barang DESC');

// Get and clear any error/success messages
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

include __DIR__.'/../includes/header.php';

// Add JavaScript for modal handling
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal handling
    const openAddBtn = document.getElementById('openAdd');
    const modalAdd = document.getElementById('modalAdd');
    
    // Open modal when clicking the button
    if (openAddBtn && modalAdd) {
        openAddBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modalAdd.style.display = 'flex';
            document.body.classList.add('modal-open');
        });
    }

    // Close modal when clicking close button or outside modal
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-close');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    });
});
</script>

<?php
// Display error message if any
if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">Data Barang</h2>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'karyawan' || $_SESSION['role'] === 'admin')): ?>
    <a href="#" id="openAdd" class="btn" style="text-decoration: none;">
        <i class="bi bi-plus-circle"></i> Tambah Barang
    </a>
    <?php endif; ?>
</div>
<!-- Modal Tambah Barang -->
<div id="modalAdd" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addTitle">
    <header>
      <h3 id="addTitle" style="margin:0;">Tambah Barang</h3>
      <button type="button" class="btn secondary" data-close="modalAdd">Tutup</button>
    </header>
    <form class="form compact" method="post" action="tambah.php" enctype="multipart/form-data">
      <div class="form-group">
        <label for="nama_barang">Nama Barang</label>
        <input type="text" id="nama_barang" name="nama_barang" placeholder="Nama Barang" required>
      </div>
      <div class="form-group">
        <label for="kategori">Kategori</label>
        <input type="text" id="kategori" name="kategori" placeholder="Kategori" required>
      </div>
      <div class="form-group">
        <label for="jumlah">Jumlah</label>
        <input type="number" id="jumlah" name="jumlah" placeholder="Jumlah" min="0" required>
      </div>
      <div class="form-group">
        <label for="kondisi">Kondisi</label>
        <select id="kondisi" name="kondisi" required>
          <option value="">-- Pilih Kondisi --</option>
          <option value="Baru">Baru</option>
          <option value="Bekas">Bekas</option>
        </select>
      </div>
      <div class="form-group">
        <label for="lokasi">Lokasi</label>
        <select id="lokasi" name="lokasi" required>
          <option value="">-- Pilih Lokasi --</option>
          <option value="Kantor Pusat (Jerman)">Kantor Pusat (Jerman)</option>
          <option value="Cabang Citayem">Cabang Citayem</option>
          <option value="Cabang Sobang">Cabang Sobang</option>
        </select>
      </div>
      <div class="form-group">
        <label for="gambar">Foto Barang</label>
        <input type="file" id="gambar" name="gambar" accept="image/*" capture="camera">
        <small class="form-text text-muted">Ukuran maksimal 2MB. Format: JPG, PNG, JPEG</small>
      </div>
      <div class="modal-actions">
        <button class="btn" type="submit">Simpan</button>
      </div>
    </form>
  </div>
  </div>


<div class="table-responsive">
<table class="table">
  <thead>
    <tr>
      <th>#</th>
      <th>Gambar</th>
      <th>Kode</th>
      <th>Nama</th>
      <th>Kategori</th>
      <th>Jumlah</th>
      <th>Kondisi</th>
      <th>Lokasi</th>
      <th>Tanggal Input</th>
      <th class="no-print">Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php 
  if($rows) { 
    $no = 1;
    while($r = $rows->fetch_assoc()): 
  ?>
    <tr>
      <td><?= $no++ ?></td>
      <td>
        <?php 
        $gambar_path = '';
        $full_path = '';
        if (!empty($r['gambar'])) {
            // Jika path sudah lengkap (mengandung 'barang/')
            if (strpos($r['gambar'], 'barang/') === 0) {
                $gambar_path = '/INVENKAS/uploads/' . $r['gambar'];
                $full_path = $_SERVER['DOCUMENT_ROOT'] . '/INVENKAS/uploads/' . $r['gambar'];
            } 
            // Jika hanya nama file
            else {
                $gambar_path = '/INVENKAS/uploads/barang/' . $r['gambar'];
                $full_path = $_SERVER['DOCUMENT_ROOT'] . '/INVENKAS/uploads/barang/' . $r['gambar'];
            }
        }
        
        if (!empty($r['gambar']) && file_exists($full_path)): 
        ?>
          <img src="<?= $gambar_path ?>" 
               alt="<?= htmlspecialchars($r['nama_barang']) ?>" 
               style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
               onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23f5f5f5%22%2F%3E%3Ctext%20x%3D%2225%22%20y%3D%2228%22%20font-family%3D%22Arial%22%20font-size%3D%2212%22%20text-anchor%3D%22middle%22%20fill%3D%22%23999%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fsvg%3E';"
               loading="lazy">
        <?php else: ?>
          <div style="width: 50px; height: 50px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Gambar tidak ditemukan">
            <i class="fas fa-image" style="font-size: 20px; color: #999;"></i>
          </div>
        <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($r['kode_barang']) ?></td>
      <td><?= htmlspecialchars($r['nama_barang']) ?></td>
      <td><?= htmlspecialchars($r['kategori']) ?></td>
      <td><?= (int)$r['jumlah'] ?></td>
      <td>
        <span class="badge <?= $r['kondisi'] === 'Baru' ? 'badge-success' : 'badge-warning' ?>">
          <?= htmlspecialchars($r['kondisi']) ?>
        </span>
      </td>
      <td><?= htmlspecialchars($r['lokasi']) ?></td>
      <td><?= date('d/m/Y', strtotime($r['tanggal_input'])) ?></td>
      <td class="no-print" style="white-space: nowrap;">
        <a class="btn btn-sm btn-primary" href="edit.php?id=<?= (int)$r['id_barang'] ?>">
          <i class="fas fa-edit"></i> Edit
        </a>
        <a class="btn btn-sm btn-danger" href="hapus.php?id=<?= (int)$r['id_barang'] ?>" onclick="return confirm('Yakin ingin menghapus barang ini?')">
          <i class="fas fa-trash"></i> Hapus
        </a>
      </td>
    </tr>
  <?php 
    endwhile; 
  } else { 
  ?>
    <tr>
      <td colspan="10" class="text-center">Tidak ada data barang</td>
    </tr>
  <?php } ?>
  </tbody>
</table>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>
<script>
(function(){
  function openModal(id){
    var el=document.getElementById(id);
    if(el){ el.classList.add('open'); el.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open');
      var first=el.querySelector('input,select,button'); if(first){ try{first.focus();}catch(e){} }
    }
  }
  function closeModal(id){
    var el=document.getElementById(id);
    if(el){ el.classList.remove('open'); el.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); }
  }
  var btn=document.getElementById('openAdd');
  if(btn){ btn.addEventListener('click', function(){ openModal('modalAdd'); }); }
  document.querySelectorAll('[data-close]').forEach(function(b){ b.addEventListener('click', function(){ closeModal(b.getAttribute('data-close')); }); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeModal('modalAdd'); } });
  document.querySelectorAll('.modal-overlay').forEach(function(ov){ ov.addEventListener('click', function(e){ if(e.target===ov){ closeModal('modalAdd'); } }); });
})();
</script>
