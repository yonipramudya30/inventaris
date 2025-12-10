<?php
require_once __DIR__ . '/../includes/config.php';

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'inventaris'");
if ($result->num_rows == 0) {
    die("Table 'inventaris' does not exist. Please create it first.");
}

// Get table structure
$result = $conn->query("DESCRIBE inventaris");
if ($result === false) {
    die("Error describing table: " . $conn->error);
}

echo "<h2>Table Structure for 'inventaris':</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if all required columns exist
$required_columns = [
    'kode_barang', 'nama_barang', 'kategori', 'jumlah', 
    'kondisi', 'lokasi', 'deskripsi', 'gambar'
];

$result = $conn->query("SHOW COLUMNS FROM inventaris");
$existing_columns = [];

while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

$missing_columns = array_diff($required_columns, $existing_columns);

if (!empty($missing_columns)) {
    echo "<h3 style='color: red;'>Missing columns: " . implode(', ', $missing_columns) . "</h3>";
    
    // Generate SQL to add missing columns
    echo "<h4>SQL to add missing columns:</h4>";
    echo "<pre>";
    foreach ($missing_columns as $column) {
        $sql = "ALTER TABLE inventaris ADD COLUMN ";
        switch($column) {
            case 'kode_barang':
                $sql .= "`kode_barang` VARCHAR(50) NOT NULL";
                break;
            case 'nama_barang':
                $sql .= "`nama_barang` VARCHAR(100) NOT NULL";
                break;
            case 'kategori':
                $sql .= "`kategori` VARCHAR(50) NOT NULL";
                break;
            case 'jumlah':
                $sql .= "`jumlah` INT(11) NOT NULL DEFAULT 0";
                break;
            case 'kondisi':
                $sql .= "`kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Perlu Perbaikan') NOT NULL DEFAULT 'Baik'";
                break;
            case 'lokasi':
                $sql .= "`lokasi` VARCHAR(100) DEFAULT NULL";
                break;
            case 'deskripsi':
                $sql .= "`deskripsi` TEXT DEFAULT NULL";
                break;
            case 'gambar':
                $sql .= "`gambar` VARCHAR(255) DEFAULT NULL";
                break;
        }
        echo htmlspecialchars($sql) . ";<br>";
    }
    echo "</pre>";
} else {
    echo "<h3 style='color: green;'>All required columns exist in the table.</h3>";
}

// Check for primary key
$result = $conn->query("SHOW INDEX FROM inventaris WHERE Key_name = 'PRIMARY'");
if ($result->num_rows == 0) {
    echo "<h3 style='color: orange;'>Warning: No primary key is set on the 'inventaris' table.</h3>";
    echo "<p>Consider adding a primary key with:</p>";
    echo "<pre>ALTER TABLE inventaris ADD PRIMARY KEY (id_barang);</pre>";
}

$conn->close();
?>
