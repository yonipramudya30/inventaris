<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';

// Redirect ke halaman login jika belum login
if (!is_logged_in()) {
    header('Location: /INVENKAS/login.php');
    exit();
}

// Redirect admin ke halaman admin
if (is_admin()) {
    header('Location: /INVENKAS/admin/');
    exit();
}

// Ambil data statistik
$stats = [
    'total_barang' => 0,
    'barang_dipinjam' => 0,
    'peminjaman_aktif' => 0
];

// Hitung total barang
$result = $conn->query("SELECT COUNT(*) as total FROM inventaris");
if ($result) {
    $stats['total_barang'] = $result->fetch_assoc()['total'];
}

// Hitung total barang yang sedang dipinjam
$result = $conn->query("SELECT SUM(jumlah) as total FROM peminjaman WHERE status = 'Dipinjam'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['barang_dipinjam'] = $row['total'] ?? 0;
}

// Hitung total peminjaman aktif
$result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Dipinjam'");
if ($result) {
    $stats['peminjaman_aktif'] = $result->fetch_assoc()['total'];
}

// Ambil daftar peminjaman aktif user
$peminjaman_aktif = [];
$nama_peminjam = $_SESSION['nama'] ?? '';

// Pastikan koneksi database berhasil
if ($conn) {
    $sql = "SELECT p.*, i.nama_barang, i.kode_barang 
            FROM peminjaman p 
            JOIN inventaris i ON p.id_barang = i.id_barang 
            WHERE p.nama_peminjam = ? AND p.status = 'Dipinjam'
            ORDER BY p.tanggal_pinjam DESC 
            LIMIT 5";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $nama_peminjam);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $peminjaman_aktif[] = $row;
            }
        }
        $stmt->close();
    } else {
        // Jika terjadi error pada prepare
        error_log("Error in prepare: " . $conn->error);
    }
} else {
    error_log("Database connection failed");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventaris</title>
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
        .welcome-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold">Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?>!</h1>
                    <p class="lead mb-0">Selamat datang di Sistem Manajemen Inventaris</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <i class="bi bi-person-circle" style="font-size: 5rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Barang Tersedia</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_barang']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Barang Dipinjam</h6>
                            <h3 class="mb-0"><?= number_format($stats['barang_dipinjam']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-box-arrow-up"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Peminjaman Aktif</h6>
                            <h3 class="mb-0"><?= number_format($stats['peminjaman_aktif']) ?></h3>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Peminjaman Aktif -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Peminjaman Aktif Anda</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($peminjaman_aktif) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Barang</th>
                                            <th>Kode</th>
                                            <th>Jumlah</th>
                                            <th>Tgl Pinjam</th>
                                            <th>Tgl Kembali</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($peminjaman_aktif as $p): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($p['nama_barang']) ?></td>
                                                <td><?= htmlspecialchars($p['kode_barang']) ?></td>
                                                <td><?= $p['jumlah'] ?? '0' ?></td>
                                                <td><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
                                                <td><?= date('d M Y', strtotime($p['tanggal_kembali'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-2 mb-0">Tidak ada peminjaman aktif</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($peminjaman_aktif) > 0): ?>
                        <div class="card-footer bg-white border-top text-end">
                            <a href="peminjaman/" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menu Cepat -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Menu Cepat</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="barang/" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-box-seam me-3" style="font-size: 1.25rem;"></i>
                                <div>
                                    <h6 class="mb-0">Daftar Barang</h6>
                                    <small class="text-muted">Lihat semua barang inventaris</small>
                                </div>
                            </a>
                            <a href="../barang/pinjam.php" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-cart-plus me-3" style="font-size: 1.25rem;"></i>
                                <div>
                                    <h6 class="mb-0">Pinjam Barang</h6>
                                    <small class="text-muted">Ajukan peminjaman barang</small>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-clock-history me-3" style="font-size: 1.25rem;"></i>
                                <div>
                                    <h6 class="mb-0">Riwayat Peminjaman</h6>
                                    <small class="text-muted">Lihat riwayat peminjaman Anda</small>
                                </div>
                            </a>
                            <?php if (is_admin()): ?>
                                <a href="../admin/" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="bi bi-speedometer2 me-3" style="font-size: 1.25rem;"></i>
                                    <div>
                                        <h6 class="mb-0">Admin Dashboard</h6>
                                        <small class="text-muted">Akses menu admin</small>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="card mt-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="list-group-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="bi bi-check2-circle text-success"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-0">Peminjaman Berhasil</h6>
                                                <small class="text-muted"><?= $i ?>h yang lalu</small>
                                            </div>
                                            <small class="text-muted">Anda meminjam Laptop Acer (2 unit)</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
