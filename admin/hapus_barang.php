<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID barang tidak valid';
    header('Location: barang.php');
    exit();
}

$id_barang = (int)$_GET['id'];

// Cek apakah barang ada
$stmt = $conn->prepare("SELECT id_barang FROM inventaris WHERE id_barang = ?");
$stmt->bind_param("i", $id_barang);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Barang tidak ditemukan';
    header('Location: barang.php');
    exit();
}

// Hapus barang
$stmt = $conn->prepare("DELETE FROM inventaris WHERE id_barang = ?");
$stmt->bind_param("i", $id_barang);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Barang berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus barang: ' . $conn->error;
}

header('Location: barang.php');
exit();
