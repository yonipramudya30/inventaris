<?php
// Koneksi ke database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'inventaris_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Periksa struktur tabel users
$result = $conn->query("SHOW COLUMNS FROM users");
if ($result === false) {
    die("Error: " . $conn->error);
}

echo "<h2>Struktur Tabel users</h2>";
echo "<table border='1' cellpadding='5'>";
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

$conn->close();
?>
