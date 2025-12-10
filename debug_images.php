<?php
require __DIR__.'/config/db.php';

// Check if uploads directory exists and is writable
$uploadDir = __DIR__ . '/uploads/barang/';
$dirWritable = is_writable($uploadDir);
$dirExists = is_dir($uploadDir);

// Get all items with images
$result = $conn->query("SELECT id_barang, kode_barang, nama_barang, gambar FROM inventaris WHERE gambar IS NOT NULL");
$items = [];
if ($result) {
    $items = $result->fetch_all(MYSQLI_ASSOC);
}

// Check if sample file exists
$sampleFile = null;
if (!empty($items) && isset($items[0]['gambar'])) {
    $sampleFile = __DIR__ . '/uploads/' . $items[0]['gambar'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Images</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #dff0d8; color: #3c763d; }
        .error { background-color: #f2dede; color: #a94442; }
        .info { background-color: #d9edf7; color: #31708f; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        img { max-width: 100px; max-height: 100px; }
    </style>
</head>
<body>
    <h1>Image Upload Debug Information</h1>
    
    <div class="status <?= $dirExists ? 'success' : 'error' ?>">
        <strong>Uploads Directory:</strong> <?= htmlspecialchars($uploadDir) ?>
        <br>
        <strong>Status:</strong> <?= $dirExists ? 'Exists' : 'Does not exist' ?>
        <?php if ($dirExists): ?>
            <br>
            <strong>Writable:</strong> <?= $dirWritable ? 'Yes' : 'No' ?>
        <?php endif; ?>
    </div>

    <div class="status info">
        <h3>Items with images in database (<?= count($items) ?>)</h3>
        <?php if (!empty($items)): ?>
            <p>First item image path: <?= htmlspecialchars($items[0]['gambar']) ?></p>
            <?php if ($sampleFile): ?>
                <p>Full server path: <?= htmlspecialchars($sampleFile) ?></p>
                <p>File exists: <?= file_exists($sampleFile) ? 'Yes' : 'No' ?></p>
                <p>File size: <?= file_exists($sampleFile) ? filesize($sampleFile) . ' bytes' : 'N/A' ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p>No items with images found in the database.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($items)): ?>
        <h2>Items with Images</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Image Path</th>
                <th>Image Preview</th>
                <th>File Exists</th>
            </tr>
            <?php foreach ($items as $item): ?>
                <?php 
                    $fullPath = __DIR__ . '/uploads/' . $item['gambar'];
                    $fileExists = file_exists($fullPath);
                    $webPath = '/INVENKAS/uploads/' . $item['gambar'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['id_barang']) ?></td>
                    <td><?= htmlspecialchars($item['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($item['gambar']) ?></td>
                    <td>
                        <?php if ($fileExists): ?>
                            <img src="<?= htmlspecialchars($webPath) ?>" alt="<?= htmlspecialchars($item['nama_barang']) ?>">
                        <?php else: ?>
                            <em>File not found</em>
                        <?php endif; ?>
                    </td>
                    <td><?= $fileExists ? 'Yes' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
