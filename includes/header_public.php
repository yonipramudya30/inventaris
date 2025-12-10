<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>INVENKAS - Login</title>
<style>
 body{margin:0;font-family:Segoe UI,Arial,sans-serif;background:#f6f7fb;color:#222}
 header{background:#111827;color:#fff;padding:10px 16px;display:flex;align-items:center;gap:12px}
 .brand{font-weight:700;letter-spacing:.3px}
 .container{max-width:1200px;margin:0 auto;padding:16px}
 .btn{display:inline-block;background:#1f6feb;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none;border:none;cursor:pointer}
 .btn.secondary{background:#6b7280}
 .btn.danger{background:#ef4444}
 .form{display:grid;gap:10px}
 input{padding:8px;border:1px solid #d1d5db;border-radius:6px}
 .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
</style>
</head>
<body>
<header>
  <div class="brand">INVENKAS</div>
</header>
<main class="container">
