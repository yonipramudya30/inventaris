<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID barang tidak valid';
    header('Location: barang.php');
    exit();
}

$id_barang = (int)$_GET['id'];
$error = '';
$success = '';

// Ambil data barang
$stmt = $conn->prepare("SELECT * FROM inventaris WHERE id_barang = ?");
$stmt->bind_param("i", $id_barang);
$stmt->execute();
$result = $stmt->get_result();
$barang = $result->fetch_assoc();

if (!$barang) {
    $_SESSION['error'] = 'Barang tidak ditemukan';
    header('Location: barang.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang = $_POST['kode_barang'] ?? '';
    $nama_barang = $_POST['nama_barang'] ?? '';
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $kondisi = $_POST['kondisi'] ?? 'Baik';
    $keterangan = $_POST['keterangan'] ?? '';
    
    // Validasi input
    if (empty($kode_barang) || empty($nama_barang) || $jumlah < 0) {
        $error = 'Semua field harus diisi dan jumlah tidak boleh negatif';
    } else {
        // Cek apakah kode barang sudah digunakan oleh barang lain
        $stmt = $conn->prepare("SELECT id_barang FROM inventaris WHERE kode_barang = ? AND id_barang != ?");
        $stmt->bind_param("si", $kode_barang, $id_barang);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Kode barang sudah digunakan oleh barang lain';
        } else {
            // Update data barang
            $stmt = $conn->prepare("UPDATE inventaris SET kode_barang = ?, nama_barang = ?, jumlah = ?, kondisi = ?, keterangan = ? WHERE id_barang = ?");
            $stmt->bind_param("ssissi", $kode_barang, $nama_barang, $jumlah, $kondisi, $keterangan, $id_barang);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Data barang berhasil diperbarui';
                header('Location: barang.php');
                exit();
            } else {
                $error = 'Gagal memperbarui data barang: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - INVENKAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Barang</h2>
            <a href="barang.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode_barang" name="kode_barang" required 
                               value="<?= htmlspecialchars($_POST['kode_barang'] ?? $barang['kode_barang']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required
                               value="<?= htmlspecialchars($_POST['nama_barang'] ?? $barang['nama_barang']) ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="0" required
                                   value="<?= htmlspecialchars($_POST['jumlah'] ?? $barang['jumlah']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="kondisi" class="form-label">Kondisi</label>
                            <select class="form-select" id="kondisi" name="kondisi">
                                <option value="Baik" <?= (($_POST['kondisi'] ?? $barang['kondisi'] ?? '') === 'Baik') ? 'selected' : '' ?>>Baik</option>
                                <option value="Rusak Ringan" <?= (($_POST['kondisi'] ?? $barang['kondisi'] ?? '') === 'Rusak Ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                                <option value="Rusak Berat" <?= (($_POST['kondisi'] ?? $barang['kondisi'] ?? '') === 'Rusak Berat') ? 'selected' : '' ?>>Rusak Berat</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= htmlspecialchars($_POST['keterangan'] ?? $barang['keterangan'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="barang.php" class="btn btn-secondary me-md-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
