<?php
// Cek session untuk kedua role
$adminSession = $_COOKIE['invenkas_admin'] ?? null;
$karyawanSession = $_COOKIE['invenkas_karyawan'] ?? null;
$isLoggedIn = false;

// Cek session admin
if ($adminSession) {
    session_name('invenkas_admin');
    session_start();
    if (isset($_SESSION['user_id'])) {
        $isLoggedIn = true;
        $currentRole = 'admin';
    }
    session_write_close();
}

// Cek session karyawan jika belum login sebagai admin
if (!$isLoggedIn && $karyawanSession) {
    session_name('invenkas_karyawan');
    session_start();
    if (isset($_SESSION['user_id'])) {
        $isLoggedIn = true;
        $currentRole = 'karyawan';
    }
    session_write_close();
}

// Redirect ke halaman login jika belum login
if (!$isLoggedIn) {
    header('Location: /INVENKAS/login.php');
    exit();
}

// Set session yang sesuai untuk request saat ini
if (isset($currentRole)) {
    session_name('invenkas_' . $currentRole);
    session_start();
}
?>
