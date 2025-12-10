<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan session dimulai di awal file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: /INVENKAS/admin/');
    } else {
        header('Location: /INVENKAS/dashboard/');
    }
    exit();
}

// Variabel untuk menampung pesan error
$error = '';

// Proses login hanya jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        $login_result = login($username, $password);
        error_log("Login result for $username: " . ($login_result ? 'Success' : 'Failed'));
        if ($login_result) {
            // Redirect berdasarkan role setelah login berhasil
            if (is_admin()) {
                header('Location: /INVENKAS/admin/');
            } else {
                header('Location: /INVENKAS/dashboard/');
            }
            exit();
        } else {
            $error = 'Username atau password salah';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 10px 10px 0 0 !important;
        }
        .card-body {
            padding: 30px;
        }
        .btn-login {
            background-color: #0d6efd;
            color: white;
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">SISTEM INVENTARIS</h4>
                <small class="d-block mt-1">Silakan masuk untuk melanjutkan</small>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-login">Masuk</button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">© <?= date('Y') ?> Sistem Inventaris</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
