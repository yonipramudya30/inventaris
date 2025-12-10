<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: index.php'); exit; }
$id=(int)($_POST['id']??0);
$alasan=trim($_POST['alasan']??'');
if(!$id || !$alasan){ header('Location: index.php'); exit; }

$conn->begin_transaction();
try{
  // Ambil data barang sebelum dihapus
  $st=$conn->prepare('SELECT * FROM inventaris WHERE id_barang=? FOR UPDATE');
  $st->bind_param('i',$id);
  $st->execute();
  $barang=$st->get_result()->fetch_assoc();
  if(!$barang){ throw new Exception('Data barang tidak ditemukan'); }

  // Simpan log penghapusan
  $ins=$conn->prepare('INSERT INTO penghapusan(id_barang,kode_barang,nama_barang,kategori,jumlah_terakhir,kondisi,lokasi,alasan) VALUES(?,?,?,?,?,?,?,?)');
  $ins->bind_param(
    'isssisss',
    $barang['id_barang'],
    $barang['kode_barang'],
    $barang['nama_barang'],
    $barang['kategori'],
    $barang['jumlah'],
    $barang['kondisi'],
    $barang['lokasi'],
    $alasan
  );
  $ins->execute();

  // Hapus barang
  $del=$conn->prepare('DELETE FROM inventaris WHERE id_barang=?');
  $del->bind_param('i',$id);
  $del->execute();

  $conn->commit();
  header('Location: index.php');
}catch(Exception $e){
  $conn->rollback();
  header('Location: index.php');
}
