<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$error = '';
$success = '';

// Inisialisasi variabel form
$kode_barang = '';
$nama_barang = '';
$kategori = '';
$jumlah = '';
$kondisi = 'Baik';
$lokasi = '';
$deskripsi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $kondisi = trim($_POST['kondisi'] ?? 'Baik');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // Validasi input
    if (empty($kode_barang) || empty($nama_barang) || empty($kategori) || $jumlah <= 0) {
        $error = 'Semua field wajib diisi dan jumlah harus lebih dari 0';
    } else {
        try {
            // Cek apakah kode barang sudah ada
            $stmt = $conn->prepare("SELECT id_barang FROM inventaris WHERE kode_barang = ?");
            $stmt->bind_param("s", $kode_barang);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Kode barang sudah digunakan';
            } else {
                // Handle upload gambar
                $gambar = '';
                if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                    $target_dir = __DIR__ . "/../uploads/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    // Validasi tipe file
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($file_extension, $allowed_types)) {
                        $error = 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan';
                    } elseif (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                        $gambar = '/INVENKAS/uploads/' . $new_filename;
                    } else {
                        $error = 'Gagal mengupload gambar';
                    }
                }
                
                if (empty($error)) {
                    // Pastikan semua parameter terisi
                    $params = [
                        'kode_barang' => $kode_barang,
                        'nama_barang' => $nama_barang,
                        'kategori' => $kategori,
                        'jumlah' => $jumlah,
                        'kondisi' => $kondisi,
                        'lokasi' => $lokasi,
                        'deskripsi' => $deskripsi,
                        'gambar' => $gambar
                    ];

                    // Validasi tipe data
                    if (!is_numeric($params['jumlah']) || $params['jumlah'] <= 0) {
                        throw new Exception('Jumlah harus berupa angka lebih dari 0');
                    }

                    // Pastikan koneksi database aktif
                    if ($conn->connect_error) {
                        throw new Exception('Koneksi database gagal: ' . $conn->connect_error);
                    }

                    // Cek apakah tabel ada
                    $table_check = $conn->query("SHOW TABLES LIKE 'inventaris'");
                    if ($table_check->num_rows == 0) {
                        throw new Exception('Tabel inventaris tidak ditemukan di database');
                    }

                    // Pertama, periksa koneksi database
                    if (!$conn) {
                        throw new Exception('Tidak ada koneksi database. Silakan periksa konfigurasi database Anda.');
                    }

                    // Periksa apakah tabel ada
                    $table_check = $conn->query("SHOW TABLES LIKE 'inventaris'");
                    if ($table_check->num_rows == 0) {
                        throw new Exception('Tabel inventaris tidak ditemukan di database. Pastikan Anda sudah menjalankan skema database yang benar.');
                    }

                    // Dapatkan daftar kolom yang ada di tabel
                    $columns = $conn->query("SHOW COLUMNS FROM inventaris");
                    $column_names = [];
                    while($column = $columns->fetch_assoc()) {
                        $column_names[] = $column['Field'];
                    }

                    // Buat query berdasarkan kolom yang ada
                    $sql_columns = [];
                    $placeholders = [];
                    $types = '';
                    $values = [];

                    // Mapping field yang akan diinsert
                    $fields = [
                        'kode_barang' => ['type' => 's', 'value' => $params['kode_barang']],
                        'nama_barang' => ['type' => 's', 'value' => $params['nama_barang']],
                        'kategori' => ['type' => 's', 'value' => $params['kategori']],
                        'jumlah' => ['type' => 'i', 'value' => $params['jumlah']],
                        'kondisi' => ['type' => 's', 'value' => $params['kondisi']],
                        'lokasi' => ['type' => 's', 'value' => $params['lokasi']],
                        'deskripsi' => ['type' => 's', 'value' => $params['deskripsi']],
                        'gambar' => ['type' => 's', 'value' => $params['gambar']]
                    ];

                    // Siapkan kolom, placeholder, dan nilai yang akan diinsert
                    foreach ($fields as $field => $data) {
                        if (in_array($field, $column_names)) {
                            $sql_columns[] = $field;
                            $placeholders[] = '?';
                            $types .= $data['type'];
                            $values[] = $data['value'];
                        }
                    }

                    // Buat query SQL
                    $sql = "INSERT INTO inventaris (" . implode(', ', $sql_columns) . ") 
                            VALUES (" . implode(', ', $placeholders) . ")";

                    // Debug
                    error_log("SQL: " . $sql);
                    error_log("Params: " . print_r($params, true));

                    // Debug: Tampilkan query dan parameter
                    error_log("SQL: " . $sql);
                    error_log("Parameter types: " . $types);
                    error_log("Parameter values: " . print_r($values, true));

                    // Siapkan statement
                    $stmt = $conn->prepare($sql);
                    if ($stmt === false) {
                        throw new Exception('Gagal mempersiapkan query: ' . $conn->error . '\nQuery: ' . $sql);
                    }

                    // Bind parameter
                    $bind_names[] = $types;
                    for ($i = 0; $i < count($values); $i++) {
                        $bind_name = 'bind' . $i;
                        $$bind_name = $values[$i];
                        $bind_names[] = &$$bind_name;
                    }
                    
                    call_user_func_array(array($stmt, 'bind_param'), $bind_names);

                    // Eksekusi query
                    if (!$stmt->execute()) {
                        throw new Exception('Gagal mengeksekusi query: ' . $stmt->error);
                    }

                    $_SESSION['success'] = 'Barang berhasil ditambahkan';
                    header('Location: barang.php');
                    exit();
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
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
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tambah Barang Baru</h2>
            <a href="barang.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="<?= htmlspecialchars($kode_barang) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($nama_barang) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kategori" name="kategori" value="<?= htmlspecialchars($kategori) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="<?= $jumlah > 0 ? $jumlah : '' ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kondisi" class="form-label">Kondisi</label>
                            <select class="form-select" id="kondisi" name="kondisi">
                                <option value="Baik" <?= $kondisi === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                <option value="Rusak Ringan" <?= $kondisi === 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                                <option value="Rusak Berat" <?= $kondisi === 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                                <option value="Perlu Perbaikan" <?= $kondisi === 'Perlu Perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= htmlspecialchars($lokasi) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="gambar" class="form-label">Gambar Barang</label>
                        <div class="mb-2">
                            <img id="imagePreview" src="" alt="Preview Gambar" class="img-thumbnail d-none" style="max-width: 200px; max-height: 200px;">
                        </div>
                        <input class="form-control" type="file" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this);">
                        <div class="form-text">Format yang didukung: JPG, JPEG, PNG, GIF (Maks. 2MB)</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
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
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
                preview.classList.remove('d-none');
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
                preview.classList.add('d-none');
            }
        }
    </script>
</body>
</html>