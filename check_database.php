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

// Fungsi untuk mengecek apakah tabel ada
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}

// Periksa tabel users
if (!tableExists($conn, 'users')) {
    die("Tabel 'users' tidak ditemukan!");
} else {
    echo "<h3>Tabel users ada</h3>";
    $result = $conn->query("DESCRIBE users");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Periksa tabel inventaris
if (!tableExists($conn, 'inventaris')) {
    echo "<p style='color:red;'>Tabel 'inventaris' tidak ditemukan!</p>";
} else {
    echo "<h3>Tabel inventaris ada</h3>";
    $result = $conn->query("DESCRIBE inventaris");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Periksa tabel peminjaman
if (!tableExists($conn, 'peminjaman')) {
    echo "<p style='color:red;'>Tabel 'peminjaman' tidak ditemukan!</p>";
} else {
    echo "<h3>Tabel peminjaman ada</h3>";
    $result = $conn->query("DESCRIBE peminjaman");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Tampilkan data contoh
if (tableExists($conn, 'inventaris')) {
    echo "<h3>Data Inventaris</h3>";
    $result = $conn->query("SELECT * FROM inventaris LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr>";
        while($field = $result->fetch_field()) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Tidak ada data inventaris.";
    }
}

$conn->close();
?>
