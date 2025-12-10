<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Memeriksa dan Memperbaiki Database</h2>";

// Koneksi ke MySQL tanpa memilih database terlebih dahulu
$conn = new mysqli('localhost', 'root', '');
if ($conn->connect_error) {
    die("Koneksi ke MySQL gagal: " . $conn->connect_error);
}

echo "<p>✅ Terhubung ke server MySQL</p>";

// Buat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS inventaris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Database 'inventaris_db' siap digunakan</p>";
} else {
    die("Gagal membuat database: " . $conn->error);
}

// Pilih database
$conn->select_db('inventaris_db');

// Buat tabel users jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Tabel 'users' siap digunakan</p>";
} else {
    die("Gagal membuat tabel users: " . $conn->error);
}

// Buat akun admin
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Hapus user admin yang sudah ada (jika ada)
$conn->query("DELETE FROM users WHERE username = 'admin'");

// Buat user admin baru
$sql = "INSERT INTO users (username, password, role, nama_lengkap, email) 
        VALUES (?, ?, 'admin', 'Administrator', 'admin@example.com')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $hashed_password);

if ($stmt->execute()) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 4px;'>";
    echo "<h3>✅ Akun Admin Berhasil Dibuat!</h3>";
    echo "<p>Berikut kredensial untuk login:</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='display: inline-block; background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-top: 10px;'>Klik di sini untuk login</a></p>";
    echo "</div>";
    
    // Tampilkan data yang baru dibuat
    $result = $conn->query("SELECT * FROM users WHERE username = 'admin'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<h3>Detail Akun Admin:</h3>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    }
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px;'>";
    echo "<h3>❌ Gagal Membuat Akun Admin</h3>";
    echo "<p>Error: " . $stmt->error . "</p>";
    echo "</div>";
}

$stmt->close();
$conn->close();
?>

<h3>Langkah Selanjutnya:</h3>
<ol>
    <li>Coba login dengan kredensial di atas</li>
    <li>Setelah berhasil login, hapus file <code>fix_database.php</code> untuk alasan keamanan</li>
    <li>Ganti password default segera setelah login pertama kali</li>
</ol>
