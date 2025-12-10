-- Migration 001: Initial upgrade for new features
-- Add image column to inventaris table
ALTER TABLE inventaris ADD COLUMN gambar VARCHAR(255) DEFAULT NULL AFTER kode_barang;

-- Update users table to include role and other user details
ALTER TABLE users 
ADD COLUMN role ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
ADD COLUMN nama_lengkap VARCHAR(100) NOT NULL AFTER username,
ADD COLUMN email VARCHAR(100) DEFAULT NULL,
ADD COLUMN no_hp VARCHAR(20) DEFAULT NULL,
ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Add due date and reminder status to peminjaman table
ALTER TABLE peminjaman 
ADD COLUMN tanggal_kembali DATE DEFAULT NULL AFTER tanggal_pinjam,
ADD COLUMN status_reminder ENUM('none', 'dipinjam', 'jatuh_tempo', 'terlambat') DEFAULT 'none' AFTER status,
ADD COLUMN tanggal_reminder DATETIME DEFAULT NULL;

-- Create a table for reminders
CREATE TABLE IF NOT EXISTS reminder_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_pinjam INT NOT NULL,
  jenis_reminder ENUM('dipinjam', 'jatuh_tempo', 'terlambat') NOT NULL,
  waktu_kirim DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status_kirim ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
  FOREIGN KEY (id_pinjam) REFERENCES peminjaman(id_pinjam) ON DELETE CASCADE
) ENGINE=InnoDB;
