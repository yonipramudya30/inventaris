<?php
require_once __DIR__ . '/includes/config.php';

// Data akun yang akan dibuat
$accounts = [
    [
        'username' => 'admin',
        'nama' => 'Administrator',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'username' => 'karyawan',
        'nama' => 'Karyawan',
        'password' => 'karyawan123',
        'role' => 'karyawan'
    ]
];

// Fungsi untuk membersihkan output
function clean_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

echo "<h2>Pembuatan Akun</h2>";

foreach ($accounts as $account) {
    $username = $account['username'];
    $nama = $account['nama'];
    $password = $account['password'];
    $role = $account['role'];
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<p>Akun dengan username <strong>" . clean_output($username) . "</strong> sudah ada. Dilewati.</p>";
        continue;
    }
    
    // Tambahkan akun baru
    $stmt = $conn->prepare("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $username, $hashed_password, $nama, $role);
    
    if ($stmt->execute()) {
        echo "<div style='background: #e8f5e9; padding: 15px; margin: 10px 0; border-left: 5px solid #4caf50;'>";
        echo "<h3>Akun " . clean_output($role) . " berhasil dibuat!</h3>";
        echo "<p><strong>Username:</strong> " . clean_output($username) . "<br>";
        echo "<strong>Password:</strong> " . clean_output($password) . "<br>";
        echo "<strong>Role:</strong> " . clean_output($role) . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>Gagal membuat akun " . clean_output($username) . ": " . $conn->error . "</p>";
    }
    
    $stmt->close();
}

echo "<hr>";
echo "<p><a href='/INVENKAS/login.php' style='display: inline-block; background: #4caf50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Kembali ke Halaman Login</a></p>";

$conn->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah username sudah ada
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Username sudah digunakan. Silakan pilih username lain.");
}

// Tambahkan akun baru
$stmt = $conn->prepare("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $username, $hashed_password, $nama, $role);

if ($stmt->execute()) {
    echo "Akun karyawan berhasil dibuat!<br>";
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Password: " . htmlspecialchars($password) . "<br>";
    echo "<a href='/INVENKAS/login.php'>Kembali ke Halaman Login</a>";
} else {
    echo "Gagal membuat akun: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
