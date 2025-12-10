<?php
require_once __DIR__ . '/../includes/config.php';

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Dapatkan struktur tabel
$result = $conn->query("SHOW CREATE TABLE inventaris");
if ($result === false) {
    die("Error: " . $conn->error);
}

$row = $result->fetch_assoc();
echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";

// Tampilkan data yang ada (maksimal 5 baris)
$data = $conn->query("SELECT * FROM inventaris LIMIT 5");
echo "<h2>Data Contoh (maks 5 baris):</h2>";
echo "<table border='1' cellpadding='5'>";
// Header
$fields = $data->fetch_fields();
echo "<tr>";
foreach ($fields as $field) {
    echo "<th>" . htmlspecialchars($field->name) . "</th>";
}
echo "</tr>";

// Data
$data->data_seek(0);
while ($row = $data->fetch_assoc()) {
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>" . htmlspecialchars($value) . "&nbsp;</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
