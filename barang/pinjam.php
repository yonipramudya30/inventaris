<?php
require __DIR__.'/../includes/auth_check.php';
require __DIR__.'/../config/db.php';

// Ambil daftar barang yang tersedia (jumlah > 0)
$sql = "SELECT * FROM inventaris WHERE jumlah > 0 ORDER BY nama_barang";
$result = $conn->query($sql);
$barang = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $barang[] = $row;
    }
}

// Ambil daftar karyawan
$sql_karyawan = "SELECT id_karyawan, nama FROM karyawan ORDER BY nama";
$result_karyawan = $conn->query($sql_karyawan);
$karyawan = [];
if ($result_karyawan && $result_karyawan->num_rows > 0) {
    while ($row = $result_karyawan->fetch_assoc()) {
        $karyawan[] = $row;
    }
}

// Proses form peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang = $_POST['id_barang'] ?? '';
    $id_karyawan = $_POST['id_karyawan'] ?? '';
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $tanggal_pinjam = date('Y-m-d H:i:s');
    $tanggal_kembali = $_POST['tanggal_kembali'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status = 'Dipinjam';

    // Validasi
    if ($id_barang && $id_karyawan && $jumlah > 0 && $tanggal_kembali) {
        // Cek stok tersedia
        $sql_check = "SELECT jumlah FROM inventaris WHERE id_barang = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param('i', $id_barang);
        $stmt->execute();
        $result = $stmt->get_result();
        $stok = $result->fetch_assoc();

        if ($stok && $stok['jumlah'] >= $jumlah) {
            // Mulai transaksi
            $conn->begin_transaction();

            try {
                // Tambahkan data peminjaman
                $sql_pinjam = "INSERT INTO peminjaman (id_barang, id_karyawan, jumlah, tanggal_pinjam, tanggal_kembali, status, keterangan) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_pinjam);
                $stmt->bind_param('iiissss', $id_barang, $id_karyawan, $jumlah, $tanggal_pinjam, $tanggal_kembali, $status, $keterangan);
                $stmt->execute();

                // Kurangi stok barang
                $sql_kurangi = "UPDATE inventaris SET jumlah = jumlah - ? WHERE id_barang = ?";
                $stmt = $conn->prepare($sql_kurangi);
                $stmt->bind_param('ii', $jumlah, $id_barang);
                $stmt->execute();

                // Commit transaksi
                $conn->commit();
                $success = "Peminjaman berhasil dicatat";
            } catch (Exception $e) {
                // Rollback transaksi jika terjadi error
                $conn->rollback();
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        } else {
            $error = "Stok barang tidak mencukupi";
        }
    } else {
        $error = "Harap isi semua field yang diperlukan";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options {
            max-height: 300px;
            overflow-y: auto;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.5rem 1rem;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #f8f9fa;
            color: #000;
        }
        .select2-container--bootstrap-5 .select2-selection--single {
            height: 38px;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Form Peminjaman Barang</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="id_barang" class="form-label">Pilih Barang</label>
                                <select class="form-select select2" id="id_barang" name="id_barang" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php foreach ($barang as $item): ?>
                                        <option value="<?= $item['id_barang'] ?>" data-img-src="<?= !empty($item['gambar']) ? htmlspecialchars($item['gambar']) : '/INVENKAS/assets/img/no-image.png' ?>">
                                            <?= htmlspecialchars($item['nama_barang']) ?> 
                                            (Tersedia: <?= $item['jumlah'] ?>, Kode: <?= htmlspecialchars($item['kode_barang']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="id_karyawan" class="form-label">Peminjam (Karyawan)</label>
                                <select class="form-select" id="id_karyawan" name="id_karyawan" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php foreach ($karyawan as $k): ?>
                                        <option value="<?= $k['id_karyawan'] ?>">
                                            <?= htmlspecialchars($k['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah</label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                            </div>

                            <div class="mb-3">
                                <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                                <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" required>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Peminjaman</button>
                                <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Barang</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inisialisasi Select2 dengan template gambar
        $(document).ready(function() {
            function formatItem(option) {
                if (!option.id) return option.text;
                var $option = $(option.element);
                var imgSrc = $option.data('img-src');
                var $container = $(
                    '<div class="d-flex align-items-center">' +
                    '<img src="' + imgSrc + '" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">' +
                    '<div>' + option.text + '</div>' +
                    '</div>'
                );
                return $container;
            }

            $('#id_barang').select2({
                theme: 'bootstrap-5',
                templateResult: formatItem,
                templateSelection: formatItem,
                escapeMarkup: function(m) { return m; }
            });
        });
        // Set tanggal minimal untuk tanggal kembali adalah hari ini
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_kembali').min = today;
        });
    </script>
</body>
</html>
