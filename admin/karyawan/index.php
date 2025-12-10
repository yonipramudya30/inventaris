<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Query untuk mengambil data karyawan
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(username LIKE ? OR nama LIKE ? OR role LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= 'sss';
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung total data
$count_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'karyawan' $where_clause";
$count_stmt = $conn->prepare($count_sql);

if ($count_stmt === false) {
    die("Error preparing count statement: " . $conn->error);
}

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

if (!$count_stmt->execute()) {
    die("Error executing count statement: " . $count_stmt->error);
}

$count_result = $count_stmt->get_result();
if ($count_result === false) {
    die("Error getting result: " . $count_stmt->error);
}

$total_data = $count_result->fetch_assoc();
$total_rows = $total_data['total'];
$total_pages = ceil($total_rows / $per_page);

// Ambil data karyawan
$sql = "SELECT id, username, nama FROM users 
        WHERE role = 'karyawan' $where_clause 
        ORDER BY id DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Buat salinan parameter untuk binding
$bind_params = $params;
$bind_types = $types;

// Tambahkan parameter untuk LIMIT dan OFFSET
$bind_types .= 'ii';
$bind_params[] = $per_page;
$bind_params[] = $offset;

// Gunakan referensi untuk bind_param
$bind_args = array($bind_types);
foreach ($bind_params as $key => $value) {
    $bind_args[] = &$bind_params[$key];
}

// Panggil bind_param dengan parameter yang benar
if (!empty($bind_params)) {
    call_user_func_array(array($stmt, 'bind_param'), $bind_args);
}

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result === false) {
    die("Error getting result: " . $stmt->error);
}
$karyawan = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Karyawan - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .table th { white-space: nowrap; }
        .action-buttons .btn { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Karyawan</h2>
            <a href="tambah.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Karyawan
            </a>
        </div>

        <!-- Pencarian dan Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari karyawan...">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="?" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Karyawan -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Tanggal Daftar</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($karyawan) > 0): ?>
                                <?php $no = $offset + 1; ?>
                                <?php foreach ($karyawan as $k): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($k['username']) ?></td>
                                        <td><?= htmlspecialchars($k['nama']) ?></td>
                                        <td>N/A</td>
                                        <td class="text-end action-buttons">
                                            <a href="edit.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?= $k['id'] ?>, '<?= addslashes($k['nama']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">Tidak ada data karyawan</div>
                                        <?php if (!empty($search)): ?>
                                            <a href="?" class="btn btn-sm btn-outline-primary mt-2">Tampilkan Semua</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        &laquo; Sebelumnya
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        Selanjutnya &raquo;
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus karyawan <strong id="deleteName"></strong>?</p>
                    <p class="text-danger">Data yang dihapus tidak dapat dikembalikan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, name) {
            document.getElementById('deleteName').textContent = name;
            document.getElementById('deleteForm').action = `hapus.php?id=${id}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>
