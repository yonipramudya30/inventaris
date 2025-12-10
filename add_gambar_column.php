<?php
require __DIR__.'/config/db.php';

// Check if the column already exists
$checkColumn = $conn->query("SHOW COLUMNS FROM inventaris LIKE 'gambar'");

if ($checkColumn->num_rows === 0) {
    // Add the gambar column
    $sql = "ALTER TABLE inventaris ADD COLUMN gambar VARCHAR(255) NULL AFTER lokasi";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: green;'>Column 'gambar' added successfully to 'inventaris' table.</div>";
    } else {
        echo "<div style='color: red;'>Error adding column: " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: orange;'>Column 'gambar' already exists in 'inventaris' table.</div>";
}

// Show current table structure
echo "<h3>Current table structure:</h3>";
$result = $conn->query("SHOW COLUMNS FROM inventaris");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>

<p><a href="/INVENKAS/barang/">Kembali ke Daftar Barang</a></p>
