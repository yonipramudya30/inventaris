<?php
require_once __DIR__ . '/../includes/config.php';

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS=0");

// Drop the table if it exists
$conn->query("DROP TABLE IF EXISTS `inventaris`");

// Create the table with the correct structure
$sql = "CREATE TABLE `inventaris` (
    `id_barang` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_barang` VARCHAR(50) NOT NULL,
    `nama_barang` VARCHAR(100) NOT NULL,
    `kategori` VARCHAR(50) NOT NULL,
    `jumlah` INT(11) NOT NULL DEFAULT '0',
    `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Perlu Perbaikan') NOT NULL DEFAULT 'Baik',
    `lokasi` VARCHAR(100) DEFAULT NULL,
    `deskripsi` TEXT DEFAULT NULL,
    `gambar` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_barang`),
    UNIQUE KEY `kode_barang` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "<div class='alert alert-success'>Tabel 'inventaris' berhasil dibuat ulang dengan struktur yang benar.</div>";
    
    // Show the table structure
    echo "<h3>Struktur Tabel 'inventaris':</h3>";
    $result = $conn->query("DESCRIBE inventaris");
    if ($result) {
        echo "<table class='table table-bordered'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . (is_null($row['Default']) ? 'NULL' : htmlspecialchars($row['Default'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='alert alert-danger'>Gagal membuat tabel: " . $conn->error . "</div>";
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS=1");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup Database Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Setup Database Inventaris</h1>
        <div class="mt-4">
            <a href="tambah_barang.php" class="btn btn-primary">Kembali ke Halaman Tambah Barang</a>
            <a href="barang.php" class="btn btn-secondary">Lihat Daftar Barang</a>
        </div>
    </div>
</body>
</html>
