<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-08-14 10:16:53 --> Severity: error --> Exception: Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.1.0". You are running 8.0.30. C:\xampp\htdocs\katalog\application\vendor\composer\platform_check.php 22
ERROR - 2026-08-14 10:16:57 --> Severity: error --> Exception: Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.1.0". You are running 8.0.30. C:\xampp\htdocs\katalog\application\vendor\composer\platform_check.php 22
ERROR - 2026-08-14 10:36:22 --> Severity: error --> Exception: Company_m::set_kbli(): Argument #2 ($kbli_ids) must be of type array, string given, called in C:\xampp\htdocs\katalog\application\controllers\admin\Companies.php on line 115 C:\xampp\htdocs\katalog\application\models\Company_m.php 101
ERROR - 2026-08-14 10:43:51 --> Query error: Not unique table/alias: 'p' - Invalid query: SELECT `p`.*, `c`.`name` AS `company_name`, `c`.`city` AS `company_city`, (SELECT pi.filename FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.position ASC LIMIT 1) AS image, (SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = p.id ORDER BY pl.name ASC LIMIT 1) AS marketplace_name, (SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = p.id ORDER BY pp.id ASC LIMIT 1) AS marketplace_url
FROM (`products` `p`, `products` `p`)
JOIN `companies` `c` ON `c`.`id` = `p`.`company_id`
WHERE `p`.`is_active` = 1
AND `c`.`is_active` = 1
ORDER BY `p`.`is_promo` DESC, `p`.`is_featured` DESC, `p`.`avg_rating` DESC, `p`.`rating_count` DESC, `p`.`created_at` DESC
 LIMIT 20
ERROR - 2026-08-14 10:44:39 --> 404 Page Not Found: 
ERROR - 2026-08-14 11:27:10 --> Query error: Column 'user_id' in where clause is ambiguous - Invalid query: SELECT `p`.*, `c`.`name` AS `company_name`, `c`.`city` AS `company_city`, (SELECT pi.filename FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.position ASC LIMIT 1) AS image, (SELECT pl.name FROM product_platforms pp JOIN platforms pl ON pl.id = pp.platform_id WHERE pp.product_id = p.id ORDER BY pl.name ASC LIMIT 1) AS marketplace_name, (SELECT pp.product_url FROM product_platforms pp WHERE pp.product_id = p.id ORDER BY pp.id ASC LIMIT 1) AS marketplace_url, `keyword`
FROM (`products` `p`, `search_logs`)
JOIN `companies` `c` ON `c`.`id` = `p`.`company_id`
WHERE `p`.`is_active` = 1
AND `c`.`is_active` = 1
AND `user_id` = 2
ORDER BY `id` DESC
 LIMIT 20
ERROR - 2026-08-14 11:27:46 --> Query error: Unknown column 'p.id' in 'where clause' - Invalid query: SELECT ((SELECT COALESCE(MAX(pr.score), 0)
			FROM product_kbli pk2
			JOIN (SELECT 2 AS kid, 4 AS score UNION ALL SELECT 3 AS kid, 1 AS score) pr ON pr.kid = pk2.kbli_id
			WHERE pk2.product_id = p.id)) AS pref_score
ORDER BY `p`.`is_promo` DESC, `pref_score` DESC, `p`.`avg_rating` DESC, `p`.`rating_count` DESC, `p`.`created_at` DESC
 LIMIT 20
ERROR - 2026-08-14 12:19:24 --> Severity: error --> Exception: Call to undefined method Product_m::where() C:\xampp\htdocs\katalog\application\controllers\admin\Wa.php 30
ERROR - 2026-08-14 12:22:06 --> 404 Page Not Found: 
