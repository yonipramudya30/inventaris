<?php
require_once __DIR__ . '/config/db.php';

// Check if peminjaman table exists
$result = $conn->query("SHOW TABLES LIKE 'peminjaman'");
if ($result->num_rows === 0) {
    die("Table 'peminjaman' does not exist in the database.\n");
}

// Get table structure
$result = $conn->query("DESCRIBE peminjaman");
if ($result === false) {
    die("Error describing table: " . $conn->error . "\n");
}

echo "Table structure for 'peminjaman':\n";
echo str_pad("Field", 20) . str_pad("Type", 20) . str_pad("Null", 10) . str_pad("Key", 10) . "Default\n";
echo str_repeat("-", 60) . "\n";

while ($row = $result->fetch_assoc()) {
    echo str_pad($row['Field'], 20) . 
         str_pad($row['Type'], 20) . 
         str_pad($row['Null'], 10) . 
         str_pad($row['Key'], 10) . 
         $row['Default'] . "\n";
}

// Check if photo column exists
$result = $conn->query("SHOW COLUMNS FROM peminjaman LIKE 'foto_barang'");
if ($result->num_rows === 0) {
    echo "\nNote: 'foto_barang' column does not exist in the 'peminjaman' table.\n";
} else {
    echo "\n'foto_barang' column exists in the 'peminjaman' table.\n";
}

$conn->close();
?>
