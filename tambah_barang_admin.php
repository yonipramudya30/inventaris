<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include file konfigurasi dan autentikasi
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Inisialisasi variabel
$success_message = '';
$error_message = '';

// Fungsi untuk upload gambar
function uploadGambar($file) {
    // Cek error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Cek ukuran file (maks 2MB)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return null;
    }
    
    // Cek tipe file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedTypes)) {
        return null;
    }
    
    // Buat direktori upload jika belum ada
    $uploadDir = __DIR__ . '/uploads/barang/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate nama file unik
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('img_') . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    // Pindahkan file ke direktori upload
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'barang/' . $filename;
    }
    
    return null;
}

// Proses form tambah barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_barang'])) {
    // Ambil data dari form
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $kondisi = trim($_POST['kondisi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $gambar = null;
    
    // Handle upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $gambar = uploadGambar($_FILES['gambar']);
    }
    
    // Validasi input
    if ($nama_barang && $kategori && $jumlah > 0 && $kondisi && $lokasi) {
        // Generate kode barang
        $kode_barang = 'BRG-' . strtoupper(uniqid());
        
        try {
            // Siapkan query SQL
            if ($gambar) {
                $sql = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi, gambar) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiss", $kode_barang, $nama_barang, $kategori, $jumlah, $kondisi, $lokasi, $gambar);
            } else {
                $sql = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiss", $kode_barang, $nama_barang, $kategori, $jumlah, $kondisi, $lokasi);
            }
            
            // Eksekusi query
            if ($stmt->execute()) {
                $success_message = "Barang berhasil ditambahkan dengan kode: $kode_barang";
                // Reset form
                $_POST = [];
            } else {
                $error_message = "Gagal menambahkan barang: " . $conn->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error_message = "Semua field wajib diisi dengan benar";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - INVENKAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #eee; }
        .preview-image { max-width: 200px; margin-top: 10px; display: none; }
        .form-label.required:after { content: ' *'; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Barang Baru</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="tambah_barang" value="1">
                            
                            <div class="mb-3">
                                <label for="nama_barang" class="form-label required">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" 
                                       value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>" required>
                                <div class="invalid-feedback">Harap isi nama barang</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label required">Kategori</label>
                                    <input type="text" class="form-control" id="kategori" name="kategori" 
                                           value="<?= htmlspecialchars($_POST['kategori'] ?? '') ?>" required>
                                    <div class="invalid-feedback">Harap isi kategori</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jumlah" class="form-label required">Jumlah</label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" 
                                           min="1" value="<?= htmlspecialchars($_POST['jumlah'] ?? '1') ?>" required>
                                    <div class="invalid-feedback">Jumlah minimal 1</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kondisi" class="form-label required">Kondisi</label>
                                    <select class="form-select" id="kondisi" name="kondisi" required>
                                        <option value="Baik" <?= ($_POST['kondisi'] ?? '') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                        <option value="Rusak Ringan" <?= ($_POST['kondisi'] ?? '') === 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                                        <option value="Rusak Berat" <?= ($_POST['kondisi'] ?? '') === 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                                        <option value="Perlu Perbaikan" <?= ($_POST['kondisi'] ?? '') === 'Perlu Perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="lokasi" class="form-label required">Lokasi</label>
                                    <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                           value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>" required>
                                    <div class="invalid-feedback">Harap isi lokasi</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="gambar" class="form-label">Gambar Barang</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" 
                                       accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">Format: JPG, PNG, atau GIF (maks. 2MB)</small>
                                <img id="imagePreview" class="img-thumbnail mt-2 preview-image" alt="Preview Gambar">
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/INVENKAS/admin/barang.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="reset" class="btn btn-warning me-md-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Barang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Fungsi untuk preview gambar
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
        }
    }

    // Validasi form
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>

<?php
// Tutup koneksi database
if (isset($conn) && $conn) {
    $conn->close();
}
?>
