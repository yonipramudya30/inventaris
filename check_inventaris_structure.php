<?php
require_once __DIR__ . '/config/db.php';

// Check if inventaris table exists
$result = $conn->query("SHOW TABLES LIKE 'inventaris'");
if ($result->num_rows === 0) {
    die("Table 'inventaris' does not exist in the database.\n");
}

// Get table structure
$result = $conn->query("DESCRIBE inventaris");
if ($result === false) {
    die("Error describing table: " . $conn->error . "\n");
}

echo "Table structure for 'inventaris':\n";
echo str_pad("Field", 20) . str_pad("Type", 20) . str_pad("Null", 10) . str_pad("Key", 10) . "Default\n";
echo str_repeat("-", 60) . "\n";

while ($row = $result->fetch_assoc()) {
    echo str_pad($row['Field'], 20) . 
         str_pad($row['Type'], 20) . 
         str_pad($row['Null'], 10) . 
         str_pad($row['Key'], 10) . 
         ($row['Default'] ?? 'NULL') . "\n";
}

// Check for required columns
$required_columns = ['kode_barang', 'nama_barang', 'kategori', 'jumlah', 'kondisi', 'lokasi', 'deskripsi', 'gambar'];
$missing_columns = [];

foreach ($required_columns as $col) {
    $result = $conn->query("SHOW COLUMNS FROM inventaris LIKE '$col'");
    if ($result->num_rows === 0) {
        $missing_columns[] = $col;
    }
}

if (!empty($missing_columns)) {
    echo "\nMissing required columns: " . implode(', ', $missing_columns) . "\n";
    
    // Generate SQL to add missing columns
    $alter_sql = [];
    
    if (in_array('gambar', $missing_columns)) {
        $alter_sql[] = "ADD COLUMN gambar VARCHAR(255) DEFAULT NULL";
    }
    
    // Add other missing columns if needed
    foreach ($missing_columns as $col) {
        if ($col !== 'gambar') {
            $alter_sql[] = "ADD COLUMN $col VARCHAR(255) NOT NULL";
        }
    }
    
    if (!empty($alter_sql)) {
        echo "\nYou can fix this by running the following SQL:\n";
        echo "ALTER TABLE inventaris " . implode(", ", $alter_sql) . ";\n";
    }
} else {
    echo "\nAll required columns exist in the 'inventaris' table.\n";
}

$conn->close();
?>
