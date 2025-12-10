<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

// Inisialisasi variabel form
$username = '';
$nama = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validasi
    if (empty($username)) {
        $errors['username'] = 'Username harus diisi';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter';
    } else {
        // Cek apakah username sudah digunakan
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['username'] = 'Username sudah digunakan';
        }
    }
    
    if (empty($nama)) {
        $errors['nama'] = 'Nama lengkap harus diisi';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter';
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Konfirmasi password tidak cocok';
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'karyawan';
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $hashed_password, $nama, $role);
        
        if ($stmt->execute()) {
            $success = 'Karyawan berhasil ditambahkan';
            // Reset form
            $username = $nama = '';
        } else {
            $errors['database'] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tambah Karyawan Baru</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['database'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($errors['database']) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" class="form-container mx-auto">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['username']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>" 
                               id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                        <?php if (isset($errors['nama'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['nama']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" name="password" required>
                        <div class="form-text">Minimal 6 karakter</div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirm" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                               id="password_confirm" name="password_confirm" required>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password_confirm']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
