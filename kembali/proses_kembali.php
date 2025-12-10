<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$conn->begin_transaction();
try{
  // Safety: tambahkan kolom tanggal_kembali jika belum ada
  try{ $conn->query("ALTER TABLE peminjaman ADD COLUMN tanggal_kembali DATETIME NULL"); }catch(Exception $e){}
  $st=$conn->prepare("SELECT id_barang,jumlah_pinjam,status FROM peminjaman WHERE id_pinjam=? FOR UPDATE");
  $st->bind_param('i',$id);
  $st->execute();
  $row=$st->get_result()->fetch_assoc();
  if(!$row || $row['status']!=='Dipinjam'){ throw new Exception('Transaksi tidak valid'); }
  $st2=$conn->prepare('UPDATE inventaris SET jumlah=jumlah+? WHERE id_barang=?');
  $st2->bind_param('ii',$row['jumlah_pinjam'],$row['id_barang']);
  $st2->execute();
  $st3=$conn->prepare("UPDATE peminjaman SET status='Dikembalikan', tanggal_kembali=NOW() WHERE id_pinjam=?");
  $st3->bind_param('i',$id);
  $st3->execute();
  $conn->commit();
  header('Location: index.php');
}catch(Exception $e){
  $conn->rollback();
  header('Location: index.php');
}
?>
