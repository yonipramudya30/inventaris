<?php
require_once __DIR__ . '/config.php';

// Fungsi untuk memulai session dengan aman
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Cek jika ada session name yang disimpan di cookie
        $sessionName = $_COOKIE['invenkas_session_name'] ?? 'invenkas_default';
        
        // Mulai session dengan pengaturan yang aman
        session_name($sessionName);
        session_start([
            'cookie_lifetime' => 86400, // 1 hari
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
            'use_cookies' => true,
            'use_only_cookies' => true,
            'cookie_secure' => isset($_SERVER['HTTPS'])
        ]);
    }
}

// Fungsi untuk login
function login($username, $password) {
    global $conn;
    
    error_log("Attempting login for username: " . $username);
    
    // Bersihkan input username
    $username = trim($username);
    
    // Query untuk mendapatkan data user
    $sql = "SELECT id, username, password, role, nama FROM users WHERE username = ? LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Debug: Tampilkan data user yang didapat dari database
        error_log("User data from DB: " . print_r($user, true));
        
        // Debug: Tampilkan password yang dimasukkan dan hash dari database
        error_log("Input password: $password");
        error_log("Stored hash: " . $user['password']);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            error_log("Password verified successfully");
            
            // Hapus semua session yang ada
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
            }
            
            // Mulai session baru
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['last_activity'] = time();
            
            error_log("Session data set: " . print_r($_SESSION, true));
            error_log("Login successful for user: " . $user['username'] . " with role: " . $user['role']);
            
            return true;
        } else {
            error_log("Password verification failed");
        }
    } else {
        error_log("User not found or multiple users found");
    }
    
    error_log("Login failed for username: " . $username);
    return false;
}

// Fungsi untuk mengecek apakah user sudah login
function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['user_id']);
}

// Fungsi untuk mengecek role user
function is_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk redirect jika belum login
function require_login() {
    if (!is_logged_in()) {
        header("Location: /INVENKAS/login.php");
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: /INVENKAS/dashboard/");
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    // Hapus semua data session
    $_SESSION = [];
    
    // Hapus cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Hapus cookie session name
    setcookie('invenkas_session_name', '', time() - 3600, '/');
    
    // Hancurkan session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
    
    // Redirect ke halaman login
    header("Location: /INVENKAS/login.php");
    exit();
}
?>
