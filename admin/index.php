<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Ambil statistik
$stats = [
    'total_barang' => 0,
    'total_karyawan' => 0,
    'total_peminjaman' => 0,
    'peminjaman_aktif' => 0
];

// Hitung total barang
$result = $conn->query("SELECT COUNT(*) as total FROM inventaris");
if ($result) {
    $stats['total_barang'] = $result->fetch_assoc()['total'];
}

// Hitung total karyawan
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'karyawan'");
if ($result) {
    $stats['total_karyawan'] = $result->fetch_assoc()['total'];
}

// Hitung total peminjaman
$result = $conn->query("SELECT COUNT(*) as total FROM peminjaman");
if ($result) {
    $stats['total_peminjaman'] = $result->fetch_assoc()['total'];
}

// Hitung peminjaman aktif
$result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Dipinjam'");
if ($result) {
    $stats['peminjaman_aktif'] = $result->fetch_assoc()['total'];
}

// Ambil daftar peminjaman terbaru
$peminjaman = [];
$sql = "SELECT p.*, i.nama_barang, u.nama as nama_peminjam 
        FROM peminjaman p 
        JOIN inventaris i ON p.id_barang = i.id_barang 
        JOIN users u ON p.id_user = u.id 
        ORDER BY p.tanggal_pinjam DESC 
        LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $peminjaman[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2.5rem;
            color: #0d6efd;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard Admin</h2>
            <div>
                <span class="me-2">Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></span>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Barang</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_barang']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Karyawan</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_karyawan']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Peminjaman</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_peminjaman']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Peminjaman Aktif</h6>
                            <h3 class="mb-0"><?= number_format($stats['peminjaman_aktif']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card p-4">
                    <h5 class="mb-3">Aksi Cepat</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="barang/tambah.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Barang
                        </a>
                        <a href="karyawan/tambah.php" class="btn btn-success">
                            <i class="bi bi-person-plus me-2"></i>Tambah Karyawan
                        </a>
                        <a href="peminjaman/" class="btn btn-info text-white">
                            <i class="bi bi-clipboard-plus me-2"></i>Peminjaman Baru
                        </a>
                        <a href="laporan/" class="btn btn-warning">
                            <i class="bi bi-file-earmark-text me-2"></i>Buat Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peminjaman Terbaru -->
        <div class="row">
            <div class="col-md-8">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Peminjaman Terbaru</h5>
                        <a href="peminjaman/" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Peminjam</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($peminjaman) > 0): ?>
                                    <?php foreach ($peminjaman as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['nama_barang']) ?></td>
                                            <td><?= htmlspecialchars($p['nama_peminjam']) ?></td>
                                            <td><?= date('d M Y H:i', strtotime($p['tanggal_pinjam'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $p['status'] === 'Dipinjam' ? 'warning' : 'success' ?>">
                                                    <?= htmlspecialchars($p['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data peminjaman</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4">
                    <h5 class="mb-3">Aktivitas Terkini</h5>
                    <div class="recent-activity">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="d-flex mb-3 pb-2 border-bottom">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-check text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="mb-1"><strong>Admin</strong> menambahkan barang baru</p>
                                    <small class="text-muted">2 jam yang lalu</small>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
