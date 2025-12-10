<?php
require_once __DIR__ . '/../includes/config.php';

// Create the table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `inventaris` (
    `id_barang` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_barang` VARCHAR(50) NOT NULL,
    `nama_barang` VARCHAR(100) NOT NULL,
    `kategori` VARCHAR(50) NOT NULL,
    `jumlah` INT(11) NOT NULL DEFAULT 0,
    `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Perlu Perbaikan') NOT NULL DEFAULT 'Baik',
    `lokasi` VARCHAR(100) DEFAULT NULL,
    `deskripsi` TEXT DEFAULT NULL,
    `gambar` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_barang`),
    UNIQUE KEY `kode_barang` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "<div class='alert alert-success'>Tabel 'inventaris' berhasil dibuat/diperbarui.</div>";
    
    // Check if the table was created successfully
    $result = $conn->query("SHOW TABLES LIKE 'inventaris'");
    if ($result->num_rows > 0) {
        echo "<div class='alert alert-info'>Tabel 'inventaris' sudah ada di database.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal membuat tabel 'inventaris': " . $conn->error . "</div>";
    }
    
    // Show table structure
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
    } else {
        echo "<div class='alert alert-warning'>Tidak dapat menampilkan struktur tabel: " . $conn->error . "</div>";
    }
    
} else {
    echo "<div class='alert alert-danger'>Gagal membuat tabel: " . $conn->error . "</div>";
}

// Add any missing columns
$columns_to_add = [
    'id_barang' => "ALTER TABLE `inventaris` ADD COLUMN `id_barang` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id_barang`)",
    'kode_barang' => "ALTER TABLE `inventaris` ADD COLUMN `kode_barang` VARCHAR(50) NOT NULL AFTER `id_barang`",
    'nama_barang' => "ALTER TABLE `inventaris` ADD COLUMN `nama_barang` VARCHAR(100) NOT NULL AFTER `kode_barang`",
    'kategori' => "ALTER TABLE `inventaris` ADD COLUMN `kategori` VARCHAR(50) NOT NULL AFTER `nama_barang`",
    'jumlah' => "ALTER TABLE `inventaris` ADD COLUMN `jumlah` INT(11) NOT NULL DEFAULT '0' AFTER `kategori`",
    'kondisi' => "ALTER TABLE `inventaris` ADD COLUMN `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Perlu Perbaikan') NOT NULL DEFAULT 'Baik' AFTER `jumlah`",
    'lokasi' => "ALTER TABLE `inventaris` ADD COLUMN `lokasi` VARCHAR(100) DEFAULT NULL AFTER `kondisi`",
    'deskripsi' => "ALTER TABLE `inventaris` ADD COLUMN `deskripsi` TEXT DEFAULT NULL AFTER `lokasi`",
    'gambar' => "ALTER TABLE `inventaris` ADD COLUMN `gambar` VARCHAR(255) DEFAULT NULL AFTER `deskripsi`",
    'created_at' => "ALTER TABLE `inventaris` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `gambar`",
    'updated_at' => "ALTER TABLE `inventaris` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
];

// Check and add missing columns
$result = $conn->query("SHOW COLUMNS FROM `inventaris`");
$existing_columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "<h3>Menambahkan Kolom yang Hilang:</h3>";
    foreach ($columns_to_add as $column => $sql) {
        if (!in_array($column, $existing_columns)) {
            if ($conn->query($sql)) {
                echo "<div class='alert alert-success'>Berhasil menambahkan kolom: $column</div>";
            } else {
                echo "<div class='alert alert-danger'>Gagal menambahkan kolom $column: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-info'>Kolom '$column' sudah ada.</div>";
        }
    }
}

// Add unique constraint to kode_barang if it doesn't exist
$result = $conn->query("SHOW INDEX FROM `inventaris` WHERE Key_name = 'kode_barang'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE `inventaris` ADD UNIQUE INDEX `kode_barang` (`kode_barang`)";
    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Berhasil menambahkan UNIQUE constraint pada kolom kode_barang</div>";
    } else {
        echo "<div class='alert alert-warning'>Gagal menambahkan UNIQUE constraint: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='alert alert-info'>UNIQUE constraint pada kode_barang sudah ada.</div>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Perbaikan Database Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Perbaikan Database Inventaris</h1>
        <div class="mt-4">
            <a href="tambah_barang.php" class="btn btn-primary">Kembali ke Halaman Tambah Barang</a>
            <a href="barang.php" class="btn btn-secondary">Lihat Daftar Barang</a>
        </div>
    </div>
</body>
</html>
