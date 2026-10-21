-- Database Aplikasi Peminjaman Alat (MySQL/MariaDB)
DROP DATABASE IF EXISTS peminjaman_alat;
CREATE DATABASE peminjaman_alat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE peminjaman_alat;

CREATE TABLE users (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','petugas','peminjam') NOT NULL DEFAULT 'peminjam',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id_kategori INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE alat (
  id_alat INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kategori INT UNSIGNED NOT NULL,
  nama_alat VARCHAR(100) NOT NULL,
  stok INT NOT NULL DEFAULT 0 CHECK (stok >= 0),
  kondisi ENUM('baik','rusak ringan','rusak berat') NOT NULL DEFAULT 'baik',
  deskripsi TEXT NULL,
  INDEX idx_alat_nama (nama_alat),
  CONSTRAINT fk_alat_kategori FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
) ENGINE=InnoDB;

CREATE TABLE peminjaman (
  id_peminjaman INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL,
  id_alat INT UNSIGNED NOT NULL,
  id_petugas INT UNSIGNED NULL,
  jumlah INT NOT NULL CHECK (jumlah > 0),
  tgl_pinjam DATE NOT NULL,
  tgl_kembali_rencana DATE NOT NULL,
  status ENUM('menunggu','disetujui','ditolak','dikembalikan') NOT NULL DEFAULT 'menunggu',
  INDEX idx_pinjam_status (status),
  CONSTRAINT fk_pinjam_user FOREIGN KEY (id_user) REFERENCES users(id_user),
  CONSTRAINT fk_pinjam_alat FOREIGN KEY (id_alat) REFERENCES alat(id_alat),
  CONSTRAINT fk_pinjam_petugas FOREIGN KEY (id_petugas) REFERENCES users(id_user)
) ENGINE=InnoDB;

CREATE TABLE pengembalian (
  id_pengembalian INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_peminjaman INT UNSIGNED NOT NULL UNIQUE,
  tgl_kembali DATE NOT NULL,
  keterlambatan_hari INT NOT NULL DEFAULT 0,
  denda DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_kembali_pinjam FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman)
) ENGINE=InnoDB;

CREATE TABLE log_aktivitas (
  id_log INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NULL,
  aktivitas VARCHAR(255) NOT NULL,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_log_waktu (waktu)
) ENGINE=InnoDB;

-- VIEW (operasi relasional: JOIN)
CREATE VIEW v_peminjaman AS
SELECT p.id_peminjaman, u.nama AS peminjam, a.nama_alat, k.nama_kategori, p.jumlah,
       p.tgl_pinjam, p.tgl_kembali_rencana, p.status, pt.nama AS petugas,
       r.tgl_kembali, r.keterlambatan_hari, r.denda
FROM peminjaman p
JOIN users u ON u.id_user = p.id_user
JOIN alat a ON a.id_alat = p.id_alat
JOIN kategori k ON k.id_kategori = a.id_kategori
LEFT JOIN users pt ON pt.id_user = p.id_petugas
LEFT JOIN pengembalian r ON r.id_peminjaman = p.id_peminjaman;

DELIMITER //

-- FUNCTION: denda Rp2.000 per hari keterlambatan
CREATE FUNCTION fn_hitung_denda(p_rencana DATE, p_kembali DATE) RETURNS DECIMAL(12,2)
DETERMINISTIC
BEGIN
  RETURN GREATEST(DATEDIFF(p_kembali, p_rencana), 0) * 2000;
END//

-- TRIGGER: hitung keterlambatan & denda otomatis
CREATE TRIGGER trg_pengembalian_bi BEFORE INSERT ON pengembalian
FOR EACH ROW
BEGIN
  DECLARE v_rencana DATE;
  SELECT tgl_kembali_rencana INTO v_rencana FROM peminjaman WHERE id_peminjaman = NEW.id_peminjaman;
  SET NEW.keterlambatan_hari = GREATEST(DATEDIFF(NEW.tgl_kembali, v_rencana), 0);
  SET NEW.denda = fn_hitung_denda(v_rencana, NEW.tgl_kembali);
END//

-- TRIGGER: catat log saat alat baru ditambahkan
CREATE TRIGGER trg_alat_ai AFTER INSERT ON alat
FOR EACH ROW
BEGIN
  INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (NULL, CONCAT('Alat baru ditambahkan: ', NEW.nama_alat));
END//

-- STORED PROCEDURE: ajukan peminjaman (COMMIT / ROLLBACK)
CREATE PROCEDURE sp_ajukan_peminjaman(IN p_user INT, IN p_alat INT, IN p_jumlah INT, IN p_rencana DATE)
BEGIN
  DECLARE v_stok INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
  START TRANSACTION;
  IF p_jumlah <= 0 OR p_rencana < CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Jumlah atau tanggal kembali tidak valid';
  END IF;
  SELECT stok INTO v_stok FROM alat WHERE id_alat = p_alat FOR UPDATE;
  IF v_stok IS NULL OR v_stok < p_jumlah THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok alat tidak mencukupi';
  END IF;
  INSERT INTO peminjaman (id_user, id_alat, jumlah, tgl_pinjam, tgl_kembali_rencana)
  VALUES (p_user, p_alat, p_jumlah, CURDATE(), p_rencana);
  COMMIT;
END//

-- STORED PROCEDURE: setujui peminjaman, kurangi stok
CREATE PROCEDURE sp_setujui_peminjaman(IN p_id INT, IN p_petugas INT)
BEGIN
  DECLARE v_alat INT; DECLARE v_jumlah INT; DECLARE v_status VARCHAR(20); DECLARE v_stok INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
  START TRANSACTION;
  SELECT id_alat, jumlah, status INTO v_alat, v_jumlah, v_status FROM peminjaman WHERE id_peminjaman = p_id FOR UPDATE;
  IF v_status IS NULL OR v_status <> 'menunggu' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Peminjaman tidak ditemukan atau sudah diproses';
  END IF;
  SELECT stok INTO v_stok FROM alat WHERE id_alat = v_alat FOR UPDATE;
  IF v_stok < v_jumlah THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok alat tidak mencukupi';
  END IF;
  UPDATE alat SET stok = stok - v_jumlah WHERE id_alat = v_alat;
  UPDATE peminjaman SET status = 'disetujui', id_petugas = p_petugas WHERE id_peminjaman = p_id;
  COMMIT;
END//

-- STORED PROCEDURE: kembalikan alat (denda dihitung trigger)
CREATE PROCEDURE sp_kembalikan_alat(IN p_id INT, IN p_user INT)
BEGIN
  DECLARE v_alat INT; DECLARE v_jumlah INT; DECLARE v_status VARCHAR(20);
  DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
  START TRANSACTION;
  SELECT id_alat, jumlah, status INTO v_alat, v_jumlah, v_status
  FROM peminjaman WHERE id_peminjaman = p_id AND id_user = p_user FOR UPDATE;
  IF v_status IS NULL OR v_status <> 'disetujui' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Peminjaman tidak dapat dikembalikan';
  END IF;
  INSERT INTO pengembalian (id_peminjaman, tgl_kembali) VALUES (p_id, CURDATE());
  UPDATE peminjaman SET status = 'dikembalikan' WHERE id_peminjaman = p_id;
  UPDATE alat SET stok = stok + v_jumlah WHERE id_alat = v_alat;
  COMMIT;
END//

DELIMITER ;

-- DATA AWAL (password semua akun: "password")
INSERT INTO users (nama, username, password, role) VALUES
('Administrator','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('Petugas Satu','petugas','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','petugas'),
('Peminjam Satu','peminjam','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','peminjam');
INSERT INTO kategori (nama_kategori) VALUES ('Elektronik'),('Perkakas'),('Multimedia');
INSERT INTO alat (id_kategori, nama_alat, stok, kondisi, deskripsi) VALUES
(1,'Multimeter Digital',5,'baik','Alat ukur listrik'),
(2,'Bor Listrik',3,'baik','Bor tangan 500W'),
(3,'Proyektor',2,'baik','Proyektor 3000 lumens');
