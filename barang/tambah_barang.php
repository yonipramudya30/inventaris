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

// Debug: Tampilkan error jika ada
if (isset($_SESSION['error'])) {
    error_log('Error: ' . $_SESSION['error']);
}

// Hanya admin dan karyawan yang boleh menambah barang
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'karyawan')) {
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengakses halaman ini.';
    header('Location: /INVENKAS/login.php');
    exit();
}

// Ambil daftar kategori untuk dropdown
$kategori = [];
$result = $conn->query("SELECT DISTINCT kategori FROM inventaris WHERE kategori IS NOT NULL AND kategori != ''");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $kategori[] = $row['kategori'];
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
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle"></i> Tambah Barang Baru</h2>
            <a href="/INVENKAS/barang/" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Barang
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="tambah.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                        </div>
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kategori" name="kategori" list="kategori-list" required>
                            <datalist id="kategori-list">
                                <?php foreach ($kategori as $kat): ?>
                                    <option value="<?= htmlspecialchars($kat) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="kondisi" class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select class="form-select" id="kondisi" name="kondisi" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Barang</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this)">
                        <small class="text-muted">Format: JPG, PNG, atau GIF (maks. 2MB)</small>
                        <img id="imagePreview" class="preview-image img-thumbnail mt-2" alt="Preview Gambar">
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const nama = document.getElementById('nama_barang').value;
            const kategori = document.getElementById('kategori').value;
            const jumlah = document.getElementById('jumlah').value;
            const kondisi = document.getElementById('kondisi').value;
            const lokasi = document.getElementById('lokasi').value;
            
            if (!nama || !kategori || !jumlah || !kondisi || !lokasi) {
                alert('Harap isi semua field yang wajib diisi!');
                return false;
            }
            return true;
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }

        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('gambar');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                if (fileSize > 2) {
                    e.preventDefault();
                    alert('Ukuran gambar tidak boleh melebihi 2MB');
                    return false;
                }
            }
            return true;
        });
    </script>
</body>
</html>
