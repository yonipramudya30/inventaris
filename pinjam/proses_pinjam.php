<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../config/db.php';

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Metode request tidak valid';
    header('Location: index.php');
    exit();
}

// Validasi CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token keamanan tidak valid';
    header('Location: index.php');
    exit();
}

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    header('Location: /INVENKAS/login.php');
    exit();
}

// Pastikan user memiliki akses
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Anda harus login terlebih dahulu';
    header('Location: /INVENKAS/login.php');
    exit();
}

// Validasi input
$id_barang = isset($_POST['id_barang']) ? (int)$_POST['id_barang'] : 0;
$nama = isset($_POST['nama_peminjam']) ? trim($_POST['nama_peminjam']) : '';
$qty = isset($_POST['jumlah_pinjam']) ? (int)$_POST['jumlah_pinjam'] : 0;

if ($id_barang <= 0 || empty($nama) || $qty <= 0) {
    $_SESSION['error'] = 'Data yang dimasukkan tidak valid';
    header('Location: index.php');
    exit();
}
$conn->begin_transaction();

try {
  $st=$conn->prepare('SELECT jumlah FROM inventaris WHERE id_barang=? FOR UPDATE');
  $st->bind_param('i',$id_barang);
  $st->execute();
  $stok=$st->get_result()->fetch_assoc();
  if(!$stok){ throw new Exception('Barang tidak ditemukan'); }
  if($qty<=0 || $qty>$stok['jumlah']){ throw new Exception('Jumlah pinjam melebihi stok (maks '.$stok['jumlah'].')'); }
  $st2=$conn->prepare('UPDATE inventaris SET jumlah=jumlah-? WHERE id_barang=?');
  $st2->bind_param('ii',$qty,$id_barang);
  $st2->execute();
  $st3=$conn->prepare("INSERT INTO peminjaman(id_barang,nama_peminjam,jumlah_pinjam,status) VALUES(?,?,?,'Dipinjam')");
  $st3->bind_param('isi',$id_barang,$nama,$qty);
  $st3->execute();
  $conn->commit();
  $_SESSION['success'] = 'Peminjaman berhasil disimpan';
} catch (Exception $e) {
  $conn->rollback();
  $_SESSION['error'] = 'Gagal menyimpan peminjaman: ' . $e->getMessage();
  error_log('Error pada peminjaman: ' . $e->getMessage());
}

// Redirect kembali ke halaman peminjaman
header('Location: index.php');
exit();
?>
