-- ============================================================
-- E-Katalog — DATA CONTOH (Development Only)
-- Bukan bagian dari skema inti; untuk demo & pengujian katalog.
-- File gambar contoh sudah disiapkan di assets/uploads/.
-- ============================================================
    
USE `ekatalog`;

SET FOREIGN_KEY_CHECKS = 0;

-- Tambahan KBLI untuk demo
INSERT INTO `kbli` (`id`, `code`, `name`) VALUES
(6, '11010', 'Industri Minuman'),
(7, '15125', 'Industri Alas Kaki')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Companies & Products: data dihapus, kosongkan untuk data produksi.

-- Banners
INSERT INTO `banners` (`id`, `title`, `image`, `url`, `position`, `sort_order`, `is_active`) VALUES
(1, 'Promo Besar-besaran', 'b1.png', 'http://localhost/katalog/', 'top', 1, 1),
(2, 'Diskon Musim Panas', 'b2.png', 'http://localhost/katalog/', 'middle', 1, 1)
ON DUPLICATE KEY UPDATE `image` = VALUES(`image`);

-- Kategori (data contoh) + relasi ke KBLI demo (termasuk id 6 & 7)
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `is_active`) VALUES
(1, 'Kopi & Minuman', 'kopi-minuman', 1, 1),
(2, 'Pakaian', 'pakaian', 2, 1),
(3, 'Farmasi', 'farmasi', 3, 1),
(4, 'Otomotif', 'otomotif', 4, 1),
(5, 'Minuman', 'minuman', 1, 1),
(6, 'Alas Kaki', 'alas-kaki', 2, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `category_kbli` (`category_id`, `kbli_id`) VALUES
(1, 2), (5, 6), (2, 3), (6, 7)
ON DUPLICATE KEY UPDATE `kbli_id` = VALUES(`kbli_id`);

SET FOREIGN_KEY_CHECKS = 1;
