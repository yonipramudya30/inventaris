<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Ambil data barang
$barang = [];
$error = null;

try {
    // Pastikan koneksi database berhasil
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    // Jalankan query
    $sql = "SELECT id_barang, kode_barang, nama_barang, kategori, jumlah, kondisi, lokasi, gambar FROM inventaris ORDER BY nama_barang ASC";
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Error dalam menjalankan query: " . $conn->error);
    }
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $barang[] = $row;
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Barang - INVENKAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Barang</h2>
            <a href="tambah_barang.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Barang
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Kondisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($barang) > 0): ?>
                                <?php foreach ($barang as $index => $item): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php if (!empty($item['gambar'])): ?>
                                                <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="Gambar Barang" class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">Tidak ada gambar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                        <td><?= $item['jumlah'] ?? 0 ?></td>
                                        <td><?= $item['kondisi'] ?? 'Baik' ?></td>
                                        <td>
                                            <a href="edit_barang.php?id=<?= $item['id_barang'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button onclick="hapusBarang(<?= $item['id_barang'] ?>)" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data barang</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function hapusBarang(id) {
        if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
            window.location.href = 'hapus_barang.php?id=' + id;
        }
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
