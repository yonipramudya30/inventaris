<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';

// Debug session
// Hanya admin dan karyawan yang boleh menambah barang
if (!isset($_SESSION['role'])) {
    // Debug: Tampilkan isi session
    error_log('Session role tidak ditemukan. Isi session: ' . print_r($_SESSION, true));
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengakses halaman ini. Silakan login terlebih dahulu.';
    header('Location: /INVENKAS/login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'karyawan') {
    error_log('Akses ditolak untuk role: ' . $_SESSION['role']);
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk menambahkan barang.';
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/INVENKAS/dashboard/');
    exit();
}

function getNextKode(mysqli $conn): string {
  $sql = "SELECT MAX(CAST(SUBSTRING(kode_barang,5) AS UNSIGNED)) AS maxnum FROM inventaris WHERE kode_barang LIKE 'BRG-%'";
  $res = $conn->query($sql);
  $row = $res ? $res->fetch_assoc() : null;
  $next = (int)($row['maxnum'] ?? 0) + 1;
  return 'BRG-'.str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

function uploadGambar($file) {
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Check file size (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        return null;
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedTypes)) {
        return null;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/barang/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'barang/' . $filename; // Return relative path
    }
    
    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Metode request tidak valid';
    header('Location: /INVENKAS/admin/barang.php');
    exit;
}

// Debug: Tampilkan data POST yang diterima
error_log('Data POST: ' . print_r($_POST, true));
error_log('Data FILES: ' . print_r($_FILES, true));

// Generate kode barang
try {
    $kode = getNextKode($conn);
    $nama = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $kondisi = trim($_POST['kondisi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $gambar = null;
    
    // Debug: Tampilkan data yang akan diproses
    error_log("Mencoba menambahkan barang: $nama, Kategori: $kategori, Jumlah: $jumlah, Kondisi: $kondisi, Lokasi: $lokasi");
} catch (Exception $e) {
    error_log('Error saat memproses data: ' . $e->getMessage());
    $_SESSION['error'] = 'Terjadi kesalahan saat memproses data';
    header('Location: index.php');
    exit;
}

// Handle file upload
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $gambar = uploadGambar($_FILES['gambar']);
}

if ($nama && $kategori && $jumlah >= 0 && $kondisi && $lokasi) {
    if ($gambar !== null) {
        // Check if image upload was successful
        if ($gambar === false) {
            // Handle upload error
            header('Location: index.php?error=upload');
            exit;
        }
        // Save the item with the image path
        $sql = 'INSERT INTO inventaris(kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi, gambar) VALUES(?, ?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error) . ' SQL: ' . $sql);
        }
        $stmt->bind_param('sssisss', $kode, $nama, $kategori, $jumlah, $kondisi, $lokasi, $gambar);
    } else {
        $sql = 'INSERT INTO inventaris(kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi) VALUES(?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error) . ' SQL: ' . $sql);
        }
        $stmt->bind_param('sssiss', $kode, $nama, $kategori, $jumlah, $kondisi, $lokasi);
    }
    
        if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        error_log("Barang berhasil ditambahkan dengan ID: $last_id");
        $_SESSION['success'] = 'Barang berhasil ditambahkan';
    } else {
        $error_msg = 'Gagal menambahkan barang: ' . $stmt->error;
        error_log($error_msg);
        $_SESSION['error'] = $error_msg;
    }
    
    // Redirect ke halaman daftar barang
    header('Location: /INVENKAS/admin/barang.php');
    exit;
}

// If validation failed, redirect back with error message
$error = 'Harap isi semua field yang wajib diisi';
if (empty($nama)) $error = 'Nama barang harus diisi';
else if (empty($kategori)) $error = 'Kategori harus diisi';
else if ($jumlah < 0) $error = 'Jumlah tidak valid';
else if (empty($kondisi)) $error = 'Kondisi harus dipilih';
else if (empty($lokasi)) $error = 'Lokasi harus dipilih';

error_log('Validasi gagal: ' . $error);
$_SESSION['error'] = $error;
header('Location: /INVENKAS/barang/tambah_barang.php');
exit;
