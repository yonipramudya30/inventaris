<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)$_POST['id'];
  $kode=trim($_POST['kode_barang']);
  $nama=trim($_POST['nama_barang']);
  $kategori=trim($_POST['kategori']);
  $jumlah=(int)$_POST['jumlah'];
  $kondisi=trim($_POST['kondisi']);
  $lokasi=trim($_POST['lokasi']);
  // Ambil data saat ini untuk membandingkan stok
  $cur=$conn->prepare('SELECT * FROM inventaris WHERE id_barang=?');
  $cur->bind_param('i',$id);
  $cur->execute();
  $curr=$cur->get_result()->fetch_assoc();
  $qty_awal = $curr ? (int)$curr['jumlah'] : null;
  if($curr===null){
    $err='Data tidak ditemukan';
  } else {
    // Jika stok berkurang, wajib alasan dan catat ke penyesuaian_stok
    if($jumlah < $qty_awal){
      $alasan = trim($_POST['alasan_stok'] ?? '');
      $pilihan = ['Barang sudah tidak layak pakai','Barang rusak','Barang hilang'];
      if(!$alasan || !in_array($alasan,$pilihan,true)){
        $err='Mohon pilih alasan pengurangan stok.';
      } else {
        $selisih = $jumlah - $qty_awal; // nilai negatif
        $ins=$conn->prepare('INSERT INTO penyesuaian_stok(id_barang,kode_barang,nama_barang,qty_awal,qty_baru,selisih,alasan) VALUES(?,?,?,?,?,?,?)');
        $ins->bind_param('issiiis',$id,$curr['kode_barang'],$curr['nama_barang'],$qty_awal,$jumlah,$selisih,$alasan);
        $ins->execute();
      }
    }
    if(!$err){
      $stmt=$conn->prepare('UPDATE inventaris SET kode_barang=?, nama_barang=?, kategori=?, jumlah=?, kondisi=?, lokasi=? WHERE id_barang=?');
      $stmt->bind_param('sssissi',$kode,$nama,$kategori,$jumlah,$kondisi,$lokasi,$id);
      if($stmt->execute()){ header('Location: index.php'); exit; }
      else { $err='Gagal memperbarui data'; }
    }
  }
}
$st=$conn->prepare('SELECT * FROM inventaris WHERE id_barang=?');
$st->bind_param('i',$id);
$st->execute();
$data=$st->get_result()->fetch_assoc();
include __DIR__.'/../includes/header.php';
?>
<h2>Edit Barang</h2>
<?php if(!$data): ?><div>Data tidak ditemukan</div><?php include __DIR__.'/../includes/footer.php'; exit; endif; ?>
<?php if($err): ?><div style="color:#ef4444;"><?= htmlspecialchars($err) ?></div><?php endif; ?>
<form class="form" method="post">
  <input type="hidden" name="id" value="<?= $data['id_barang'] ?>">
  <input type="hidden" id="original_jumlah" value="<?= (int)$data['jumlah'] ?>">
  <input name="kode_barang" value="<?= htmlspecialchars($data['kode_barang']) ?>" required>
  <input name="nama_barang" value="<?= htmlspecialchars($data['nama_barang']) ?>" required>
  <input name="kategori" value="<?= htmlspecialchars($data['kategori']) ?>" required>
  <input type="number" id="input_jumlah" name="jumlah" value="<?= (int)$data['jumlah'] ?>" min="0" required>
  <input name="kondisi" value="<?= htmlspecialchars($data['kondisi']) ?>" required>
  <input name="lokasi" value="<?= htmlspecialchars($data['lokasi']) ?>" required>
  <div id="wrap_alasan" style="display:none;">
    <select name="alasan_stok">
      <option value="">-- Pilih Alasan Pengurangan Stok --</option>
      <option value="Barang sudah tidak layak pakai">Barang sudah tidak layak pakai</option>
      <option value="Barang rusak">Barang rusak</option>
      <option value="Barang hilang">Barang hilang</option>
    </select>
  </div>
  <button class="btn" type="submit">Simpan</button>
  <a class="btn secondary" href="index.php">Batal</a>
</form>
<script>
(function(){
  var original = parseInt(document.getElementById('original_jumlah').value,10);
  var input = document.getElementById('input_jumlah');
  var wrap = document.getElementById('wrap_alasan');
  var alasan = document.querySelector('select[name="alasan_stok"]');
  function toggleReason(){
    var val = parseInt(input.value||'0',10);
    if(!isNaN(val) && val < original){
      wrap.style.display='block';
      alasan.setAttribute('required','required');
    }else{
      wrap.style.display='none';
      alasan.removeAttribute('required');
      if(alasan) alasan.value='';
    }
  }
  input.addEventListener('input', toggleReason);
  toggleReason();
})();
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
