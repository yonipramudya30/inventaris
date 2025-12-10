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

// Periksa apakah tabel users ada
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows === 0) {
    die("Tabel 'users' tidak ditemukan di database.");
}

// Tampilkan isi tabel users
echo "<h2>Daftar Pengguna</h2>";
$users = $conn->query("SELECT * FROM users");
if ($users->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th></tr>";
    while($row = $users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Tidak ada data pengguna.";
}

// Periksa session
session_start();
echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Periksa cookie
echo "<h2>Cookies</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

$conn->close();
?>
