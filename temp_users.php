<?php
require __DIR__.'/config/db.php';

// Query untuk mendapatkan daftar pengguna
$sql = "SELECT id, username, nama, role FROM users ORDER BY role, username";
$result = $conn->query($sql);

// Tampilkan hasil
if ($result->num_rows > 0) {
    echo "<h2>Daftar Pengguna</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Tidak ada data pengguna";
}

// Jangan lupa menutup koneksi
$conn->close();
?>

<!-- Tombol untuk menghapus file ini setelah digunakan -->
<div style="margin-top: 20px;">
    <form method="post" onsubmit="return confirm('Hapus file ini?');">
        <input type="hidden" name="action" value="delete">
        <button type="submit" style="padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Hapus File Ini</button>
    </form>
</div>

<?php
// Proses hapus file jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    unlink(__FILE__);
    header('Location: /INVENKAS/');
    exit();
}
?>
