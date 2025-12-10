<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Fungsi untuk upload gambar
function uploadGambar($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        return null;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedTypes)) {
        return null;
    }
    
    $uploadDir = __DIR__ . '/uploads/barang/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'barang/' . $filename;
    }
    
    return null;
}

// Proses form tambah barang
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_barang'])) {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $kondisi = trim($_POST['kondisi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $gambar = null;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $gambar = uploadGambar($_FILES['gambar']);
    }
    
    if ($nama_barang && $kategori && $jumlah > 0 && $kondisi && $lokasi) {
        $kode_barang = 'BRG-' . strtoupper(uniqid());
        
        if ($gambar) {
            $sql = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiss", $kode_barang, $nama_barang, $kategori, $jumlah, $kondisi, $lokasi, $gambar);
        } else {
            $sql = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiss", $kode_barang, $nama_barang, $kategori, $jumlah, $kondisi, $lokasi);
        }
        
        if ($stmt->execute()) {
            $success_message = "Barang berhasil ditambahkan dengan kode: $kode_barang";
        } else {
            $error_message = "Gagal menambahkan barang: " . $conn->error;
        }
    } else {
        $error_message = "Semua field harus diisi dengan benar";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tambah Barang - INVENKAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { padding: 20px; }
        .preview-image { max-width: 200px; margin-top: 10px; display: none; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Fungsi untuk upload gambar
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
    $uploadDir = __DIR__ . '/uploads/barang/';
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

// Proses form tambah barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_barang'])) {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $kondisi = trim($_POST['kondisi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $gambar = null;
    
    // Handle file upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $gambar = uploadGambar($_FILES['gambar']);
    }
    
    if ($nama_barang && $kategori && $jumlah > 0 && $kondisi && $lokasi) {
        // Generate kode barang
        $kode_barang = 'BRG-' . strtoupper(uniqid());
        
        // Insert ke database
        if ($gambar) {
            $sql = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiss", $kode_barang, $nama_barang, $kategori, $jumlah, $kondisi, $lokasi, $gambar);
        } else {
            $sql = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiss", $kode_barang, $nama_barang, $kategori, $jumlah, $kondisi, $lokasi);
        }
        
        if ($stmt->execute()) {
            $success_message = "Barang berhasil ditambahkan dengan kode: $kode_barang";
        } else {
            $error_message = "Gagal menambahkan barang: " . $conn->error;
        }
    } else {
        $error_message = "Semua field harus diisi dengan benar";
    }
}

echo "<div class='container mt-4'>";

// Tampilkan pesan sukses/error
if (isset($success_message)) {
    echo "<div class='alert alert-success'>$success_message</div>";
}
if (isset($error_message)) {
    echo "<div class='alert alert-danger'>$error_message</div>";
}

// Tampilkan informasi koneksi untuk debugging
echo "<div class='card mb-4'>";
echo "<div class='card-header'><h3>Informasi Koneksi Database:</h3></div>";
echo "<div class='card-body'>";
echo "<p>Host: " . DB_HOST . "</p>";
echo "<p>Database: " . DB_NAME . "</p>";
echo "<p>User: " . DB_USER . "</p>";
echo "</div></div>";

// Data admin
$username = 'admin';
$password = 'admin123'; // Password yang diinginkan
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Informasi Akun:</h3>";
echo "Username: " . $username . "<br>";
echo "Password (plain): " . $password . "<br>";
echo "Password (hashed): " . $hashed_password . "<br><br>";

try {
    // Cek apakah tabel users ada
    $check_table = $conn->query("SHOW TABLES LIKE 'users'");
    if ($check_table->num_rows == 0) {
        throw new Exception("Tabel 'users' tidak ditemukan di database!");
    }

    // Dapatkan struktur tabel untuk debugging
    $result = $conn->query("DESCRIBE users");
    echo "<h3>Struktur Tabel Users:</h3>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";

    // Cek apakah user sudah ada
    $check_user = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check_user->bind_param("s", $username);
    $check_user->execute();
    $user_exists = $check_user->get_result();

    if ($user_exists->num_rows > 0) {
        echo "<p>User dengan username '$username' sudah ada. Memperbarui password...</p>";
        $sql = "UPDATE users SET password = ?, role = 'admin', nama_lengkap = 'Administrator', email = 'admin@example.com' WHERE username = ?";
    } else {
        echo "<p>Membuat user admin baru...</p>";
        $sql = "INSERT INTO users (username, password, role, nama_lengkap, email) VALUES (?, ?, 'admin', 'Administrator', 'admin@example.com')";
    }

    $stmt = $conn->prepare($sql);
    
    if ($user_exists->num_rows > 0) {
        $stmt->bind_param("ss", $hashed_password, $username);
    } else {
        $stmt->bind_param("ss", $username, $hashed_password);
    }

    if ($stmt->execute()) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 4px;'>";
        echo "<h3>✅ Berhasil!</h3>";
        echo "<p>Akun admin telah berhasil dibuat/diperbarui.</p>";
        echo "<p><strong>Silakan login dengan:</strong></p>";
        echo "<ul>";
        echo "<li>Username: <strong>admin</strong></li>";
        echo "<li>Password: <strong>admin123</strong></li>";
        echo "</ul>";
        echo "<p><a href='login.php' style='display: inline-block; background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Klik di sini untuk login</a></p>";
        echo "</div>";
        
        // Tampilkan data yang disimpan di database
        $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<h3>Data yang tersimpan di database:</h3>";
            echo "<pre>" . print_r($user, true) . "</pre>";
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>✅ Password berhasil diverifikasi!</p>";
            } else {
                echo "<p style='color: red;'>❌ Gagal memverifikasi password!</p>";
                echo "<p>Password yang dicoba: $password</p>";
                echo "<p>Hash yang disimpan: " . $user['password'] . "</p>";
            }
        }
    } else {
        throw new Exception("Error: " . $stmt->error);
    }
}

// Form Tambah Barang
echo "<div class='card mb-4'>";
echo "<div class='card-header'><h3><i class='bi bi-plus-circle'></i> Tambah Barang Baru</h3></div>";
echo "<div class='card-body'>";
echo "<form method='POST' enctype='multipart/form-data' class='needs-validation' novalidate>";
echo "<input type='hidden' name='tambah_barang' value='1'>";

echo "<div class='row mb-3'>";
echo "<div class='col-md-6'>";
echo "<label for='nama_barang' class='form-label'>Nama Barang <span class='text-danger'>*</span></label>";
echo "<input type='text' class='form-control' id='nama_barang' name='nama_barang' required>";
echo "<div class='invalid-feedback'>Harap isi nama barang</div>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<label for='kategori' class='form-label'>Kategori <span class='text-danger'>*</span></label>";
echo "<input type='text' class='form-control' id='kategori' name='kategori' required>";
echo "<div class='invalid-feedback'>Harap isi kategori</div>";
echo "</div>";
echo "</div>";

echo "<div class='row mb-3'>";
echo "<div class='col-md-4'>";
echo "<label for='jumlah' class='form-label'>Jumlah <span class='text-danger'>*</span></label>";
echo "<input type='number' class='form-control' id='jumlah' name='jumlah' min='1' value='1' required>";
echo "<div class='invalid-feedback'>Jumlah minimal 1</div>";
echo "</div>";

echo "<div class='col-md-4'>";
echo "<label for='kondisi' class='form-label'>Kondisi <span class='text-danger'>*</span></label>";
echo "<select class='form-select' id='kondisi' name='kondisi' required>";
echo "<option value='Baik'>Baik</option>";
echo "<option value='Rusak Ringan'>Rusak Ringan</option>";
echo "<option value='Rusak Berat'>Rusak Berat</option>";
echo "<option value='Perlu Perbaikan'>Perlu Perbaikan</option>";
echo "</select>";
echo "</div>";

echo "<div class='col-md-4'>";
echo "<label for='lokasi' class='form-label'>Lokasi <span class='text-danger'>*</span></label>";
echo "<input type='text' class='form-control' id='lokasi' name='lokasi' required>";
echo "<div class='invalid-feedback'>Harap isi lokasi</div>";
echo "</div>";
echo "</div>";

echo "<div class='mb-3'>";
echo "<label for='gambar' class='form-label'>Gambar Barang</label>";
echo "<input type='file' class='form-control' id='gambar' name='gambar' accept='image/*' onchange='previewImage(this)'>";
echo "<small class='text-muted'>Format: JPG, PNG, atau GIF (maks. 2MB)</small>";
echo "<img id='imagePreview' class='img-thumbnail mt-2 d-none' style='max-width: 200px; max-height: 200px;' alt='Preview Gambar'>";
echo "</div>";

echo "<div class='d-grid gap-2 d-md-flex justify-content-md-end'>";
echo "<button type='reset' class='btn btn-secondary me-md-2'>Reset</button>";
echo "<button type='submit' class='btn btn-primary'>";
echo "<i class='bi bi-save'></i> Simpan Barang";
echo "</button>";
echo "</div>";

echo "</form>";
echo "</div></div>";

// Tampilkan daftar barang
try {
    $result = $conn->query("SELECT * FROM inventaris ORDER BY id_barang DESC LIMIT 10");
    if ($result && $result->num_rows > 0) {
        echo "<div class='card'>";
        echo "<div class='card-header'><h3>Daftar Barang Terbaru</h3></div>";
        echo "<div class='card-body'>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-hover'>";
        echo "<thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Jumlah</th><th>Kondisi</th><th>Lokasi</th></tr></thead>";
        echo "<tbody>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['kode_barang']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
            echo "<td>" . $row['jumlah'] . "</td>";
            echo "<td>" . htmlspecialchars($row['kondisi']) . "</td>";
            echo "<td>" . htmlspecialchars($row['lokasi']) . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
        echo "<div class='text-end mt-3'>";
        echo "<a href='/INVENKAS/barang/' class='btn btn-sm btn-outline-primary'>Lihat Semua Barang</a>";
        echo "</div>";
        echo "</div></div>";
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px;'>";
    echo "<h3>❌ Terjadi Kesalahan!</h3>";
    echo "<div class='alert alert-warning'>Error menampilkan daftar barang: " . $e->getMessage() . "</div>";
}

// JavaScript untuk preview gambar dan validasi form
echo "
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
<script>
// Preview gambar
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            preview.classList.add('d-block');
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.classList.remove('d-block');
        preview.classList.add('d-none');
    }
}
</script<?php if (isset($success_message) && $success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if (isset($error_message) && $error_message): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="bi bi-plus-circle"></i> Tambah Barang Baru</h2>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="tambah_barang" value="1">
                
                <div class="mb-3">
                    <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                    <div class="invalid-feedback">Harap isi nama barang</div>
                </div>
                
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kategori" name="kategori" required>
                    <div class="invalid-feedback">Harap isi kategori</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="1" required>
                        <div class="invalid-feedback">Jumlah minimal 1</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="kondisi" class="form-label">Kondisi <span class="text-danger">*</span></label>
                        <select class="form-select" id="kondisi" name="kondisi" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                    <div class="invalid-feedback">Harap isi lokasi</div>
                </div>
                
                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Barang</label>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted">Format: JPG, PNG, atau GIF (maks. 2MB)</small>
                    <img id="imagePreview" class="img-thumbnail mt-2 preview-image" alt="Preview Gambar">
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
// Tutup koneksi database di akhir script
if (isset($stmt) && $stmt) {
    $stmt->close();
}
if (isset($conn) && $conn) {
    $conn->close();
}
?>
