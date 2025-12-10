-- Buat database terlebih dahulu: CREATE DATABASE IF NOT EXISTS invenkas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Lalu gunakan: USE invenkas;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventaris (
  id_barang INT AUTO_INCREMENT PRIMARY KEY,
  kode_barang VARCHAR(50) NOT NULL UNIQUE,
  nama_barang VARCHAR(100) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  jumlah INT NOT NULL DEFAULT 0,
  kondisi VARCHAR(50) NOT NULL,
  lokasi VARCHAR(100) NOT NULL,
  tanggal_input DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS peminjaman (
  id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
  id_barang INT NOT NULL,
  nama_peminjam VARCHAR(100) NOT NULL,
  jumlah_pinjam INT NOT NULL,
  tanggal_pinjam DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('Dipinjam','Dikembalikan') NOT NULL DEFAULT 'Dipinjam',
  CONSTRAINT fk_pinjam_barang FOREIGN KEY (id_barang) REFERENCES inventaris(id_barang) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Log penghapusan barang (riwayat delete dgn alasan)
CREATE TABLE IF NOT EXISTS penghapusan (
  id_hapus INT AUTO_INCREMENT PRIMARY KEY,
  id_barang INT,
  kode_barang VARCHAR(50) NOT NULL,
  nama_barang VARCHAR(100) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  jumlah_terakhir INT NOT NULL,
  kondisi VARCHAR(50) NOT NULL,
  lokasi VARCHAR(100) NOT NULL,
  alasan VARCHAR(255) NOT NULL,
  tanggal_hapus DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Log penyesuaian stok (khusus pengurangan melalui edit)
CREATE TABLE IF NOT EXISTS penyesuaian_stok (
  id_adjust INT AUTO_INCREMENT PRIMARY KEY,
  id_barang INT NOT NULL,
  kode_barang VARCHAR(50) NOT NULL,
  nama_barang VARCHAR(100) NOT NULL,
  qty_awal INT NOT NULL,
  qty_baru INT NOT NULL,
  selisih INT NOT NULL, -- nilai negatif saat berkurang
  alasan VARCHAR(100) NOT NULL, -- Rusak | Tidak Layak Pakai | Hilang
  tanggal_adjust DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
