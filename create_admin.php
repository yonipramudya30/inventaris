<?php
// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'inventaris_db');

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Hapus tabel users jika ada
$conn->query("DROP TABLE IF EXISTS users");

// Buat tabel users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL DEFAULT 'User',
    role ENUM('admin','karyawan') NOT NULL DEFAULT 'karyawan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Tabel users berhasil dibuat<br>";
} else {
    die("Error membuat tabel: " . $conn->error);
}

// Hash password
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Tambahkan user admin
$username = 'admin';
$nama = 'Administrator';
$sql = "INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, 'admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $hashed_password, $nama);

if ($stmt->execute()) {
    echo "User admin berhasil dibuat<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "Password hash: " . $hashed_password . "<br>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();

// Tampilkan isi tabel users
echo "<br><br>Daftar user saat ini:<br>";
$conn = new mysqli('localhost', 'root', '', 'inventaris_db');
$result = $conn->query("SELECT * FROM users");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Username: " . $row["username"]. " - Nama: " . $row["nama"]. " - Role: " . $row["role"]. "<br>";
    }
} else {
    echo "Tidak ada user yang ditemukan";
}
$conn->close();
?>
