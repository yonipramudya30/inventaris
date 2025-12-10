<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>INVENKAS</title>
<style>
 body{margin:0;font-family:Segoe UI,Arial,sans-serif;background:#f6f7fb;color:#222}
 header{background:#111827;color:#fff;padding:10px 16px;display:flex;align-items:center;gap:12px}
 .brand{font-weight:700;letter-spacing:.3px}
 .container{max-width:1200px;margin:0 auto;padding:16px}
 .btn{display:inline-block;background:#1f6feb;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none;border:none;cursor:pointer}
 .btn.secondary{background:#6b7280}
 .btn.danger{background:#ef4444}
 table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden}
 th,td{padding:8px 10px;border:1px solid #e5e7eb}
 th{background:#f3f4f6;text-align:left}
 .form{display:grid;gap:10px;max-width:520px}
 input,select{padding:8px;border:1px solid #d1d5db;border-radius:6px}
 .actions{display:flex;gap:8px;margin-bottom:12px}

 /* Compact/responsive helpers */
 .form.compact{gap:8px;max-width:none}
 .form.compact input,.form.compact select{padding:6px;font-size:14px}
 .filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px}
 .inline-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;align-items:end}
 .table-responsive{width:100%;overflow:auto}

 /* Modal components */
 .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center;z-index:60;padding:12px}
 .modal-overlay.open{display:flex}
 .modal{background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.2);max-width:760px;width:100%;padding:16px;border:1px solid #e5e7eb;max-height:90vh;overflow:auto}
 .modal header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;background:transparent;color:#111;padding:0}
 .modal .modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
 body.modal-open{overflow:hidden}

 /* Dashboard cards */
 .card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
 .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
 .card .title{color:#6b7280;font-size:12px}
 .card .value{font-size:28px;font-weight:700;margin-top:4px}
 .card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
 .icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#111827}
 .accent-blue{background:#dbeafe}
 .accent-amber{background:#fef3c7}
 .accent-emerald{background:#d1fae5}
 .accent-purple{background:#ede9fe}

 /* Layout with sidebar */
 .layout{display:flex;min-height:calc(100vh - 56px)}
 .sidebar{width:240px;background:#111827;color:#e5e7eb;flex-shrink:0;padding:16px 12px;position:sticky;top:0;height:calc(100vh - 56px);overflow:auto}
 .sidebar nav a{display:block;color:#e5e7eb;text-decoration:none;padding:10px 12px;border-radius:6px;margin-bottom:6px}
 .sidebar nav a:hover{background:#1f2937}
 .sidebar .section{font-size:12px;text-transform:uppercase;color:#9ca3af;margin:12px 6px 6px}
 .content{flex:1;padding:16px}

  /* Collapsed sidebar (desktop) */
  body.sidebar-collapsed .sidebar{width:0;padding:0;overflow:hidden}
  body.sidebar-collapsed .content{padding:16px}

 /* Mobile behavior */
 .sidebar-toggle{background:transparent;border:0;color:#fff;font-size:20px;cursor:pointer;padding:6px;border-radius:6px}
 .sidebar-toggle:focus{outline:2px solid #60a5fa}
 @media (max-width: 900px){
   .layout{min-height:auto}
   .sidebar{position:fixed;left:0;top:56px;transform:translateX(-100%);transition:transform .2s ease;height:calc(100vh - 56px);z-index:50}
   body.sidebar-open .sidebar{transform:translateX(0)}
   .content{padding:16px}
 }
</style>
</head>
<body>
<header>
  <button class="sidebar-toggle" aria-label="Toggle menu" onclick="toggleSidebar()">☰</button>
  <div class="brand">INVENKAS</div>
</header>
<div class="layout">
  <aside class="sidebar">
    <div class="section">Menu</div>
    <nav>
      <a href="/INVENKAS/dashboard/">Dashboard</a>
      
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="section">Manajemen</div>
        <a href="/INVENKAS/admin/barang.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/barang') !== false) ? 'active' : '' ?>">Data Barang</a>
        <a href="/INVENKAS/admin/karyawan/" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/karyawan') !== false) ? 'active' : '' ?>">Data Karyawan</a>
      <?php endif; ?>
      
      <div class="section">Transaksi</div>
      <a href="/INVENKAS/pinjam/">Peminjaman</a>
      
      <div class="section">Akun</div>
      <a href="/INVENKAS/logout.php">Keluar</a>
    </nav>
  </aside>
  <main class="content container">
    <script>
      function toggleSidebar(){
        if (window.matchMedia('(max-width: 900px)').matches) {
          document.body.classList.toggle('sidebar-open');
        } else {
          document.body.classList.toggle('sidebar-collapsed');
        }
      }
      document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.sidebar nav a').forEach(function(a){
          a.addEventListener('click', function(){
            document.body.classList.remove('sidebar-open');
          });
        });
        document.querySelector('main.content')?.addEventListener('click', function(){
          document.body.classList.remove('sidebar-open');
        });
      });
    </script>
